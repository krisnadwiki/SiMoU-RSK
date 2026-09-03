<?php
/**
 * app.php — Global Application Configuration
 * Loads .env, defines constants, starts session, handles timeout,
 * and provides auth/CSRF/rate-limit helpers.
 *
 * Adopts security patterns from PANTAU Audit (RSUD Kilisuci).
 */

require_once __DIR__ . '/env.php';

loadEnv(__DIR__ . '/../.env');

// ── Application Constants ─────────────────────────────────────────────────
define('APP_NAME',              env('APP_NAME',    'SiMoU RSUD Kilisuci'));
define('APP_SHORT',             env('APP_SHORT',   'SiMoU'));
define('APP_VERSION',           env('APP_VERSION', '1.0.0'));

/**
 * Deteksi URL dasar aplikasi (APP_URL) secara dinamis berdasarkan Host & Protocol HTTP Request.
 * Jika APP_URL di .env diisi domain spesifik (selain 'auto', bukan empty, dan bukan localhost saat diakses via IP/Domain),
 * maka nilai .env tersebut yang digunakan.
 */
function get_dynamic_app_url(): string
{
    $envUrl = trim((string) env('APP_URL', ''));

    // Deteksi HTTPS (termasuk di belakang Reverse Proxy / Load Balancer)
    $isHttps = (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off')
        || (($_SERVER['SERVER_PORT'] ?? 80) == 443)
        || (strtolower((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https');

    $scheme = $isHttps ? 'https' : 'http';

    // Jika ada HTTP_HOST dari browser/client request
    if (!empty($_SERVER['HTTP_HOST'])) {
        $host = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST'];

        // Jika APP_URL di .env diset spesifik selain 'auto' & bukan localhost (misal: https://simou.rsudkilisuci.id)
        if (!empty($envUrl) && strtolower($envUrl) !== 'auto') {
            $isEnvLocalhost = str_contains($envUrl, 'localhost') || str_contains($envUrl, '127.0.0.1');
            if (!$isEnvLocalhost) {
                return rtrim($envUrl, '/');
            }
        }

        // Gunakan Host & Port dinamis dari HTTP request
        return $scheme . '://' . $host;
    }

    // Fallback untuk CLI / CRON / Task background di luar HTTP request
    if (!empty($envUrl) && strtolower($envUrl) !== 'auto') {
        return rtrim($envUrl, '/');
    }

    return 'http://localhost:8083';
}

define('APP_URL',               get_dynamic_app_url());

define('LOGIN_MAX_ATTEMPTS',    (int) env('LOGIN_MAX_ATTEMPTS',    5));
define('LOGIN_WINDOW_SECONDS',  (int) env('LOGIN_WINDOW_SECONDS',  600));
define('LOGIN_LOCKOUT_SECONDS', (int) env('LOGIN_LOCKOUT_SECONDS', 900));

// Upload config
define('UPLOAD_MAX_MB',         (int) preg_replace('/[^0-9]/', '', (string) env('UPLOAD_MAX_MB', 30)) ?: 30);
define('UPLOAD_DIR',            __DIR__ . '/../uploads/');
define('UPLOAD_URL',            APP_URL . '/uploads/');

// ── Session Hardening ─────────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly',  '1');
    ini_set('session.cookie_samesite',  'Lax');
    ini_set('session.use_strict_mode',  '1');
    ini_set('session.cookie_secure',    '0');   // set '1' when behind HTTPS
    $tempTimeout = (int) env('SESSION_TIMEOUT', 3600);
    ini_set('session.gc_maxlifetime', (string) $tempTimeout);
    session_start();
}

$dynamicTimeout  = (int) ($_SESSION['_session_timeout'] ?? env('SESSION_TIMEOUT', 3600));
define('SESSION_TIMEOUT', $dynamicTimeout);

$dynamicTimezone = $_SESSION['_timezone'] ?? env('TIMEZONE', 'Asia/Jakarta');
define('APP_TIMEZONE', $dynamicTimezone);

date_default_timezone_set(APP_TIMEZONE);

// ── Session Timeout Check ─────────────────────────────────────────────────
if (isset($_SESSION['user'])) {
    if (isset($_SESSION['_last_activity']) && (time() - $_SESSION['_last_activity'] > SESSION_TIMEOUT)) {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
        header('Location: ' . APP_URL . '/auth/login?expired=1');
        exit;
    }
    $_SESSION['_last_activity'] = time();
}

// ── Security Headers ──────────────────────────────────────────────────────
if (!headers_sent()) {
    header('X-Frame-Options: SAMEORIGIN');
    header('X-XSS-Protection: 1; mode=block');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
}

// ─────────────────────────────────────────────────────────────────────────
// Auth Helpers
// ─────────────────────────────────────────────────────────────────────────

/**
 * Lindungi halaman admin — redirect ke login jika belum auth.
 */
function auth_check(): void
{
    if (!isset($_SESSION['user'])) {
        header('Location: ' . APP_URL . '/auth/login');
        exit;
    }
    // Regenerate session ID setiap 30 menit
    if (!isset($_SESSION['_last_regen'])) {
        $_SESSION['_last_regen'] = time();
    } elseif (time() - $_SESSION['_last_regen'] > 1800) {
        session_regenerate_id(true);
        $_SESSION['_last_regen'] = time();
    }
}

/**
 * Ambil data user yang sedang login.
 */
function current_user(): array
{
    return $_SESSION['user'] ?? [];
}

/**
 * Cek apakah user memiliki role tertentu.
 */
function has_role(string ...$roles): bool
{
    return in_array($_SESSION['user']['role'] ?? '', $roles, true);
}

// ─────────────────────────────────────────────────────────────────────────
// CSRF Protection
// ─────────────────────────────────────────────────────────────────────────

function csrf_token(): string
{
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
}

function verify_csrf(): bool
{
    $token = $_POST['_csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    return !empty($_SESSION['_csrf_token']) && hash_equals($_SESSION['_csrf_token'], $token);
}

// ─────────────────────────────────────────────────────────────────────────
// Rate Limiting (Session-based, adopted from PANTAU)
// ─────────────────────────────────────────────────────────────────────────

/**
 * @return array{blocked:bool, remaining:int, retry_after:int}
 */
function check_rate_limit(bool $reset = false): array
{
    $max     = LOGIN_MAX_ATTEMPTS;
    $window  = LOGIN_WINDOW_SECONDS;
    $lockout = LOGIN_LOCKOUT_SECONDS;

    if ($reset) {
        unset($_SESSION['_login_attempts'], $_SESSION['_login_lockout_until']);
        return ['blocked' => false, 'remaining' => $max, 'retry_after' => 0];
    }

    if (!empty($_SESSION['_login_lockout_until'])) {
        $retryAfter = (int) $_SESSION['_login_lockout_until'] - time();
        if ($retryAfter > 0) {
            return ['blocked' => true, 'remaining' => 0, 'retry_after' => $retryAfter];
        }
        unset($_SESSION['_login_attempts'], $_SESSION['_login_lockout_until']);
    }

    if (empty($_SESSION['_login_attempts'])) {
        $_SESSION['_login_attempts'] = [];
    }

    $now = time();
    $_SESSION['_login_attempts'] = array_values(array_filter(
        $_SESSION['_login_attempts'],
        fn($t) => ($now - $t) < $window
    ));

    $count = count($_SESSION['_login_attempts']);
    if ($count >= $max) {
        $_SESSION['_login_lockout_until'] = $now + $lockout;
        return ['blocked' => true, 'remaining' => 0, 'retry_after' => $lockout];
    }

    return ['blocked' => false, 'remaining' => $max - $count, 'retry_after' => 0];
}

function record_failed_login(): void
{
    if (empty($_SESSION['_login_attempts'])) {
        $_SESSION['_login_attempts'] = [];
    }
    $_SESSION['_login_attempts'][] = time();
}
