<?php
/**
 * views/auth/login.php — Login Page (PANTAU-style)
 * Variables: $csrfToken, $lockoutRemaining, $expired
 */

$loginError = $_SESSION['_login_error'] ?? '';
unset($_SESSION['_login_error']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Login Admin — SiMoU RSUD Kilisuci">
    <meta name="csrf-token" content="<?= e($csrfToken) ?>">
    <meta name="robots" content="noindex">
    <title>Login — <?= e(APP_NAME) ?></title>

    <link rel="icon" type="image/png" href="<?= APP_URL ?>/assets/image/favicon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css?v=<?= APP_VERSION ?>.<?= time() ?>">
</head>
<body>

<div class="login-page">

    <div class="login-card">

        <!-- ─── Left Branding Panel ──────────────────────────── -->
        <div class="login-left">
            <div class="login-brand">
                <div class="login-brand-logo">
                    <img src="<?= APP_URL ?>/assets/image/logo.png" alt="SiMoU Logo" class="login-logo-img">
                    <div class="login-brand-text">
                        <div class="app-name">SiMoU</div>
                        <div class="app-sub">RSUD Kilisuci</div>
                    </div>
                </div>

                <h2 class="login-left-title">Sistem Informasi<br>MoU &amp; MoA<br>RSUD Kilisuci</h2>
                <p class="login-left-desc">
                    Platform terpadu untuk manajemen dokumen Memorandum of Understanding
                    dan Memorandum of Agreement RSUD Kilisuci Kota Kediri.
                </p>
            </div>

            <div class="login-features">
                <div class="login-feature">
                    <div class="login-feature-icon"><i class="fa-solid fa-file-contract"></i></div>
                    Repository Dokumen MoU &amp; MoA
                </div>
                <div class="login-feature">
                    <div class="login-feature-icon"><i class="fa-solid fa-bell"></i></div>
                    Notifikasi Masa Berlaku
                </div>
                <div class="login-feature">
                    <div class="login-feature-icon"><i class="fa-solid fa-chart-bar"></i></div>
                    Dashboard Analitik Interaktif
                </div>
                <div class="login-feature">
                    <div class="login-feature-icon"><i class="fa-solid fa-shield-halved"></i></div>
                    Audit Trail &amp; Keamanan
                </div>
            </div>

            <div class="login-left-footer">
                <div class="login-version-text">
                    <?= e(APP_NAME) ?> v<?= e(APP_VERSION) ?>
                </div>
            </div>
        </div>

        <!-- ─── Right Form Panel ─────────────────────────────── -->
        <div class="login-right">
            <div class="login-form-header">
                <h3>Selamat Datang</h3>
                <p>Masuk ke panel administrator SiMoU</p>
            </div>

            <!-- Session Expired Alert -->
            <?php if ($expired ?? false): ?>
            <div class="alert alert-warning mb-3">
                <i class="fa-solid fa-clock"></i>
                <span>Sesi Anda telah berakhir. Silakan masuk kembali.</span>
            </div>
            <?php endif; ?>

            <!-- Error Alert -->
            <?php if ($loginError): ?>
            <div class="alert alert-danger mb-3" id="errorAlert">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <span><?= e($loginError) ?></span>
            </div>
            <?php endif; ?>

            <!-- Lockout Alert -->
            <?php if (($lockoutRemaining ?? 0) > 0): ?>
            <div class="alert alert-danger mb-3">
                <i class="fa-solid fa-lock"></i>
                <span>Akun sementara dikunci. Coba lagi setelah beberapa menit.</span>
            </div>
            <?php endif; ?>

            <form action="<?= APP_URL ?>/login" method="POST" id="loginForm" onsubmit="return handleLoginSubmit(this)">
                <?= csrf_field() ?>

                <!-- Username / Email -->
                <div class="form-group">
                    <label class="lp-label" for="identifier">Username / Email</label>
                    <input
                        type="text"
                        class="lp-input"
                        id="identifier"
                        name="identifier"
                        placeholder="Masukkan username atau email"
                        autocomplete="username"
                        required autofocus maxlength="100"
                        value="<?= e($_POST['identifier'] ?? '') ?>"
                    >
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label class="lp-label" for="password">Password</label>
                    <div class="lp-input-group">
                        <input
                            type="password"
                            class="lp-input"
                            id="password"
                            name="password"
                            placeholder="Masukkan password"
                            autocomplete="current-password"
                            required maxlength="200"
                        >
                        <button type="button" class="lp-toggle-btn" id="togglePwd" aria-label="Tampilkan password">
                            <i class="fa-solid fa-eye-slash"></i>
                        </button>
                    </div>
                </div>

                <!-- Submit -->
                <button
                    type="submit"
                    class="btn btn-primary btn-lg"
                    id="submitBtn"
                    style="width:100%; margin-top: 8px;"
                    <?= (($lockoutRemaining ?? 0) > 0) ? 'disabled' : '' ?>
                >
                    <span id="btnSpinner" style="display:none;">
                        <i class="fa-solid fa-spinner fa-spin"></i>
                    </span>
                    <i class="fa-solid fa-shield-halved" id="btnIcon"></i>
                    <span id="btnText">Masuk ke SiMoU</span>
                </button>
            </form>

            <div class="login-footer-note" style="margin-top: 20px;">
                <p>&copy; <?= date('Y') ?> Information, Communication &amp; Technology</p>
                <p><strong>RSUD Kilisuci</strong> - Kota Kediri</p>
            </div>
        </div>

    </div><!-- /.login-card -->
</div><!-- /.login-page -->

<div id="toastContainer" class="toast-container"></div>

<script src="<?= APP_URL ?>/assets/js/main.js?v=<?= APP_VERSION ?>.<?= time() ?>"></script>
<script>
// Password toggle
document.getElementById('togglePwd')?.addEventListener('click', function() {
    const pwd  = document.getElementById('password');
    const icon = this.querySelector('i');
    if (pwd.type === 'password') {
        pwd.type = 'text';
        icon.className = 'fa-solid fa-eye';
    } else {
        pwd.type = 'password';
        icon.className = 'fa-solid fa-eye-slash';
    }
});

// Client-side validation & submit loading state
function handleLoginSubmit(form) {
    const id = document.getElementById('identifier').value.trim();
    const pwd = document.getElementById('password').value;

    if (!id || !pwd) {
        showToast('Username/email dan password wajib diisi.', 'warning');
        return false;
    }

    const btn     = document.getElementById('submitBtn');
    const spinner = document.getElementById('btnSpinner');
    const icon    = document.getElementById('btnIcon');
    const text    = document.getElementById('btnText');
    btn.disabled  = true;
    spinner.style.display = 'inline';
    icon.style.display    = 'none';
    text.textContent      = 'Memproses...';
    return true;
}

<?php if ($loginError): ?>
document.addEventListener('DOMContentLoaded', () => {
    showToast(<?= json_encode($loginError) ?>, 'error', 5000);
});
<?php endif; ?>

<?php if ($expired ?? false): ?>
document.addEventListener('DOMContentLoaded', () => {
    showToast('Sesi Anda telah berakhir. Silakan masuk kembali.', 'warning', 5000);
});
<?php endif; ?>

<?php if (($lockoutRemaining ?? 0) > 0): ?>
// Lockout countdown
(function() {
    let remaining = <?= (int) $lockoutRemaining ?>;
    const btn  = document.getElementById('submitBtn');
    const text = document.getElementById('btnText');
    const icon = document.getElementById('btnIcon');
    icon.style.display = 'none';
    function tick() {
        if (remaining <= 0) {
            btn.disabled = false;
            icon.style.display = '';
            text.textContent = 'Masuk ke SiMoU';
            return;
        }
        const m = Math.floor(remaining / 60);
        const s = remaining % 60;
        text.textContent = `Coba lagi ${m}:${String(s).padStart(2,'0')}`;
        remaining--;
        setTimeout(tick, 1000);
    }
    tick();
})();
<?php endif; ?>
</script>

</body>
</html>
