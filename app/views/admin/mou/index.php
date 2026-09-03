<?php
/**
 * views/admin/mou/index.php — MoU List with Filters
 */

require __DIR__ . '/../../layouts/header.php';

function pag_link(array $pag, int $targetPage): string
{
    return str_replace('{page}', $targetPage, $pag['url_pattern']);
}
?>

<div class="page-body">

    <!-- Toolbar -->
    <div class="d-flex align-center justify-between mb-3">
        <div>
            <h3 style="font-size:1rem; font-weight:700;">
                <i class="fa-solid fa-file-contract" style="color:var(--primary);"></i>
                Daftar Dokumen MoU / MoA
            </h3>
            <p class="text-muted fs-sm">Total <?= number_format($total) ?> dokumen ditemukan</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= APP_URL ?>/admin/export" class="btn btn-outline btn-sm">
                <i class="fa-solid fa-file-csv"></i> Export CSV
            </a>
            <a href="<?= APP_URL ?>/admin/mou/create" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-plus"></i> Tambah MoU
            </a>
        </div>
    </div>    <!-- Filter Bar -->
    <form method="GET" action="<?= APP_URL ?>/admin/mou" class="filter-bar mb-3">
        <div class="search-box flex-1">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" name="q" placeholder="Cari nomor, judul, atau institusi…"
                   value="<?= e($search ?? '') ?>">
        </div>
        <select name="status" class="form-control" style="width:auto; min-width:160px;">
            <option value="">Semua Status</option>
            <option value="active"        <?= ($status ?? '') === 'active'        ? 'selected' : '' ?>>Aktif</option>
            <option value="expiring_soon" <?= ($status ?? '') === 'expiring_soon' ? 'selected' : '' ?>>Segera Berakhir</option>
            <option value="expired"       <?= ($status ?? '') === 'expired'       ? 'selected' : '' ?>>Berakhir</option>
            <option value="terminated"    <?= ($status ?? '') === 'terminated'    ? 'selected' : '' ?>>Dihentikan</option>
        </select>
        <select name="cat" class="form-control" style="width:auto; min-width:180px;">
            <option value="">Semua Kategori</option>
            <?php foreach ($categories as $cat): ?>
            <option value="<?= $cat['id'] ?>" <?= ($catId ?? 0) == $cat['id'] ? 'selected' : '' ?>>
                <?= e($cat['name']) ?>
            </option>
            <?php endforeach; ?>
        </select>
        <select name="year" class="form-control" style="width:auto; min-width:130px;">
            <option value="">Semua Tahun</option>
            <?php foreach ($years as $y): ?>
            <option value="<?= $y ?>" <?= ($year ?? 0) == $y ? 'selected' : '' ?>><?= $y ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-filter"></i> Filter
        </button>
        <a href="<?= APP_URL ?>/admin/mou" class="btn btn-outline btn-sm">Reset</a>
    </form>

    <!-- Table -->
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Nomor / Tipe</th>
                    <th>Judul Kerjasama &amp; Berkas PDF</th>
                    <th>Institusi</th>
                    <th>Kategori</th>
                    <th>Periode &amp; Masa Habis PKS</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($mous as $m): ?>
                <tr>
                    <td>
                        <div style="font-size:.65rem; color:var(--text-muted); font-weight:700; text-transform:uppercase;">RSUD:</div>
                        <code style="font-size:.72rem; display:block; color:var(--primary); font-weight:700;"><?= e($m['mou_number']) ?></code>
                        <?php if (!empty($m['mou_number_mitra'])): ?>
                        <div style="font-size:.65rem; color:var(--text-muted); font-weight:700; text-transform:uppercase; margin-top:2px;">Mitra:</div>
                        <code style="font-size:.68rem; display:block; color:var(--text-body);"><?= e($m['mou_number_mitra']) ?></code>
                        <?php endif; ?>
                        <span class="badge <?= $m['doc_type'] === 'MoA' ? 'badge-moa' : 'badge-mou' ?>" style="margin-top:4px; display:inline-block;">
                            <?= e($m['doc_type']) ?>
                        </span>
                    </td>
                    <td>
                        <a href="<?= APP_URL ?>/admin/mou/<?= $m['id'] ?>/edit"
                           style="font-weight:600; color:var(--text-main); display:block; margin-bottom:4px;">
                            <?= e(mb_strimwidth($m['title'], 0, 65, '...')) ?>
                        </a>
                        <div class="d-flex align-center gap-2" style="flex-wrap:wrap;">
                            <?php if ($m['unit_name']): ?>
                            <small class="text-muted"><i class="fa-solid fa-sitemap"></i> <?= e($m['unit_name']) ?></small>
                            <?php endif; ?>

                            <!-- Berkas PDF di Kolom Judul -->
                            <?php if (!empty($m['file_path'])): ?>
                            <a href="<?= APP_URL ?>/admin/mou/<?= $m['id'] ?>/document" target="_blank"
                               class="badge" style="background:var(--danger-l); color:var(--danger); border:1px solid rgba(220,38,38,.2); text-decoration:none;" title="Buka Berkas PDF Dokumen Utama">
                                <i class="fa-solid fa-file-pdf"></i> PDF Dokumen
                            </a>
                            <?php else: ?>
                            <span class="badge" style="background:var(--bg-base); color:var(--text-subtle); border:1px solid var(--border);" title="Berkas PDF belum diunggah">
                                <i class="fa-solid fa-file-circle-xmark"></i> No PDF
                            </span>
                            <?php endif; ?>

                            <!-- Info Adendum / Perpanjangan -->
                            <?php if (!empty($m['renewal_count']) && $m['renewal_count'] > 0): ?>
                            <span class="badge" style="background:var(--accent-l); color:var(--accent); border:1px solid rgba(13,148,136,.2);"
                                  title="Terakhir: <?= e($m['latest_renewal_number'] ?? '-') ?> s/d <?= !empty($m['latest_renewal_end_date']) ? format_date_id($m['latest_renewal_end_date'], 'short') : '-' ?>">
                                <i class="fa-solid fa-rotate-right"></i>
                                <?= (int)$m['renewal_count'] ?>x Adendum
                            </span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td><?= e($m['institution_name']) ?></td>
                    <td><span style="font-size:.78rem;"><?= e($m['category_name']) ?></span></td>
                    <td style="white-space:nowrap; font-size:.78rem;">
                        <div style="font-weight:600; color:var(--text-main);">
                            <?= format_date_id($m['start_date'], 'short') ?> s/d <?= format_date_id($m['end_date'], 'short') ?>
                        </div>
                        <?php
                            $d = days_until($m['end_date']);
                            $rem = (int)($m['reminder_days'] ?? 60);
                        ?>
                        <?php if ($m['status'] === 'terminated'): ?>
                            <small style="color:var(--text-muted);"><i class="fa-solid fa-ban"></i> Dihentikan</small>
                        <?php elseif ($d < 0): ?>
                            <small style="color:var(--danger); font-weight:700;">
                                <i class="fa-solid fa-circle-exclamation"></i> Habis <?= abs($d) ?> hari lalu
                            </small>
                        <?php elseif ($d === 0): ?>
                            <small style="color:var(--danger); font-weight:700;">
                                <i class="fa-solid fa-triangle-exclamation"></i> Habis Hari Ini!
                            </small>
                        <?php elseif ($d <= $rem): ?>
                            <small style="color:var(--warning); font-weight:700;">
                                <i class="fa-solid fa-clock"></i> Sisa <?= $d ?> hari (Notif H-<?= $rem ?>)
                            </small>
                        <?php else: ?>
                            <small style="color:var(--success); font-weight:600;">
                                <i class="fa-solid fa-calendar-check"></i> Sisa <?= $d ?> hari
                            </small>
                        <?php endif; ?>
                    </td>
                    <td><?= status_badge($m['status']) ?></td>
                    <td style="white-space:nowrap;">
                        <div style="display:flex; gap:6px; align-items:center;">
                            <!-- 1. Edit -->
                            <a href="<?= APP_URL ?>/admin/mou/<?= $m['id'] ?>/edit"
                               class="btn btn-outline btn-sm" title="Edit Data MoU">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>

                            <!-- 2. Perbarui (Perpanjang / Adendum) -->
                            <a href="<?= APP_URL ?>/admin/mou/<?= $m['id'] ?>/renew"
                               class="btn btn-outline btn-sm" style="color:var(--accent); border-color:rgba(13,148,136,.3);" title="Perbarui / Perpanjang / Adendum MoU">
                                <i class="fa-solid fa-rotate-right"></i>
                            </a>

                            <!-- 3. Hapus (Superadmin) -->
                            <?php if (has_role('superadmin')): ?>
                            <form method="POST" action="<?= APP_URL ?>/admin/mou/<?= $m['id'] ?>/delete"
                                  id="deleteMou<?= $m['id'] ?>" style="display:inline;">
                                <?= csrf_field() ?>
                                <button type="button" class="btn btn-outline btn-sm" style="color:var(--danger); border-color:rgba(220,38,38,.2);" title="Hapus Dokumen"
                                        onclick="confirmDeleteMou('<?= $m['id'] ?>', '<?= e(addslashes(mb_strimwidth($m['title'], 0, 50, '...'))) ?>')">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($mous)): ?>
                <tr>
                    <td colspan="7">
                        <div class="empty-state">
                            <i class="fa-solid fa-folder-open"></i>
                            <h3>Tidak ada dokumen ditemukan</h3>
                            <p>Coba ubah filter atau tambahkan dokumen baru</p>
                        </div>
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <?php if ($pag['total_pages'] > 1): ?>
        <div class="pagination">
            <span>
                Menampilkan <?= number_format($pag['offset'] + 1) ?>–<?= number_format(min($pag['offset'] + $pag['per_page'], $total)) ?>
                dari <?= number_format($total) ?> dokumen
            </span>
            <div class="pagination-links">
                <?php if ($pag['current_page'] > 1): ?>
                <a href="<?= pag_link($pag, 1) ?>" title="Pertama"><i class="fa-solid fa-angles-left"></i></a>
                <a href="<?= pag_link($pag, $pag['current_page'] - 1) ?>"><i class="fa-solid fa-angle-left"></i></a>
                <?php endif; ?>

                <?php for ($i = max(1, $pag['current_page'] - 2); $i <= min($pag['total_pages'], $pag['current_page'] + 2); $i++): ?>
                <?php if ($i === $pag['current_page']): ?>
                    <span class="active"><?= $i ?></span>
                <?php else: ?>
                    <a href="<?= pag_link($pag, $i) ?>"><?= $i ?></a>
                <?php endif; ?>
                <?php endfor; ?>

                <?php if ($pag['current_page'] < $pag['total_pages']): ?>
                <a href="<?= pag_link($pag, $pag['current_page'] + 1) ?>"><i class="fa-solid fa-angle-right"></i></a>
                <a href="<?= pag_link($pag, $pag['total_pages']) ?>" title="Terakhir"><i class="fa-solid fa-angles-right"></i></a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

</div>

<?php
$extraScript = <<<'JS'
function confirmDeleteMou(id, title) {
    showConfirmModal(
        'Yakin hapus dokumen MoU "' + title + '"? Tindakan ini tidak dapat dibatalkan.',
        'Hapus Dokumen MoU',
        function() {
            document.getElementById('deleteMou' + id).submit();
        }
    );
}
JS;
require __DIR__ . '/../../layouts/footer.php';
?>
