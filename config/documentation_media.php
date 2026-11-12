<?php

return [
    'video_max_kb' => (int) env('DOCUMENTATION_VIDEO_MAX_KB', 15360),
    'video_max_seconds' => (int) env('DOCUMENTATION_VIDEO_MAX_SECONDS', 60),
    'video_input_max_kb' => (int) env('DOCUMENTATION_VIDEO_INPUT_MAX_KB', 30720),
    'image_max_kb' => 2048,
    'allowed_video_mimes' => ['mp4', 'mov', 'webm'],
    'video_max_width' => 1280,
];
