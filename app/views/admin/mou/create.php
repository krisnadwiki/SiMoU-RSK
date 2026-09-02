<?php
/**
 * views/admin/mou/create.php — Add New MoU Form
 */

require __DIR__ . '/../../layouts/header.php';
?>

<div class="page-body">

    <div style="max-width: 860px; margin: 0 auto;">

        <!-- Back link -->
        <a href="<?= APP_URL ?>/admin/mou" class="btn btn-ghost btn-sm mb-3" style="padding-left:0;">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar MoU
        </a>

        <!-- Error list -->
        <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <div>
                <strong>Terdapat kesalahan:</strong>
                <ul style="margin:.5rem 0 0 1rem; padding:0;">
                    <?php foreach ($errors as $err): ?>
                    <li><?= e($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <i class="fa-solid fa-plus-circle"></i>
                    Tambah Dokumen MoU / MoA Baru
                </div>
            </div>
            <div class="card-body">

                <form method="POST" action="<?= APP_URL ?>/admin/mou/create"
                      enctype="multipart/form-data" id="mouForm">
                    <?= csrf_field() ?>

                    <div class="form-row">
                        <!-- Nomor RSUD Kilisuci -->
                        <div class="form-group">
                            <label class="form-label">
                                Nomor Surat RSUD Kilisuci <span class="form-required">*</span>
                            </label>
                            <input type="text" name="mou_number" class="form-control" required
                                   placeholder="Contoh: MOU/RSUDKLS/2025/001"
                                   value="<?= e($old['mou_number'] ?? '') ?>">
                        </div>

                        <!-- Nomor Surat Mitra -->
                        <div class="form-group">
                            <label class="form-label">
                                Nomor Surat Mitra / Institusi
                            </label>
                            <input type="text" name="mou_number_mitra" class="form-control"
                                   placeholder="Contoh: 045.2/123/UNIK/2025 (jika ada)"
                                   value="<?= e($old['mou_number_mitra'] ?? '') ?>">
                        </div>

                        <!-- Tipe Dokumen -->
                        <div class="form-group">
                            <label class="form-label">
                                Tipe Dokumen <span class="form-required">*</span>
                            </label>
                            <select name="doc_type" class="form-control" required>
                                <option value="MoU" <?= ($old['doc_type'] ?? '') === 'MoU' ? 'selected' : '' ?>>MoU - Memorandum of Understanding</option>
                                <option value="MoA" <?= ($old['doc_type'] ?? '') === 'MoA' ? 'selected' : '' ?>>MoA - Memorandum of Agreement</option>
                            </select>
                        </div>
                    </div>

                    <!-- Judul -->
                    <div class="form-group">
                        <label class="form-label">
                            Judul Kerjasama <span class="form-required">*</span>
                        </label>
                        <input type="text" name="title" class="form-control" required
                               placeholder="Judul lengkap dokumen kerjasama"
                               value="<?= e($old['title'] ?? '') ?>">
                    </div>

                    <div class="form-row">
                        <!-- Institusi -->
                        <div class="form-group">
                            <label class="form-label">
                                Institusi / Mitra <span class="form-required">*</span>
                            </label>
                            <select name="institution_id" class="form-control" required>
                                <option value="">- Pilih Institusi -</option>
                                <?php foreach ($options['institutions'] as $inst): ?>
                                <option value="<?= $inst['id'] ?>"
                                    <?= ($old['institution_id'] ?? 0) == $inst['id'] ? 'selected' : '' ?>>
                                    <?= e($inst['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Kategori -->
                        <div class="form-group">
                            <label class="form-label">
                                Kategori <span class="form-required">*</span>
                            </label>
                            <select name="category_id" class="form-control" required>
                                <option value="">- Pilih Kategori -</option>
                                <?php foreach ($options['categories'] as $cat): ?>
                                <option value="<?= $cat['id'] ?>"
                                    <?= ($old['category_id'] ?? 0) == $cat['id'] ? 'selected' : '' ?>>
                                    <?= e($cat['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Unit Kerja (optional) -->
                    <div class="form-group">
                        <label class="form-label">Unit Kerja Internal (opsional)</label>
                        <select name="unit_id" class="form-control">
                            <option value="">- Tidak ada unit spesifik -</option>
                            <?php foreach ($options['units'] as $unit): ?>
                            <option value="<?= $unit['id'] ?>"
                                <?= ($old['unit_id'] ?? null) == $unit['id'] ? 'selected' : '' ?>>
                                <?= e($unit['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-row">
                        <!-- Tanggal Mulai -->
                        <div class="form-group">
                            <label class="form-label">
                                Tanggal Mulai <span class="form-required">*</span>
                            </label>
                            <input type="date" name="start_date" class="form-control" required
                                   value="<?= e($old['start_date'] ?? '') ?>">
                        </div>

                        <!-- Tanggal Berakhir -->
                        <div class="form-group">
                            <label class="form-label">
                                Tanggal Berakhir <span class="form-required">*</span>
                            </label>
                            <input type="date" name="end_date" class="form-control" required
                                   value="<?= e($old['end_date'] ?? '') ?>">
                        </div>
                    </div>

                    <!-- Pengingat Masa Berakhir (Segmented Radio Group) -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fa-solid fa-bell" style="color:var(--warning);"></i>
                            Pengingat Masa Berakhir
                        </label>
                        <div class="radio-btn-group">
                            <?php $rem = (int)($old['reminder_days'] ?? 60); ?>
                            <input type="radio" name="reminder_days" id="rem30" value="30" <?= $rem === 30 ? 'checked' : '' ?>>
                            <label for="rem30"><i class="fa-solid fa-clock"></i> 30 Hari</label>

                            <input type="radio" name="reminder_days" id="rem60" value="60" <?= $rem === 60 ? 'checked' : '' ?>>
                            <label for="rem60"><i class="fa-solid fa-clock"></i> 60 Hari</label>

                            <input type="radio" name="reminder_days" id="rem90" value="90" <?= $rem === 90 ? 'checked' : '' ?>>
                            <label for="rem90"><i class="fa-solid fa-clock"></i> 90 Hari</label>

                            <input type="radio" name="reminder_days" id="rem120" value="120" <?= $rem === 120 ? 'checked' : '' ?>>
                            <label for="rem120"><i class="fa-solid fa-clock"></i> 120 Hari</label>
                        </div>
                        <small class="text-muted" style="margin-top:6px; display:block;">Dokumen otomatis berstatus "Segera Berakhir" sesuai jumlah hari di atas sebelum PKS berakhir.</small>
                    </div>

                    <!-- Deskripsi -->
                    <div class="form-group">
                        <label class="form-label">Keterangan / Deskripsi</label>
                        <textarea name="description" class="form-control" rows="4"
                                  placeholder="Deskripsi singkat ruang lingkup kerjasama…"><?= e($old['description'] ?? '') ?></textarea>
                    </div>

                    <!-- Upload Dokumen -->
                    <div class="form-group">
                        <label class="form-label">Dokumen PDF (opsional, maks <?= UPLOAD_MAX_MB ?>MB)</label>
                        <div class="file-upload-area" id="dropZone">
                            <input type="file" name="document" accept=".pdf" id="fileInput"
                                   onchange="updateFileLabel(this)">
                            <div class="file-upload-icon"><i class="fa-solid fa-cloud-arrow-up"></i></div>
                            <div class="file-upload-text" id="fileLabel">
                                Klik atau drag &amp; drop file PDF di sini
                            </div>
                            <div class="file-upload-hint">Format PDF, ukuran maksimal <?= UPLOAD_MAX_MB ?> MB</div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="d-flex gap-3 mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-floppy-disk"></i> Simpan Dokumen
                        </button>
                        <a href="<?= APP_URL ?>/admin/mou" class="btn btn-outline">
                            <i class="fa-solid fa-xmark"></i> Batal
                        </a>
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
            label.textContent = 'Klik atau drag & drop file PDF di sini';
            return;
        }

        if (file.size > maxBytes) {
            showToast(`Ukuran berkas melebihi batas maksimal ${maxMB} MB!`, 'error');
            input.value = '';
            label.textContent = 'Klik atau drag & drop file PDF di sini';
            return;
        }

        const size = (file.size / 1024 / 1024).toFixed(2);
        label.innerHTML = `<i class="fa-solid fa-file-pdf" style="color:var(--danger);"></i> <strong>${file.name}</strong> (${size} MB)`;
    } else {
        label.textContent = 'Klik atau drag & drop file PDF di sini';
    }
}

// Date validation
document.querySelector('[name="end_date"]')?.addEventListener('change', function() {
    const start = document.querySelector('[name="start_date"]').value;
    if (start && this.value && this.value <= start) {
        showToast('Tanggal berakhir harus setelah tanggal mulai!', 'error');
        this.value = '';
    }
});
JS;

require __DIR__ . '/../../layouts/footer.php';
?>
