<?php
/**
 * layouts/header.php - Admin Panel HTML Head + Sidebar
 *
 * Required variables:
 *   $pageTitle  string  - title for <title> tag
 *   $activeMenu string  - key of active sidebar item
 */

$user      = current_user();
$initials  = strtoupper(substr($user['name'] ?? 'A', 0, 1));
$flash_ok  = get_flash('success');
$flash_err = get_flash('error');

// Format tanggal Bahasa Indonesia
$_hariId  = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
$_bulanId = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
$_tglNow  = $_hariId[(int)date('w')] . ', ' . date('j') . ' ' . $_bulanId[(int)date('n')] . ' ' . date('Y');
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($pageTitle ?? 'Admin') ?> - <?= e(APP_NAME) ?></title>

    <link rel="icon" type="image/png" href="<?= APP_URL ?>/assets/image/favicon.png">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Tom Select (Searchable Dropdowns) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.min.css">
    <!-- SiMoU CSS (with cache-buster query) -->
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css?v=<?= APP_VERSION ?>.<?= time() ?>">

    <style>
    /* Override tegas: Sembunyikan nama user di navbar mode mobile */
    @media (max-width: 768px) {
        .user-pill-name, #userPillName {
            display: none !important;
            visibility: hidden !important;
            width: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        .user-pill-caret, #userPillCaret {
            display: none !important;
            visibility: hidden !important;
            width: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        .user-pill, #userDropdown {
            padding: 4px !important;
            gap: 0 !important;
            background: transparent !important;
            border-color: transparent !important;
            box-shadow: none !important;
            border-radius: 50% !important;
        }
        .user-avatar-badge {
            width: 34px !important;
            height: 34px !important;
            font-size: .85rem !important;
        }
    }
    </style>


    <script>
    (function() {
        var t = localStorage.getItem('simou-theme') || 'light';
        document.documentElement.setAttribute('data-theme', t);
    })();
    window._APP_URL = '<?= APP_URL ?>';
    window._UPLOAD_MAX_MB = <?= (int) UPLOAD_MAX_MB ?>;
    </script>
</head>
<body>

<!-- Sidebar Overlay (mobile) -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

<div class="app-shell">

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <!-- Close button (mobile only) -->
        <button class="sidebar-close-btn" id="sidebarCloseBtn" onclick="closeSidebar()" aria-label="Tutup menu">
            <i class="fa-solid fa-xmark"></i>
        </button>

        <!-- Sidebar Brand -->
        <div class="sidebar-brand">
            <a href="<?= APP_URL ?>/admin" class="sidebar-brand-link">
                <img src="<?= APP_URL ?>/assets/image/logo.png" alt="SiMoU Logo" class="sidebar-brand-img">
                <div class="sidebar-brand-info">
                    <span class="sidebar-brand-title">SiMoU</span>
                    <span class="sidebar-brand-desc">RSUD Kilisuci</span>
                </div>
            </a>
        </div>

        <!-- Navigation -->
        <nav class="sidebar-nav">
            <div class="sidebar-section-label">Menu Utama</div>

            <a href="<?= APP_URL ?>/admin"
               class="sidebar-item <?= ($activeMenu ?? '') === 'dashboard' ? 'active' : '' ?>">
                <i class="fa-solid fa-chart-pie"></i>
                Dashboard
            </a>

            <a href="<?= APP_URL ?>/admin/mou"
               class="sidebar-item <?= ($activeMenu ?? '') === 'mou' ? 'active' : '' ?>">
                <i class="fa-solid fa-file-contract"></i>
                Dokumen MoU / MoA
            </a>

            <div class="sidebar-section-label">Master Data</div>

            <a href="<?= APP_URL ?>/admin/master/categories"
               class="sidebar-item <?= ($activeMenu ?? '') === 'master' ? 'active' : '' ?>">
                <i class="fa-solid fa-layer-group"></i>
                Kategori MoU
            </a>

            <a href="<?= APP_URL ?>/admin/institutions"
               class="sidebar-item <?= ($activeMenu ?? '') === 'institutions' ? 'active' : '' ?>">
                <i class="fa-solid fa-building"></i>
                Institusi & Mitra
            </a>

            <a href="<?= APP_URL ?>/admin/units"
               class="sidebar-item <?= ($activeMenu ?? '') === 'units' ? 'active' : '' ?>">
                <i class="fa-solid fa-sitemap"></i>
                Unit Kerja Internal
            </a>

            <?php if (has_role('superadmin')): ?>
            <a href="<?= APP_URL ?>/admin/users"
               class="sidebar-item <?= ($activeMenu ?? '') === 'users' ? 'active' : '' ?>">
                <i class="fa-solid fa-users-gear"></i>
                Manajemen Pengguna
            </a>
            <?php endif; ?>

            <div class="sidebar-section-label">Laporan & Log</div>

            <a href="<?= APP_URL ?>/admin/export"
               class="sidebar-item <?= ($activeMenu ?? '') === 'export' ? 'active' : '' ?>">
                <i class="fa-solid fa-file-export"></i>
                Export Data
            </a>

            <a href="<?= APP_URL ?>/admin/logs"
               class="sidebar-item <?= ($activeMenu ?? '') === 'logs' ? 'active' : '' ?>">
                <i class="fa-solid fa-clipboard-list"></i>
                Audit Log
            </a>
            <?php if (has_role('superadmin')): ?>
            <a href="<?= APP_URL ?>/admin/backup"
               class="sidebar-item <?= ($activeMenu ?? '') === 'backup' ? 'active' : '' ?>">
                <i class="fa-solid fa-database"></i>
                Backup &amp; Restore
            </a>
            <?php endif; ?>
        </nav>

        <!-- Sidebar Footer -->
        <div class="sidebar-footer">
            <div class="sidebar-footer-card">
                <div class="sidebar-footer-title"><i class="fa-solid fa-circle-info" style="color:var(--primary-d);"></i> SiMoU RSUD Kilisuci</div>
                <div class="sidebar-footer-subtitle">Sistem Informasi MoU &amp; MoA</div>
                <div class="sidebar-footer-version">v<?= e(APP_VERSION) ?> &bull; ICT RSUD Kilisuci</div>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Topbar / Navbar -->
        <header class="topbar">
            <!-- Hamburger (always visible - CSS hides on desktop) -->
            <button class="sidebar-toggle" id="hamburgerBtn" onclick="toggleSidebar()" aria-label="Buka menu navigasi">
                <i class="fa-solid fa-bars"></i>
            </button>

            <!-- Title -->
            <div class="topbar-title">
                <h4><?= e($pageTitle ?? 'Dashboard') ?></h4>
                <p><?= $_tglNow ?></p>
            </div>

            <!-- Actions -->
            <div class="topbar-actions">
                <!-- Theme Toggle -->
                <button class="btn-theme-toggle" id="btn-theme-toggle" onclick="toggleTheme()" title="Ganti tema" aria-label="Toggle tema">
                    <i class="fa-solid fa-moon" id="themeIcon"></i>
                </button>

                <!-- User Pill Dropdown -->
                <div class="profile-dropdown-wrapper">
                    <button class="user-pill" onclick="toggleProfileDropdown(event)" aria-haspopup="true" id="userDropdown">
                        <span class="user-avatar-badge"><?= e($initials) ?></span>
                        <span class="user-pill-name" id="userPillName"><?= e($user['name'] ?? 'Admin') ?></span>
                        <i class="fa-solid fa-chevron-down user-pill-caret" id="userPillCaret"></i>
                    </button>

                    <div class="profile-dropdown-menu" id="profileDropdownMenu">
                        <div class="profile-dropdown-header">
                            <span class="user-avatar-badge-lg"><?= e($initials) ?></span>
                            <div class="profile-dropdown-details">
                                <div class="profile-dropdown-name"><?= e($user['name'] ?? 'Admin') ?></div>
                                <div class="profile-dropdown-username">@<?= e($user['username'] ?? 'admin') ?></div>
                                <span class="role-badge"><?= e(ucfirst($user['role'] ?? 'admin')) ?></span>
                            </div>
                        </div>
                        <div class="profile-dropdown-divider"></div>
                        <button type="button" class="profile-dropdown-item" onclick="openModal('modalUpdatePassword'); closeProfileDropdown();">
                            <i class="fa-solid fa-key" style="color:var(--primary);"></i> Ubah Password
                        </button>
                        <div class="profile-dropdown-divider"></div>
                        <button type="button" class="profile-dropdown-item danger" onclick="confirmLogout(event); closeProfileDropdown();">
                            <i class="fa-solid fa-right-from-bracket" style="color:var(--danger);"></i> Logout
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <!-- Flash Messages -->
        <?php if ($flash_ok): ?>
        <div style="padding: 16px 28px 0;">
            <div class="alert alert-success">
                <i class="fa-solid fa-circle-check"></i>
                <span><?= e($flash_ok) ?></span>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($flash_err): ?>
        <div style="padding: 16px 28px 0;">
            <div class="alert alert-danger">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <span><?= e($flash_err) ?></span>
            </div>
        </div>
        <?php endif; ?>
