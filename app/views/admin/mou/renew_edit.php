<?php
/**
 * views/admin/mou/renew_edit.php — Edit Perpanjangan / Addendum MoU
 */

require __DIR__ . '/../../layouts/header.php';
?>

<div class="page-body">
<div style="max-width:680px; margin:0 auto;">

    <a href="<?= APP_URL ?>/admin/mou/<?= $mou['id'] ?>/edit" class="btn btn-ghost btn-sm mb-3" style="padding-left:0;">
        <i class="fa-solid fa-arrow-left"></i> Kembali ke Detail MoU
    </a>

    <!-- MoU Info Card -->
    <div style="background:var(--primary-xl); border:1px solid var(--primary-l);
                border-radius:var(--radius-lg); padding:18px 22px; margin-bottom:20px;">
        <div style="font-size:.7rem; font-weight:700; color:var(--primary); text-transform:uppercase; letter-spacing:.07em; margin-bottom:4px;">
            <i class="fa-solid fa-pen-to-square"></i> Edit Riwayat Adendum / Perpanjangan:
        </div>
        <div style="font-weight:700; color:var(--text-main); font-size:1rem; margin-bottom:4px;">
            <?= e($mou['title']) ?>
        </div>
        <div style="font-size:.82rem; color:var(--text-muted);">
            <?= e($mou['institution_name']) ?> &bull;
            Nomor: <code style="color:var(--primary);"><?= e($mou['mou_number']) ?></code>
        </div>
    </div>

    <?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <i class="fa-solid fa-triangle-exclamation"></i>
        <div>
            <?php foreach ($errors as $err): ?><div><?= e($err) ?></div><?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="fa-solid fa-pen-to-square" style="color:var(--accent);"></i>
                Form Edit Adendum / Perpanjangan
            </div>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= APP_URL ?>/admin/mou/<?= $mou['id'] ?>/renew/<?= $renewal['id'] ?>/edit"
                  enctype="multipart/form-data">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label class="form-label">Nomor Surat / Addendum <span class="form-required">*</span></label>
                    <input type="text" name="renewal_number" class="form-control" required
                           placeholder="Contoh: ADD/RSUDKLS/2025/001"
                           value="<?= e($old['renewal_number'] ?? $renewal['renewal_number']) ?>">
                    <div class="form-text">Nomor resmi surat perpanjangan atau addendum perjanjian</div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Tanggal Mulai Baru <span class="form-required">*</span></label>
                        <input type="date" name="new_start_date" class="form-control" required
                               value="<?= e($old['new_start_date'] ?? $renewal['new_start_date']) ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tanggal Berakhir Baru <span class="form-required">*</span></label>
                        <input type="date" name="new_end_date" class="form-control" required
                               value="<?= e($old['new_end_date'] ?? $renewal['new_end_date']) ?>">
                        <div class="form-text">Masa berlaku baru setelah perpanjangan</div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Catatan Perpanjangan</label>
                    <textarea name="notes" class="form-control" rows="3"
                              placeholder="Catatan atau alasan perpanjangan…"><?= e($old['notes'] ?? $renewal['notes'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Ganti Dokumen Addendum PDF (opsional, maks <?= UPLOAD_MAX_MB ?> MB)</label>
                    <div class="file-upload-area">
                        <input type="file" name="document" accept=".pdf" onchange="updateFileLabel(this)">
                        <div class="file-upload-icon"><i class="fa-solid fa-cloud-arrow-up"></i></div>
                        <div class="file-upload-text" id="fileLabel">
                            <?php if (!empty($renewal['document_path'])): ?>
                                <i class="fa-solid fa-file-pdf" style="color:var(--danger);"></i>
                                File sudah ada - klik atau drag file baru untuk mengganti
                            <?php else: ?>
                                Klik untuk mengunggah PDF addendum
                            <?php endif; ?>
                        </div>
                        <div class="file-upload-hint">Format PDF, maks <?= UPLOAD_MAX_MB ?> MB</div>
                    </div>
                    <?php if (!empty($renewal['document_path'])): ?>
                    <div style="margin-top:8px;">
                        <a href="javascript:void(0)"
                           onclick="openPdfModal('<?= APP_URL ?>/admin/mou/<?= $mou['id'] ?>/addendum/<?= $renewal['id'] ?>/document','<?= APP_URL ?>/admin/mou/<?= $mou['id'] ?>/addendum/<?= $renewal['id'] ?>/document?download=1','<?= e(addslashes($renewal['renewal_number'])) ?> - <?= e(addslashes($mou['institution_name'])) ?>')"
                           class="btn btn-outline btn-sm" style="font-size:.75rem; color:var(--primary);">
                            <i class="fa-solid fa-file-pdf" style="color:var(--danger);"></i> Lihat Berkas PDF yang Tersimpan Saat Ini
                        </a>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="alert alert-info" style="margin-top:0; margin-bottom:18px;">
                    <i class="fa-solid fa-circle-info"></i>
                    <span>
                        Setelah disimpan, masa berlaku dan status MoU induk akan disinkronkan secara otomatis sesuai tanggal terbaru.
                    </span>
                </div>

                <div class="d-flex gap-3">
                    <button type="submit" class="btn btn-accent">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
                    </button>
                    <a href="<?= APP_URL ?>/admin/mou/<?= $mou['id'] ?>/edit" class="btn btn-outline">Batal</a>
                </div>
            </form>
        </div>
    </div>

</div>
</div>

<?php
$extraScript = <<<'JS'
function updateFileLabel(input) {
    const label = document.getElementById('fileLabel');
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const maxMB = window._UPLOAD_MAX_MB || 10;
        const maxBytes = maxMB * 1024 * 1024;
        const ext = file.name.split('.').pop().toLowerCase();

        if (ext !== 'pdf' && file.type !== 'application/pdf') {
            showToast('Format tidak valid! Hanya berkas PDF (.pdf) yang diizinkan.', 'error');
            input.value = '';
            if (label) label.textContent = 'Klik untuk mengunggah PDF addendum';
            return;
        }

        if (file.size > maxBytes) {
            showToast(`Ukuran berkas melebihi batas maksimal ${maxMB} MB!`, 'error');
            input.value = '';
            if (label) label.textContent = 'Klik untuk mengunggah PDF addendum';
            return;
        }

        const size = (file.size / 1024 / 1024).toFixed(2);
        if (label) label.innerHTML = `<i class="fa-solid fa-file-pdf" style="color:var(--danger);"></i> <strong>${file.name}</strong> (${size} MB)`;
    }
}
JS;
require __DIR__ . '/../../layouts/footer.php';
?>
