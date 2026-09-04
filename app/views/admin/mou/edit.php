<?php
/**
 * views/admin/mou/edit.php — Edit MoU + Renewal History
 */

require __DIR__ . '/../../layouts/header.php';
?>

<div class="page-body">
<div style="max-width:960px; margin:0 auto;">

    <a href="<?= APP_URL ?>/admin/mou" class="btn btn-ghost btn-sm mb-3" style="padding-left:0;">
        <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar MoU
    </a>

    <?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <i class="fa-solid fa-triangle-exclamation"></i>
        <div>
            <strong>Terdapat kesalahan:</strong>
            <ul style="margin:.4rem 0 0 1rem;">
                <?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
            </ul>
        </div>
    </div>
    <?php endif; ?>

    <!-- MoU Info Strip / Summary Card -->
    <div class="mou-summary-strip">
        <div>
            <div style="font-size:.68rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:.07em; margin-bottom:2px;">No. Surat RSUD</div>
            <code style="font-size:.82rem; font-weight:700; color:var(--primary);"><?= e($mou['mou_number']) ?></code>
            <?php if (!empty($mou['mou_number_mitra'])): ?>
            <div style="font-size:.7rem; color:var(--text-muted); margin-top:2px;">
                Mitra: <code><?= e($mou['mou_number_mitra']) ?></code>
            </div>
            <?php endif; ?>
        </div>
        <div>
            <div style="font-size:.68rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:.07em; margin-bottom:2px;">Institusi / Mitra</div>
            <div style="font-size:.88rem; font-weight:700; color:var(--text-main);"><?= e($mou['institution_name']) ?></div>
        </div>
        <div>
            <div style="font-size:.68rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:.07em; margin-bottom:2px;">Tanggal Berakhir</div>
            <div style="font-size:.85rem; font-weight:700; color:var(--text-main);">
                <?= format_date_id($mou['end_date'], 'short') ?>
                <?php $d = days_until($mou['end_date']); ?>
                <?php if ($d < 0): ?>
                    <small style="color:var(--danger); font-weight:700; display:block;">Habis <?= abs($d) ?> hari lalu</small>
                <?php elseif ($d === 0): ?>
                    <small style="color:var(--danger); font-weight:700; display:block;">Habis Hari Ini</small>
                <?php else: ?>
                    <small style="color:var(--warning); font-weight:600; display:block;">Sisa <?= $d ?> hari</small>
                <?php endif; ?>
            </div>
        </div>
        <div>
            <div style="font-size:.68rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:.07em; margin-bottom:4px;">Status Dokumen</div>
            <?= status_badge($mou['status']) ?>
        </div>
        <div class="mou-summary-actions">
            <a href="<?= APP_URL ?>/admin/mou/<?= $mou['id'] ?>/renew" class="btn btn-primary btn-sm" style="width:100%; justify-content:center;" title="Perbarui, Perpanjang, atau Tambah Adendum">
                <i class="fa-solid fa-rotate-right"></i> Perbarui / Adendum
            </a>
            <?php if ($mou['file_path']): ?>
            <a href="javascript:void(0)"
               onclick="openPdfModal('<?= APP_URL ?>/admin/mou/<?= $mou['id'] ?>/document','<?= APP_URL ?>/admin/mou/<?= $mou['id'] ?>/document?download=1','<?= e(addslashes($mou['mou_number'])) ?>')"
               class="btn btn-outline btn-sm" style="width:100%; justify-content:center;" title="Lihat Berkas PDF Dokumen Utama">
                <i class="fa-solid fa-file-pdf" style="color:var(--danger);"></i> Lihat PDF Utama
            </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="mou-detail-layout">

        <!-- Edit Form -->
        <div class="card">
            <div class="card-header">
                <div class="card-title"><i class="fa-solid fa-pen-to-square"></i> Edit Data MoU</div>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= APP_URL ?>/admin/mou/<?= $mou['id'] ?>/edit"
                      enctype="multipart/form-data">
                    <?= csrf_field() ?>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">No. Surat RSUD Kilisuci <span class="form-required">*</span></label>
                            <input type="text" name="mou_number" class="form-control" required
                                   value="<?= e($old['mou_number'] ?? $mou['mou_number']) ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">No. Surat Mitra / Institusi</label>
                            <input type="text" name="mou_number_mitra" class="form-control"
                                   placeholder="Nomor dari mitra (jika ada)"
                                   value="<?= e($old['mou_number_mitra'] ?? $mou['mou_number_mitra'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Tipe Dokumen</label>
                            <select name="doc_type" class="form-control">
                                <option value="MoU" <?= ($old['doc_type'] ?? $mou['doc_type']) === 'MoU' ? 'selected' : '' ?>>MoU</option>
                                <option value="MoA" <?= ($old['doc_type'] ?? $mou['doc_type']) === 'MoA' ? 'selected' : '' ?>>MoA</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Judul <span class="form-required">*</span></label>
                        <input type="text" name="title" class="form-control" required
                               value="<?= e($old['title'] ?? $mou['title']) ?>">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Institusi <span class="form-required">*</span></label>
                            <select name="institution_id" class="form-control" required>
                                <?php foreach ($options['institutions'] as $inst): ?>
                                <option value="<?= $inst['id'] ?>"
                                    <?= ($old['institution_id'] ?? $mou['institution_id']) == $inst['id'] ? 'selected' : '' ?>>
                                    <?= e($inst['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Kategori <span class="form-required">*</span></label>
                            <select name="category_id" class="form-control" required>
                                <?php foreach ($options['categories'] as $cat): ?>
                                <option value="<?= $cat['id'] ?>"
                                    <?= ($old['category_id'] ?? $mou['category_id']) == $cat['id'] ? 'selected' : '' ?>>
                                    <?= e($cat['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Unit Kerja Internal</label>
                        <select name="unit_id" class="form-control">
                            <option value="">- Tidak ada unit spesifik -</option>
                            <?php foreach ($options['units'] as $unit): ?>
                            <option value="<?= $unit['id'] ?>"
                                <?= ($old['unit_id'] ?? $mou['unit_id']) == $unit['id'] ? 'selected' : '' ?>>
                                <?= e($unit['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Tanggal Mulai <span class="form-required">*</span></label>
                            <input type="date" name="start_date" class="form-control" required
                                   value="<?= e($old['start_date'] ?? $mou['start_date']) ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Tanggal Berakhir <span class="form-required">*</span></label>
                            <input type="date" name="end_date" class="form-control" required
                                   value="<?= e($old['end_date'] ?? $mou['end_date']) ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fa-solid fa-bell" style="color:var(--warning);"></i>
                            Pengingat Masa Berakhir
                        </label>
                        <div class="radio-btn-group">
                            <?php $rem = (int)($old['reminder_days'] ?? $mou['reminder_days'] ?? 60); ?>
                            <input type="radio" name="reminder_days" id="edit_rem30" value="30" <?= $rem === 30 ? 'checked' : '' ?>>
                            <label for="edit_rem30"><i class="fa-solid fa-clock"></i> 30 Hari</label>

                            <input type="radio" name="reminder_days" id="edit_rem60" value="60" <?= $rem === 60 ? 'checked' : '' ?>>
                            <label for="edit_rem60"><i class="fa-solid fa-clock"></i> 60 Hari</label>

                            <input type="radio" name="reminder_days" id="edit_rem90" value="90" <?= $rem === 90 ? 'checked' : '' ?>>
                            <label for="edit_rem90"><i class="fa-solid fa-clock"></i> 90 Hari</label>

                            <input type="radio" name="reminder_days" id="edit_rem120" value="120" <?= $rem === 120 ? 'checked' : '' ?>>
                            <label for="edit_rem120"><i class="fa-solid fa-clock"></i> 120 Hari</label>
                        </div>
                        <small class="text-muted" style="margin-top:6px; display:block;">Dokumen otomatis berstatus "Segera Berakhir" sesuai jumlah hari di atas sebelum PKS berakhir.</small>
                    </div>

                    <?php if (has_role('superadmin')): ?>
                    <div class="form-group">
                        <label class="form-label">Status (Override Manual)</label>
                        <select name="status" class="form-control">
                            <option value="active"        <?= ($mou['status'] ?? '') === 'active'        ? 'selected':'' ?>>Aktif</option>
                            <option value="expiring_soon" <?= ($mou['status'] ?? '') === 'expiring_soon' ? 'selected':'' ?>>Segera Berakhir</option>
                            <option value="expired"       <?= ($mou['status'] ?? '') === 'expired'       ? 'selected':'' ?>>Berakhir</option>
                            <option value="terminated"    <?= ($mou['status'] ?? '') === 'terminated'    ? 'selected':'' ?>>Dihentikan</option>
                        </select>
                        <div class="form-text">Status otomatis dihitung dari tanggal, kecuali "Dihentikan".</div>
                    </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label class="form-label">Keterangan</label>
                        <textarea name="description" class="form-control" rows="3"><?= e($old['description'] ?? $mou['description']) ?></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Ganti Dokumen PDF (opsional, maks <?= UPLOAD_MAX_MB ?>MB)</label>
                        <div class="file-upload-area">
                            <input type="file" name="document" accept=".pdf" onchange="updateFileLabel(this,'editFileLabel')">
                            <div class="file-upload-icon"><i class="fa-solid fa-cloud-arrow-up"></i></div>
                            <div class="file-upload-text" id="editFileLabel">
                                <?php if ($mou['file_path']): ?>
                                    <i class="fa-solid fa-file-pdf" style="color:var(--danger);"></i>
                                    File sudah ada - unggah baru untuk mengganti
                                <?php else: ?>
                                    Klik atau drag &amp; drop file PDF di sini
                                <?php endif; ?>
                            </div>
                            <div class="file-upload-hint">Format PDF, ukuran maksimal <?= UPLOAD_MAX_MB ?> MB</div>
                        </div>
                    </div>

                    <div class="d-flex gap-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
                        </button>
                        <a href="<?= APP_URL ?>/admin/mou" class="btn btn-outline">Batal</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Renewal History Sidebar -->
        <div class="card history-card" id="historyCard">
            <div class="card-header history-card-header" onclick="toggleHistoryCard()" title="Klik untuk membuka/menutup histori">
                <div class="card-title">
                    <i class="fa-solid fa-history" style="color:var(--primary);"></i> Histori Perpanjangan
                </div>
                <div style="display:flex; align-items:center; gap:8px;">
                    <span class="badge badge-primary"><?= count($renewals) ?>x</span>
                    <button type="button" class="history-toggle-btn" aria-label="Buka/Tutup Histori Perpanjangan">
                        <i class="fa-solid fa-chevron-down history-toggle-icon"></i>
                    </button>
                </div>
            </div>
            <div class="card-body history-card-body" style="padding: 16px;">
                <?php if (empty($renewals)): ?>
                    <div class="empty-state" style="padding:16px 0;">
                        <i class="fa-solid fa-rotate-right" style="font-size:1.4rem;"></i>
                        <h3 style="font-size:.82rem;">Belum ada perpanjangan</h3>
                    </div>
                <?php else: ?>
                    <div class="timeline timeline-scrollable">
                        <?php foreach ($renewals as $r): ?>
                        <div class="timeline-item">
                            <div class="timeline-date"><?= format_date_id($r['created_at'], 'medium') ?></div>
                            <div class="timeline-body">
                                <div style="font-size:.72rem; font-weight:700; color:var(--primary); margin-bottom:4px;">
                                    <?= e($r['renewal_number']) ?>
                                </div>
                                <div style="font-size:.78rem;">
                                    <?= format_date_id($r['new_start_date'], 'short') ?> –
                                    <?= format_date_id($r['new_end_date'], 'short') ?>
                                </div>
                                <?php if ($r['notes']): ?>
                                <div style="font-size:.74rem; color:var(--text-muted); margin-top:4px;">
                                    <?= e($r['notes']) ?>
                                </div>
                                <?php endif; ?>
                                <div style="margin-top:8px; display:flex; gap:6px; flex-wrap:wrap; align-items:center;">
                                    <!-- Edit button -->
                                    <a href="<?= APP_URL ?>/admin/mou/<?= $mou['id'] ?>/renew/<?= $r['id'] ?>/edit"
                                       class="btn btn-outline btn-sm" style="font-size:.72rem; padding:3px 8px; color:var(--accent); border-color:rgba(13,148,136,.3);"
                                       title="Edit entri perpanjangan / adendum ini">
                                        <i class="fa-solid fa-pen-to-square"></i> Edit
                                    </a>
                                    <?php if (!empty($r['document_path'])): ?>
                                    <a href="javascript:void(0)"
                                       onclick="openPdfModal('<?= APP_URL ?>/admin/mou/<?= $mou['id'] ?>/addendum/<?= $r['id'] ?>/document','<?= APP_URL ?>/admin/mou/<?= $mou['id'] ?>/addendum/<?= $r['id'] ?>/document?download=1','Addendum — <?= e(addslashes($r['renewal_number'])) ?>')"
                                       class="btn btn-outline btn-sm" style="font-size:.72rem; padding:3px 8px; color:var(--primary);">
                                        <i class="fa-solid fa-file-pdf" style="color:var(--danger);"></i> Lihat PDF
                                    </a>
                                    <?php else: ?>
                                    <span style="font-size:.7rem; color:var(--text-muted);">
                                        <i class="fa-solid fa-file-circle-xmark"></i> Belum ada PDF
                                    </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div><!-- /grid -->
</div>
</div>

<?php
$extraScript = <<<'JS'
function toggleHistoryCard() {
    const card = document.getElementById('historyCard');
    if (card) {
        card.classList.toggle('is-collapsed');
    }
}

// Initial state on mobile: collapsed so history does not crowd screen
document.addEventListener('DOMContentLoaded', function() {
    const card = document.getElementById('historyCard');
    if (card && window.innerWidth <= 900) {
        card.classList.add('is-collapsed');
    }
});

function updateFileLabel(input, labelId) {
    const label = document.getElementById(labelId);
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const maxMB = window._UPLOAD_MAX_MB || 10;
        const maxBytes = maxMB * 1024 * 1024;
        const ext = file.name.split('.').pop().toLowerCase();

        if (ext !== 'pdf' && file.type !== 'application/pdf') {
            showToast('Format tidak valid! Hanya berkas PDF (.pdf) yang diizinkan.', 'error');
            input.value = '';
            if (label) label.textContent = 'Klik atau drag & drop file PDF di sini';
            return;
        }

        if (file.size > maxBytes) {
            showToast(`Ukuran berkas melebihi batas maksimal ${maxMB} MB!`, 'error');
            input.value = '';
            if (label) label.textContent = 'Klik atau drag & drop file PDF di sini';
            return;
        }

        const size = (file.size / 1024 / 1024).toFixed(2);
        if (label) label.innerHTML = `<i class="fa-solid fa-file-pdf" style="color:var(--danger);"></i> <strong>${file.name}</strong> (${size} MB)`;
    }
}
JS;
require __DIR__ . '/../../layouts/footer.php';
?>
