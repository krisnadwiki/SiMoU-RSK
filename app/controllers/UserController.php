<?php
/**
 * UserController.php — Manage Admin Users (Superadmin only)
 */

function user_index(array $params): void
{
    auth_check();
    if (!has_role('superadmin')) {
        flash('error', 'Akses terbatas untuk superadmin.');
        header('Location: ' . APP_URL . '/admin');
        exit;
    }

    $db    = get_db();
    $users = $db->query("SELECT id, username, name, email, role, created_at FROM users ORDER BY name")->fetchAll();

    $pageTitle  = 'Manajemen Pengguna';
    $activeMenu = 'users';
    require __DIR__ . '/../views/admin/users.php';
}

function user_create(array $params): void
{
    auth_check();
    if (!verify_csrf()) { http_response_code(403); die('CSRF mismatch'); }
    if (!has_role('superadmin')) { flash('error', 'Akses ditolak.'); header('Location: ' . APP_URL . '/admin'); exit; }

    $username = sanitize($_POST['username'] ?? '');
    $name     = sanitize($_POST['name'] ?? '');
    $email    = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['password_confirm'] ?? '';
    $role     = in_array($_POST['role'] ?? '', ['admin', 'superadmin'], true) ? $_POST['role'] : 'admin';

    if (!$username || !$name || !$email || !$password) {
        flash('error', 'Semua field wajib diisi.');
        header('Location: ' . APP_URL . '/admin/users');
        exit;
    }

    if ($password !== $confirm) {
        flash('error', 'Konfirmasi password tidak cocok dengan password baru.');
        header('Location: ' . APP_URL . '/admin/users');
        exit;
    }

    $db = get_db();

    // Check duplicate username/email
    $dup = $db->prepare("SELECT id FROM users WHERE username = :u OR email = :e LIMIT 1");
    $dup->execute([':u' => $username, ':e' => $email]);
    if ($dup->fetch()) {
        flash('error', 'Username atau email sudah digunakan.');
        header('Location: ' . APP_URL . '/admin/users');
        exit;
    }

    $hashed = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
    $stmt   = $db->prepare(
        "INSERT INTO users (username, password, name, email, role)
         VALUES (:u, :p, :n, :e, :r)"
    );
    $stmt->execute([
        ':u' => $username,
        ':p' => $hashed,
        ':n' => $name,
        ':e' => $email,
        ':r' => $role,
    ]);

    log_activity('CREATE_USER', "Menambahkan pengguna baru: {$username} ({$role})");
    flash('success', "Pengguna '{$name}' berhasil ditambahkan.");
    header('Location: ' . APP_URL . '/admin/users');
    exit;
}

function user_edit(array $params): void
{
    auth_check();
    if (!verify_csrf()) { http_response_code(403); die('CSRF mismatch'); }
    if (!has_role('superadmin')) { flash('error', 'Akses ditolak.'); header('Location: ' . APP_URL . '/admin'); exit; }

    $id       = (int) ($params['id'] ?? 0);
    $name     = sanitize($_POST['name'] ?? '');
    $email    = sanitize($_POST['email'] ?? '');
    $role     = in_array($_POST['role'] ?? '', ['admin', 'superadmin'], true) ? $_POST['role'] : 'admin';
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['password_confirm'] ?? '';

    if (!$name || !$email) {
        flash('error', 'Nama dan email wajib diisi.');
        header('Location: ' . APP_URL . '/admin/users');
        exit;
    }

    $db = get_db();

    if ($password !== '') {
        if ($password !== $confirm) {
            flash('error', 'Konfirmasi password baru tidak cocok.');
            header('Location: ' . APP_URL . '/admin/users');
            exit;
        }

        $hashed = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
        $stmt   = $db->prepare(
            "UPDATE users SET name=:n, email=:e, role=:r, password=:p WHERE id=:id"
        );
        $stmt->execute([':n' => $name, ':e' => $email, ':r' => $role, ':p' => $hashed, ':id' => $id]);
    } else {
        $stmt = $db->prepare(
            "UPDATE users SET name=:n, email=:e, role=:r WHERE id=:id"
        );
        $stmt->execute([':n' => $name, ':e' => $email, ':r' => $role, ':id' => $id]);
    }

    log_activity('UPDATE_USER', "Memperbarui pengguna ID#{$id}: {$name}");
    flash('success', "Data pengguna berhasil diperbarui.");
    header('Location: ' . APP_URL . '/admin/users');
    exit;
}

function user_delete(array $params): void
{
    auth_check();
    if (!verify_csrf()) { http_response_code(403); die('CSRF mismatch'); }
    if (!has_role('superadmin')) { flash('error', 'Akses ditolak.'); header('Location: ' . APP_URL . '/admin'); exit; }

    $id   = (int) ($params['id'] ?? 0);
    $curr = current_user()['id'] ?? 0;

    if ($id === $curr) {
        flash('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        header('Location: ' . APP_URL . '/admin/users');
        exit;
    }

    $db  = get_db();
    $row = $db->prepare("SELECT username FROM users WHERE id=:id");
    $row->execute([':id' => $id]);
    $u = $row->fetch();

    if ($u) {
        $db->prepare("DELETE FROM users WHERE id=:id")->execute([':id' => $id]);
        log_activity('DELETE_USER', "Menghapus pengguna: {$u['username']}");
        flash('success', "Pengguna '{$u['username']}' berhasil dihapus.");
    }

    header('Location: ' . APP_URL . '/admin/users');
    exit;
}

/**
 * Update password milik user yang sedang login saat ini (wajib verifikasi password lama)
 */
function user_update_own_password(array $params): void
{
    auth_check();
    if (!verify_csrf()) { http_response_code(403); die('CSRF mismatch'); }

    $user             = current_user();
    $userId           = (int) ($user['id'] ?? 0);
    $current_password = $_POST['current_password'] ?? '';
    $password         = $_POST['password'] ?? '';
    $confirm          = $_POST['password_confirm'] ?? '';

    if (empty($current_password)) {
        flash('error', 'Password lama (saat ini) wajib diisi untuk keamanan.');
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? APP_URL . '/admin'));
        exit;
    }

    if (empty($password) || empty($confirm)) {
        flash('error', 'Password baru dan konfirmasi password wajib diisi.');
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? APP_URL . '/admin'));
        exit;
    }

    $db   = get_db();
    $stmt = $db->prepare("SELECT password FROM users WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $userId]);
    $userRow = $stmt->fetch();

    if (!$userRow || empty($userRow['password']) || !password_verify($current_password, $userRow['password'])) {
        flash('error', 'Password lama yang Anda masukkan salah!');
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? APP_URL . '/admin'));
        exit;
    }

    if ($password !== $confirm) {
        flash('error', 'Konfirmasi password baru tidak cocok.');
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? APP_URL . '/admin'));
        exit;
    }

    // Validasi penguatan keamanan password
    if (strlen($password) < 8) {
        flash('error', 'Password terlalu pendek. Panjang password minimal 8 karakter.');
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? APP_URL . '/admin'));
        exit;
    }
    if (!preg_match('/[A-Z]/', $password)) {
        flash('error', 'Password harus mengandung minimal 1 huruf besar (uppercase A-Z).');
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? APP_URL . '/admin'));
        exit;
    }
    if (!preg_match('/[a-z]/', $password)) {
        flash('error', 'Password harus mengandung minimal 1 huruf kecil (lowercase a-z).');
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? APP_URL . '/admin'));
        exit;
    }
    if (!preg_match('/[0-9]/', $password)) {
        flash('error', 'Password harus mengandung minimal 1 angka (0-9).');
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? APP_URL . '/admin'));
        exit;
    }

    $hashed    = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
    $updateStmt = $db->prepare("UPDATE users SET password = :p WHERE id = :id");
    $updateStmt->execute([':p' => $hashed, ':id' => $userId]);

    log_activity('UPDATE_PASSWORD', "Memperbarui password akun sendiri: {$user['username']}", $userId);
    flash('success', 'Password Anda berhasil diperbarui. Gunakan password baru untuk login berikutnya.');
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? APP_URL . '/admin'));
    exit;
}
