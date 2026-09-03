<?php
/**
 * views/admin/mou/renew.php — Perpanjangan / Addendum MoU
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
            <i class="fa-solid fa-rotate-right"></i> Perbarui Dokumen Kerjasama:
        </div>
        <div style="font-weight:700; color:var(--text-main); font-size:1rem; margin-bottom:4px;">
            <?= e($mou['title']) ?>
        </div>
        <div style="font-size:.82rem; color:var(--text-muted);">
            <?= e($mou['institution_name']) ?> &bull;
            Nomor: <code style="color:var(--primary);"><?= e($mou['mou_number']) ?></code> &bull;
            Berakhir: <strong><?= format_date_id($mou['end_date']) ?></strong>
            <?php $d = days_until($mou['end_date']); ?>
            <?php if ($d >= 0): ?>
            <span style="color:var(--warning); font-weight:600;">(Sisa <?= $d ?> hari)</span>
            <?php else: ?>
            <span style="color:var(--danger); font-weight:600;">(Telah berakhir <?= abs($d) ?> hari lalu)</span>
            <?php endif; ?>
        </div>
        <?php if (!empty($mou['file_path'])): ?>
        <div style="margin-top:12px;">
            <a href="javascript:void(0)"
               onclick="openPdfModal('<?= APP_URL ?>/admin/mou/<?= $mou['id'] ?>/document','<?= APP_URL ?>/admin/mou/<?= $mou['id'] ?>/document?download=1','Dokumen Utama — <?= e(addslashes($mou['mou_number'])) ?>')"
               class="btn btn-outline btn-sm" style="font-size:.78rem; border-color:var(--primary); color:var(--primary);">
                <i class="fa-solid fa-file-pdf" style="color:var(--danger);"></i> Lihat Berkas Dokumen Utama Saat Ini
            </a>
        </div>
        <?php endif; ?>
    </div>

    <!-- Explanation Box: Perpanjangan vs Adendum -->
    <div class="alert alert-info mb-4" style="line-height:1.6; font-size:.82rem;">
        <i class="fa-solid fa-circle-info" style="font-size:1.1rem; color:var(--primary); margin-top:2px;"></i>
        <div>
            <strong style="color:var(--text-main); font-size:.85rem;">Petunjuk Pembaruan Dokumen:</strong>
            <ul style="margin:4px 0 0 16px; padding:0;">
                <li><strong>Perpanjangan Masa Berlaku:</strong> Digunakan untuk memperpanjang jangka waktu kerjasama yang habis tanpa merubah struktur utama.</li>
                <li><strong>Adendum / Amandemen:</strong> Digunakan jika terdapat penambahan/perubahan pasal, klausul, atau nilai kerjasama resmi. Berkas PDF adendum baru akan tersimpan di riwayat dokumen.</li>
            </ul>
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
                <i class="fa-solid fa-rotate-right" style="color:var(--accent);"></i>
                Form Perpanjangan / Adendum Kerjasama
            </div>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= APP_URL ?>/admin/mou/<?= $mou['id'] ?>/renew"
                  enctype="multipart/form-data">
                <?= csrf_field() ?>

                <!-- Jenis Pembaruan -->
                <div class="form-group">
                    <label class="form-label">Tipe Pembaruan <span class="form-required">*</span></label>
                    <div class="radio-btn-group">
                        <input type="radio" name="renewal_type" id="type_renewal" value="Perpanjangan" checked>
                        <label for="type_renewal"><i class="fa-solid fa-calendar-plus"></i> Perpanjangan Masa Berlaku</label>

                        <input type="radio" name="renewal_type" id="type_addendum" value="Adendum">
                        <label for="type_addendum"><i class="fa-solid fa-file-signature"></i> Adendum / Perubahan Klausul</label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Nomor Surat / Addendum <span class="form-required">*</span></label>
                    <input type="text" name="renewal_number" class="form-control" required
                           placeholder="Contoh: ADD/RSUDKLS/2025/001">
                    <div class="form-text">Nomor resmi surat perpanjangan atau addendum perjanjian</div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Tanggal Mulai Baru <span class="form-required">*</span></label>
                        <input type="date" name="new_start_date" class="form-control" required
                               value="<?= $mou['end_date'] ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tanggal Berakhir Baru <span class="form-required">*</span></label>
                        <input type="date" name="new_end_date" class="form-control" required>
                        <div class="form-text">Masa berlaku baru setelah perpanjangan</div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Catatan Perpanjangan</label>
                    <textarea name="notes" class="form-control" rows="3"
                              placeholder="Catatan atau alasan perpanjangan…"></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Dokumen Addendum PDF (opsional)</label>
                    <div class="file-upload-area">
                        <input type="file" name="document" accept=".pdf" onchange="updateFileLabel(this)">
                        <div class="file-upload-icon"><i class="fa-solid fa-cloud-arrow-up"></i></div>
                        <div class="file-upload-text" id="fileLabel">Klik untuk mengunggah PDF addendum</div>
                        <div class="file-upload-hint">Format PDF, maks <?= UPLOAD_MAX_MB ?> MB</div>
                    </div>
                </div>

                <div class="alert alert-info" style="margin-top:0; margin-bottom:18px;">
                    <i class="fa-solid fa-circle-info"></i>
                    <span>
                        Setelah disimpan, tanggal berakhir MoU induk akan diperbarui secara otomatis sesuai tanggal baru yang Anda masukkan.
                    </span>
                </div>

                <div class="d-flex gap-3">
                    <button type="submit" class="btn btn-accent">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan Perpanjangan
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
