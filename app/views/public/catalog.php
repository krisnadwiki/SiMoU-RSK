<?php
/**
 * views/public/catalog.php — Public MoU Catalog with Search & Filter
 */
?>

<!-- Catalog Header -->
<div style="background:var(--bg-sidebar); padding:32px 24px 36px; color:#fff;">
    <div class="public-container">
        <div style="font-size:.72rem; font-weight:700; color:rgba(255,255,255,.5);
                    text-transform:uppercase; letter-spacing:.1em; margin-bottom:8px;">
            <i class="fa-solid fa-folder-open"></i> Katalog Dokumen
        </div>
        <h1 style="color:#fff; font-size:1.8rem; margin-bottom:12px; letter-spacing:-.03em;">
            Katalog MoU &amp; MoA
        </h1>
        <p style="color:rgba(255,255,255,.65); font-size:.9rem; max-width:560px;">
            Seluruh dokumen kerjasama RSUD Kilisuci yang dapat diakses secara publik.
        </p>

        <!-- Search -->
        <form method="GET" action="<?= APP_URL ?>/catalog"
              style="display:flex; gap:10px; margin-top:20px; flex-wrap:wrap; max-width:700px;">
            <div class="search-box flex-1" style="min-width:200px;">
                <i class="fa-solid fa-magnifying-glass" style="color:var(--text-muted);"></i>
                <input type="text" name="q" placeholder="Cari judul, nomor, atau institusi…"
                       value="<?= e($search ?? '') ?>"
                       style="border-radius:var(--radius); border:none; box-shadow:var(--shadow-sm);">
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-magnifying-glass"></i> Cari
            </button>
        </form>
    </div>
</div>

<section class="section" style="padding-top:28px;">
    <div class="public-container">

        <!-- Filter Row -->
        <form method="GET" action="<?= APP_URL ?>/catalog"
              style="display:flex; gap:10px; margin-bottom:20px; flex-wrap:wrap; align-items:center;">
            <?php if ($search ?? ''): ?>
            <input type="hidden" name="q" value="<?= e($search) ?>">
            <?php endif; ?>

            <select name="status" class="form-control" style="width:auto; min-width:160px;"
                    onchange="this.form.submit()">
                <option value="">Semua Status</option>
                <option value="active"        <?= ($status ?? '') === 'active'        ? 'selected' : '' ?>>Aktif</option>
                <option value="expiring_soon" <?= ($status ?? '') === 'expiring_soon' ? 'selected' : '' ?>>Segera Berakhir</option>
                <option value="expired"       <?= ($status ?? '') === 'expired'       ? 'selected' : '' ?>>Berakhir</option>
            </select>

            <select name="cat" class="form-control" style="width:auto; min-width:190px;"
                    onchange="this.form.submit()">
                <option value="">Semua Kategori</option>
                <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= ($catId ?? 0) == $cat['id'] ? 'selected' : '' ?>>
                    <?= e($cat['name']) ?>
                </option>
                <?php endforeach; ?>
            </select>

            <select name="year" class="form-control" style="width:auto; min-width:120px;"
                    onchange="this.form.submit()">
                <option value="">Semua Tahun</option>
                <?php foreach ($years as $y): ?>
                <option value="<?= $y ?>" <?= ($year ?? 0) == $y ? 'selected' : '' ?>><?= $y ?></option>
                <?php endforeach; ?>
            </select>

            <span style="font-size:.8rem; color:var(--text-muted);">
                <?= number_format($total) ?> dokumen ditemukan
            </span>

            <?php if (($search ?? '') || ($status ?? '') || ($catId ?? 0) || ($year ?? 0)): ?>
            <a href="<?= APP_URL ?>/catalog" class="btn btn-outline btn-sm">
                <i class="fa-solid fa-xmark"></i> Reset
            </a>
            <?php endif; ?>
        </form>

        <!-- MoU Grid -->
        <div class="mou-grid">
            <?php foreach ($mous as $m): ?>
            <a href="<?= APP_URL ?>/mou/<?= $m['id'] ?>" class="mou-card <?= e($m['status']) ?>"
               style="text-decoration:none;">
                <div class="mou-card-header">
                    <div style="flex:1; min-width:0;">
                        <div class="mou-card-number"><?= e($m['mou_number']) ?></div>
                        <div class="mou-card-title"><?= e(mb_strimwidth($m['title'], 0, 65, '…')) ?></div>
                    </div>
                    <span class="badge <?= $m['doc_type'] === 'MoA' ? 'badge-moa' : 'badge-mou' ?>"
                          style="flex-shrink:0;"><?= e($m['doc_type']) ?></span>
                </div>

                <div class="mou-card-institution">
                    <i class="fa-solid fa-building"></i>
                    <?= e($m['institution_name']) ?>
                </div>

                <div style="font-size:.72rem; color:var(--text-subtle);">
                    <i class="fa-solid fa-tag"></i> <?= e($m['category_name']) ?>
                </div>

                <div class="mou-card-footer">
                    <div class="mou-card-date">
                        <i class="fa-regular fa-calendar"></i>
                        <?= format_date_id($m['start_date'], 'short') ?> – <?= format_date_id($m['end_date'], 'short') ?>
                    </div>
                    <?= status_badge($m['status']) ?>
                </div>
            </a>
            <?php endforeach; ?>

            <?php if (empty($mous)): ?>
            <div class="empty-state" style="grid-column:1/-1;">
                <i class="fa-solid fa-folder-open"></i>
                <h3>Tidak ada dokumen ditemukan</h3>
                <p>Coba ubah kata kunci pencarian atau filter</p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ($pag['total_pages'] > 1): ?>
        <div style="display:flex; justify-content:center; margin-top:28px;">
            <div class="pagination-links">
                <?php if ($pag['current_page'] > 1): ?>
                <a href="<?= str_replace('{page}', $pag['current_page']-1, $pag['url_pattern']) ?>">
                    <i class="fa-solid fa-angle-left"></i>
                </a>
                <?php endif; ?>

                <?php for ($i = max(1,$pag['current_page']-2); $i <= min($pag['total_pages'], $pag['current_page']+2); $i++): ?>
                <?php if ($i === $pag['current_page']): ?>
                    <span class="active"><?= $i ?></span>
                <?php else: ?>
                    <a href="<?= str_replace('{page}', $i, $pag['url_pattern']) ?>"><?= $i ?></a>
                <?php endif; ?>
                <?php endfor; ?>

                <?php if ($pag['current_page'] < $pag['total_pages']): ?>
                <a href="<?= str_replace('{page}', $pag['current_page']+1, $pag['url_pattern']) ?>">
                    <i class="fa-solid fa-angle-right"></i>
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

    </div>
</section>
