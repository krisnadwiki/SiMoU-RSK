<?php
/**
 * views/public/detail.php — MoU Detail + PDF Viewer
 */
$daysLeft = days_until($mou['end_date']);
?>

<!-- Detail Header -->
<div style="background:var(--bg-sidebar); padding:28px 24px 36px; color:#fff;">
    <div class="public-container">
        <nav style="font-size:.75rem; color:rgba(255,255,255,.5); margin-bottom:12px;">
            <a href="<?= APP_URL ?>/" style="color:rgba(255,255,255,.5);">Beranda</a>
            <span style="margin:0 6px;">/</span>
            <a href="<?= APP_URL ?>/catalog" style="color:rgba(255,255,255,.5);">Katalog</a>
            <span style="margin:0 6px;">/</span>
            <span style="color:rgba(255,255,255,.8);"><?= e(mb_strimwidth($mou['title'], 0, 50, '…')) ?></span>
        </nav>

        <div style="display:flex; align-items:flex-start; gap:14px; flex-wrap:wrap;">
            <div style="flex:1; min-width:280px;">
                <code style="font-size:.72rem; color:rgba(255,255,255,.6); font-weight:700;">
                    <?= e($mou['mou_number']) ?>
                </code>
                <h1 style="color:#fff; font-size:1.45rem; margin:6px 0 10px; letter-spacing:-.02em; line-height:1.3;">
                    <?= e($mou['title']) ?>
                </h1>
                <div style="display:flex; gap:8px; flex-wrap:wrap;">
                    <span class="badge <?= $mou['doc_type'] === 'MoA' ? 'badge-moa' : 'badge-mou' ?>">
                        <?= e($mou['doc_type']) ?>
                    </span>
                    <?= status_badge($mou['status']) ?>
                    <span class="badge badge-primary"><?= e($mou['category_name']) ?></span>
                </div>
            </div>

            <?php if ($mou['file_path']): ?>
            <a href="<?= APP_URL ?>/uploads/<?= e($mou['file_path']) ?>" target="_blank"
               class="btn btn-primary" style="flex-shrink:0;">
                <i class="fa-solid fa-file-pdf"></i> Unduh PDF
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<section class="section" style="padding-top:28px;">
    <div class="public-container">

        <!-- Alert: expiry -->
        <?php if ($daysLeft >= 0 && $daysLeft <= 30): ?>
        <div class="alert alert-warning">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <span>
                Dokumen ini akan berakhir dalam <strong><?= $daysLeft ?> hari</strong>
                pada <?= format_date_id($mou['end_date']) ?>.
            </span>
        </div>
        <?php elseif ($daysLeft < 0): ?>
        <div class="alert alert-danger">
            <i class="fa-solid fa-ban"></i>
            <span>Dokumen ini telah berakhir pada <?= format_date_id($mou['end_date']) ?>.</span>
        </div>
        <?php endif; ?>

        <div class="public-detail-grid">

            <!-- PDF Viewer -->
            <div>
                <?php if ($mou['file_path']): ?>
                <div class="card mb-3">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="fa-solid fa-file-pdf" style="color:var(--danger);"></i>
                            Dokumen PDF
                        </div>
                        <a href="<?= APP_URL ?>/uploads/<?= e($mou['file_path']) ?>" target="_blank"
                           class="btn btn-outline btn-sm">
                            <i class="fa-solid fa-arrow-up-right-from-square"></i> Buka di Tab Baru
                        </a>
                    </div>
                    <div class="card-body" style="padding:0;">
                        <iframe
                            src="<?= APP_URL ?>/uploads/<?= e($mou['file_path']) ?>"
                            class="pdf-viewer"
                            title="Dokumen MoU <?= e($mou['mou_number']) ?>">
                            <p>Browser Anda tidak mendukung tampilan PDF.
                               <a href="<?= APP_URL ?>/uploads/<?= e($mou['file_path']) ?>">Klik di sini untuk mengunduh</a>.
                            </p>
                        </iframe>
                    </div>
                </div>
                <?php else: ?>
                <div class="card">
                    <div class="card-body">
                        <div class="empty-state">
                            <i class="fa-solid fa-file-circle-xmark" style="color:var(--text-subtle);"></i>
                            <h3>Dokumen PDF belum tersedia</h3>
                            <p>File dokumen belum diunggah ke sistem</p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Description -->
                <?php if ($mou['description']): ?>
                <div class="card">
                    <div class="card-header">
                        <div class="card-title"><i class="fa-solid fa-align-left"></i> Keterangan</div>
                    </div>
                    <div class="card-body">
                        <p style="font-size:.88rem; line-height:1.75; color:var(--text-body); margin:0;">
                            <?= nl2br(e($mou['description'])) ?>
                        </p>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Info Sidebar -->
            <div>
                <!-- MoU Details -->
                <div class="card mb-3">
                    <div class="card-header">
                        <div class="card-title"><i class="fa-solid fa-circle-info"></i> Informasi Dokumen</div>
                    </div>
                    <div class="card-body">
                        <div class="info-grid">
                            <div class="info-item">
                                <div class="info-label">Tipe Dokumen</div>
                                <div class="info-value"><?= e($mou['doc_type']) ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Kategori</div>
                                <div class="info-value"><?= e($mou['category_name']) ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Tanggal Mulai</div>
                                <div class="info-value"><?= format_date_id($mou['start_date']) ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Tanggal Berakhir</div>
                                <div class="info-value"><?= format_date_id($mou['end_date']) ?></div>
                            </div>
                            <?php if ($mou['unit_name']): ?>
                            <div class="info-item" style="grid-column:1/-1;">
                                <div class="info-label">Unit Kerja</div>
                                <div class="info-value"><?= e($mou['unit_name']) ?></div>
                            </div>
                            <?php endif; ?>
                            <div class="info-item" style="grid-column:1/-1;">
                                <div class="info-label">Sisa Masa Berlaku</div>
                                <div class="info-value">
                                    <?php if ($daysLeft >= 0): ?>
                                        <span style="color:<?= $daysLeft <= 30 ? 'var(--warning)' : 'var(--success)' ?>; font-weight:700;">
                                            <?= $daysLeft ?> hari lagi
                                        </span>
                                    <?php else: ?>
                                        <span style="color:var(--danger); font-weight:700;">
                                            Berakhir <?= abs($daysLeft) ?> hari lalu
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Institution Info -->
                <div class="card mb-3">
                    <div class="card-header">
                        <div class="card-title"><i class="fa-solid fa-building"></i> Institusi Mitra</div>
                    </div>
                    <div class="card-body">
                        <div style="font-weight:700; font-size:.92rem; color:var(--text-main); margin-bottom:10px;">
                            <?= e($mou['institution_name']) ?>
                        </div>
                        <div style="font-size:.78rem; color:var(--text-muted);">
                            <span class="badge badge-primary" style="margin-bottom:10px;"><?= e($mou['institution_category']) ?></span>
                        </div>
                        <?php if ($mou['contact_person']): ?>
                        <div class="info-item" style="margin-bottom:8px;">
                            <div class="info-label">Kontak</div>
                            <div class="info-value"><?= e($mou['contact_person']) ?></div>
                        </div>
                        <?php endif; ?>
                        <?php if ($mou['institution_phone']): ?>
                        <div class="info-item" style="margin-bottom:8px;">
                            <div class="info-label">Telepon</div>
                            <div class="info-value"><?= e($mou['institution_phone']) ?></div>
                        </div>
                        <?php endif; ?>
                        <?php if ($mou['institution_email']): ?>
                        <div class="info-item">
                            <div class="info-label">Email</div>
                            <div class="info-value">
                                <a href="mailto:<?= e($mou['institution_email']) ?>" style="font-size:.82rem;">
                                    <?= e($mou['institution_email']) ?>
                                </a>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Renewal History -->
                <?php if (!empty($renewals)): ?>
                <div class="card history-card" id="publicHistoryCard">
                    <div class="card-header history-card-header" onclick="togglePublicHistory()" title="Klik untuk membuka/menutup histori">
                        <div class="card-title"><i class="fa-solid fa-history" style="color:var(--primary);"></i> Histori Perpanjangan</div>
                        <div style="display:flex; align-items:center; gap:8px;">
                            <span class="badge badge-primary"><?= count($renewals) ?>x</span>
                            <button type="button" class="history-toggle-btn" aria-label="Buka/Tutup Histori Perpanjangan">
                                <i class="fa-solid fa-chevron-down history-toggle-icon"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body history-card-body" style="padding:16px;">
                        <div class="timeline timeline-scrollable">
                            <?php foreach ($renewals as $r): ?>
                            <div class="timeline-item">
                                <div class="timeline-date"><?= format_date_id($r['created_at'], 'medium') ?></div>
                                <div class="timeline-body">
                                    <div style="font-size:.72rem; font-weight:700; color:var(--primary); margin-bottom:3px;">
                                        <?= e($r['renewal_number']) ?>
                                    </div>
                                    <div style="font-size:.78rem;">
                                        <?= format_date_id($r['new_start_date'],'short') ?> –
                                        <?= format_date_id($r['new_end_date'],'short') ?>
                                    </div>
                                    <?php if ($r['notes']): ?>
                                    <div style="font-size:.72rem; color:var(--text-muted); margin-top:3px;">
                                        <?= e($r['notes']) ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

        </div><!-- /grid -->
    </div>
</section>

<script>
function togglePublicHistory() {
    const card = document.getElementById('publicHistoryCard');
    if (card) {
        card.classList.toggle('is-collapsed');
    }
}
document.addEventListener('DOMContentLoaded', function() {
    const card = document.getElementById('publicHistoryCard');
    if (card && window.innerWidth <= 900) {
        card.classList.add('is-collapsed');
    }
});
</script>

