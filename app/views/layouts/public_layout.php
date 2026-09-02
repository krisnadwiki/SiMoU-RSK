<?php
/**
 * layouts/public_layout.php — Public Portal Layout
 * Wraps public pages with nav and footer
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= e($metaDesc ?? 'Repositori Dokumen MoU dan MoA RSUD Kilisuci Kota Kediri') ?>">
    <title><?= e($pageTitle ?? 'Portal MoU') ?> — <?= e(APP_NAME) ?></title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css">
</head>
<body>

<!-- Public Navigation -->
<nav class="public-nav">
    <a href="<?= APP_URL ?>/" class="public-nav-brand">
        <div class="public-nav-icon"><i class="fa-solid fa-handshake"></i></div>
        <div>
            <div class="public-nav-brand-name">SiMoU</div>
            <div class="public-nav-brand-sub">RSUD Kilisuci</div>
        </div>
    </a>

    <div class="public-nav-links">
        <a href="<?= APP_URL ?>/" class="<?= ($activePage ?? '') === 'home' ? 'active' : '' ?>">
            <i class="fa-solid fa-house"></i> Beranda
        </a>
        <a href="<?= APP_URL ?>/catalog" class="<?= ($activePage ?? '') === 'catalog' ? 'active' : '' ?>">
            <i class="fa-solid fa-folder-open"></i> Katalog MoU
        </a>
    </div>

    <div class="public-nav-cta">
        <a href="<?= APP_URL ?>/auth/login" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-lock"></i> Admin Login
        </a>
    </div>
</nav>

<!-- Page Content -->
<?= $content ?? '' ?>

<!-- Public Footer -->
<footer style="background: var(--bg-sidebar); color: rgba(255,255,255,.55); padding: 28px 24px; text-align: center; font-size: .75rem; margin-top: 60px;">
    <p style="margin-bottom: 4px;">
        <strong style="color: rgba(255,255,255,.8);">SiMoU</strong> — Sistem Informasi MoU & MoA RSUD Kilisuci
    </p>
    <p>&copy; <?= date('Y') ?> Information, Communication and Technology — RSUD Kilisuci, Kota Kediri</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script src="<?= APP_URL ?>/assets/js/main.js"></script>
<?php if (isset($extraScript)): ?>
<script><?= $extraScript ?></script>
<?php endif; ?>
</body>
</html>
