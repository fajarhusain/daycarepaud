import { FFmpeg } from '@ffmpeg/ffmpeg';
import { fetchFile, toBlobURL } from '@ffmpeg/util';

const VIDEO_EXTENSIONS = ['mp4', 'mov', 'webm'];

let ffmpegInstance = null;
let ffmpegLoadPromise = null;

function getConfig() {
    return window.documentationMediaConfig || {
        video_max_seconds: 60,
        video_input_max_kb: 30720,
        video_max_width: 1280,
    };
}

export function isVideoFile(file) {
    if (!file) {
        return false;
    }
    if (file.type && file.type.startsWith('video/')) {
        return true;
    }
    const name = file.name || '';
    const ext = name.split('.').pop()?.toLowerCase();

    return VIDEO_EXTENSIONS.includes(ext);
}

export function isVideoPath(url) {
    if (!url) {
        return false;
    }
    const path = String(url).split('?')[0].split('#')[0];
    const ext = path.split('.').pop()?.toLowerCase();

    return VIDEO_EXTENSIONS.includes(ext);
}

async function loadFfmpeg() {
    if (ffmpegInstance?.loaded) {
        return ffmpegInstance;
    }
    if (ffmpegLoadPromise) {
        return ffmpegLoadPromise;
    }

    ffmpegLoadPromise = (async () => {
        const ffmpeg = new FFmpeg();
        const base = `${window.location.origin}/ffmpeg`;
        await ffmpeg.load({
            coreURL: await toBlobURL(`${base}/ffmpeg-core.js`, 'text/javascript'),
            wasmURL: await toBlobURL(`${base}/ffmpeg-core.wasm`, 'application/wasm'),
        });
        ffmpegInstance = ffmpeg;

        return ffmpeg;
    })();

    return ffmpegLoadPromise;
}

function readVideoDuration(file) {
    return new Promise((resolve, reject) => {
        const url = URL.createObjectURL(file);
        const video = document.createElement('video');
        video.preload = 'metadata';
        video.onloadedmetadata = () => {
            URL.revokeObjectURL(url);
            resolve(video.duration);
        };
        video.onerror = () => {
            URL.revokeObjectURL(url);
            reject(new Error('Tidak bisa membaca durasi video.'));
        };
        video.src = url;
    });
}

/**
 * @param {File} file
 * @returns {Promise<File>}
 */
function reportProgress(onProgress, percent, message) {
    onProgress?.({ percent: Math.min(100, Math.max(0, percent)), message });
}

export async function compressVideo(file, onProgress) {
    const cfg = getConfig();
    reportProgress(onProgress, 2, 'Memvalidasi video...');

    const inputMaxBytes = (cfg.video_input_max_kb || 30720) * 1024;
    if (file.size > inputMaxBytes) {
        throw new Error(`Video terlalu besar. Maksimal ${Math.round(cfg.video_input_max_kb / 1024)} MB sebelum kompresi.`);
    }

    reportProgress(onProgress, 8, 'Membaca durasi video...');
    const duration = await readVideoDuration(file);
    const maxSec = cfg.video_max_seconds || 60;
    if (duration > maxSec) {
        throw new Error(`Durasi video maksimal ${maxSec} detik.`);
    }

    reportProgress(onProgress, 12, 'Memuat modul kompresi...');
    const ffmpeg = await loadFfmpeg();
    const inputName = 'input.' + (file.name.split('.').pop() || 'mp4').toLowerCase();
    const outputName = 'output.mp4';

    reportProgress(onProgress, 18, 'Menyiapkan file...');
    await ffmpeg.writeFile(inputName, await fetchFile(file));

    const maxW = cfg.video_max_width || 1280;
    const progressHandler = ({ progress }) => {
        const ratio = typeof progress === 'number' ? progress : 0;
        reportProgress(onProgress, 20 + Math.round(ratio * 75), 'Sedang mengupload media');
    };
    ffmpeg.on('progress', progressHandler);

    try {
        await ffmpeg.exec([
            '-i', inputName,
            '-vf', `scale='min(${maxW},iw)':-2`,
            '-c:v', 'libx264',
            '-crf', '28',
            '-preset', 'fast',
            '-movflags', '+faststart',
            '-c:a', 'aac',
            '-b:a', '96k',
            outputName,
        ]);
    } finally {
        ffmpeg.off('progress', progressHandler);
    }

    reportProgress(onProgress, 96, 'Menyelesaikan...');
    const data = await ffmpeg.readFile(outputName);
    await ffmpeg.deleteFile(inputName).catch(() => {});
    await ffmpeg.deleteFile(outputName).catch(() => {});

    const baseName = file.name.replace(/\.[^/.]+$/, '') || 'video';
    const blob = new Blob([data], { type: 'video/mp4' });

    reportProgress(onProgress, 100, 'Selesai');

    return new File([blob], `${baseName}.mp4`, { type: 'video/mp4' });
}

/**
 * @param {File} file
 * @returns {Promise<File>}
 */
export async function prepareDocumentationFile(file, onProgress) {
    if (isVideoFile(file)) {
        return compressVideo(file, onProgress);
    }

    reportProgress(onProgress, 15, 'Mengompres gambar...');
    let result = file;
    if (typeof window.compressImage === 'function') {
        result = await window.compressImage(file);
    }
    reportProgress(onProgress, 100, 'Selesai');

    return result;
}

export function attachDocumentationMediaGlobals() {
    window.isVideoFile = isVideoFile;
    window.isVideoPath = isVideoPath;
    window.compressVideo = compressVideo;
    window.prepareDocumentationFile = prepareDocumentationFile;
    if (typeof window.__resolveDocMediaReady === 'function') {
        window.__resolveDocMediaReady();
    }
}

attachDocumentationMediaGlobals();
