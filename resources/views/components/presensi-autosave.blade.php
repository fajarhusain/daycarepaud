<script>
document.querySelectorAll('[data-presensi-form]').forEach((form) => {
    const token = form.querySelector('input[name="_token"]')?.value;
    const tanggal = form.querySelector('input[name="tanggal"]')?.value;
    const kelas = form.querySelector('input[name="filter_kelas_id"]')?.value ?? '';

    const save = async (select) => {
        const row = select.closest('tr');
        const match = select.name.match(/presensi\[(\d+)\]/);
        if (!match || !token || !tanggal) return;
        const anakId = match[1];
        const note = row.querySelector('[data-presensi-note]');
        const prev = select.dataset.prev ?? select.value;
        select.disabled = true;
        const body = new FormData();
        body.append('_token', token);
        body.append('tanggal', tanggal);
        if (kelas) body.append('filter_kelas_id', kelas);
        body.append(`presensi[${anakId}][status]`, select.value);
        body.append(`presensi[${anakId}][keterangan]`, note?.value ?? '');
        try {
            const res = await fetch(form.action, {
                method: 'POST',
                body,
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            if (!res.ok) throw new Error();
            const data = await res.json();
            select.dataset.prev = select.value;
            if (note) note.dataset.prev = note.value;
            const hadir = document.querySelector('[data-presensi-hadir]');
            const absen = document.querySelector('[data-presensi-absen]');
            if (hadir) hadir.textContent = data.hadir;
            if (absen) absen.textContent = Math.max(0, data.total - data.hadir);
            const bulan = row.querySelector('[data-presensi-bulan]');
            if (bulan) bulan.textContent = data.hadir_bulan;
        } catch (e) {
            select.value = prev;
        } finally {
            select.disabled = false;
        }
    };

    form.querySelectorAll('[data-presensi-status]').forEach((select) => {
        select.dataset.prev = select.value;
        select.addEventListener('change', () => save(select));
    });

    form.querySelectorAll('[data-presensi-note]').forEach((input) => {
        input.dataset.prev = input.value;
        input.addEventListener('change', () => {
            if (input.dataset.prev === input.value) return;
            const select = input.closest('tr')?.querySelector('[data-presensi-status]');
            if (select) save(select);
        });
    });

    form.addEventListener('submit', (event) => event.preventDefault());
});
</script>
