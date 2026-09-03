<?php
/**
 * auth.php — Authentication Helper
 * Login logic adopted from PANTAU Audit (RSUD Kilisuci)
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

/**
 * Proses login: validasi credentials, rate limit, session management.
 *
 * @return array{success:bool, message:string, user?:array}
 */
function attempt_login(string $identifier, string $password): array
{
    // 1. Rate limit check
    $rl = check_rate_limit();
    if ($rl['blocked']) {
        $minutes = ceil($rl['retry_after'] / 60);
        log_activity('LOGIN_BLOCKED', "Login diblokir untuk identifier: {$identifier}");
        return [
            'success'     => false,
            'message'     => "Terlalu banyak percobaan login. Coba lagi dalam {$minutes} menit.",
            'rate_limited'=> true,
            'retry_after' => $rl['retry_after'],
        ];
    }

    // 2. Basic input validation
    $identifier = trim($identifier);
    $password   = trim($password);

    if (empty($identifier) || empty($password)) {
        return ['success' => false, 'message' => 'Username/email dan password wajib diisi.'];
    }

    // 3. Query user from DB (strictly from database)
    $user = null;
    try {
        $db   = get_db();
        $stmt = $db->prepare(
            "SELECT id, username, password, name, email, role, is_active
             FROM users
             WHERE username = :id OR email = :id
             LIMIT 1"
        );
        $stmt->execute([':id' => $identifier]);
        $user = $stmt->fetch();
    } catch (Throwable $e) {
        // Fallback query if is_active column does not exist yet
        try {
            $stmt = $db->prepare(
                "SELECT id, username, password, name, email, role
                 FROM users
                 WHERE username = :id OR email = :id
                 LIMIT 1"
            );
            $stmt->execute([':id' => $identifier]);
            $user = $stmt->fetch();
            if ($user && !isset($user['is_active'])) {
                $user['is_active'] = 1;
            }
        } catch (Throwable $t) {
            error_log('[SiMoU] Login DB warning: ' . $t->getMessage());
        }
    }

    // 4. Verify password
    $validPassword = false;

    if ($user && !empty($user['password']) && password_verify($password, $user['password'])) {
        $validPassword = true;
    }

    if (!$user || !$validPassword) {
        record_failed_login();
        $updatedRl = check_rate_limit();
        $remaining = $updatedRl['remaining'];
        $msg = "Username/email atau password salah.";
        if ($remaining > 0 && $remaining <= 2) {
            $msg .= " Sisa percobaan: {$remaining}x.";
        }
        log_activity('LOGIN_FAILED', "Gagal login untuk identifier: {$identifier}");
        return ['success' => false, 'message' => $msg];
    }

    // 5. Check if account is active / disabled
    if (isset($user['is_active']) && (int) $user['is_active'] === 0) {
        log_activity('LOGIN_DEACTIVATED', "Percobaan login akun nonaktif: {$user['username']} (ID#{$user['id']})", (int) $user['id']);
        return [
            'success' => false,
            'message' => 'Akun Anda saat ini dinonaktifkan oleh Administrator. Silakan hubungi pengelola sistem.'
        ];
    }

    // 5. Login sukses — buat session
    check_rate_limit(true); // reset counter

    if (session_status() === PHP_SESSION_ACTIVE) {
        @session_regenerate_id(true);
    }

    $_SESSION['user'] = [
        'id'       => (int) $user['id'],
        'username' => $user['username'],
        'name'     => $user['name'],
        'email'    => $user['email'],
        'role'     => $user['role'],
    ];
    $_SESSION['_last_activity'] = time();
    $_SESSION['_last_regen']    = time();

    log_activity('LOGIN', "Login berhasil: {$user['username']} ({$user['role']})", (int) $user['id']);

    return ['success' => true, 'message' => 'Login berhasil.', 'user' => $_SESSION['user']];
}

/**
 * Proses logout.
 */
function do_logout(): void
{
    $user = $_SESSION['user'] ?? null;
    if ($user) {
        log_activity('LOGOUT', "Logout: {$user['username']}", (int) $user['id']);
    }

    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    @session_destroy();
}
