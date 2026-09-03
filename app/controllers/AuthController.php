<?php
/**
 * AuthController.php — Login / Logout
 */

require_once __DIR__ . '/../helpers/auth.php';

function auth_login_form(array $params): void
{
    if (isset($_SESSION['user'])) {
        header('Location: ' . APP_URL . '/admin');
        exit;
    }

    // Reset old lockout if expired
    if (!empty($_SESSION['_login_lockout_until']) && $_SESSION['_login_lockout_until'] <= time()) {
        unset($_SESSION['_login_lockout_until'], $_SESSION['_login_attempts']);
    }

    $lockoutRemaining = 0;
    if (!empty($_SESSION['_login_lockout_until'])) {
        $r = (int) $_SESSION['_login_lockout_until'] - time();
        $lockoutRemaining = $r > 0 ? $r : 0;
    }

    $csrfToken = csrf_token();
    $expired   = isset($_GET['expired']) && $_GET['expired'] == '1';

    require __DIR__ . '/../views/auth/login.php';
}

function auth_login_post(array $params): void
{
    $identifier = sanitize($_POST['identifier'] ?? '');
    $password   = $_POST['password'] ?? '';

    $result = attempt_login($identifier, $password);

    if ($result['success']) {
        flash('success', 'Selamat datang, ' . ($result['user']['name'] ?? 'Admin') . '!');
        header('Location: ' . APP_URL . '/admin');
        exit;
    }

    // Failed — store error and redirect back to login
    $_SESSION['_login_error'] = $result['message'];
    header('Location: ' . APP_URL . '/login');
    exit;
}

function auth_logout(array $params): void
{
    do_logout();
    header('Location: ' . APP_URL . '/login');
    exit;
}
