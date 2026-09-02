<?php
/**
 * UnitController.php — Manage Internal RSUD Units
 */

function unit_index(array $params): void
{
    auth_check();
    $db    = get_db();
    $units = $db->query(
        "SELECT u.*, (SELECT COUNT(*) FROM mous m WHERE m.unit_id=u.id) AS mou_count
         FROM units u ORDER BY u.name"
    )->fetchAll();

    $pageTitle  = 'Unit Kerja Internal';
    $activeMenu = 'units';
    require __DIR__ . '/../views/admin/units.php';
}

function unit_create(array $params): void
{
    auth_check();
    if (!verify_csrf()) { http_response_code(403); die('CSRF'); }

    $name = sanitize($_POST['name'] ?? '');
    if (!$name) { flash('error', 'Nama unit wajib diisi.'); header('Location: ' . APP_URL . '/admin/units'); exit; }

    $db = get_db();
    $db->prepare("INSERT INTO units (name) VALUES (:name)")->execute([':name' => $name]);
    log_activity('CREATE_UNIT', "Menambahkan unit kerja: {$name}");
    flash('success', "Unit '{$name}' berhasil ditambahkan.");
    header('Location: ' . APP_URL . '/admin/units');
    exit;
}

function unit_edit(array $params): void
{
    auth_check();
    if (!verify_csrf()) { http_response_code(403); die('CSRF'); }

    $id   = (int) ($params['id'] ?? 0);
    $name = sanitize($_POST['name'] ?? '');

    if (!$name) { flash('error', 'Nama unit wajib diisi.'); header('Location: ' . APP_URL . '/admin/units'); exit; }

    $db = get_db();
    $db->prepare("UPDATE units SET name=:name WHERE id=:id")->execute([':name' => $name, ':id' => $id]);
    log_activity('UPDATE_UNIT', "Memperbarui unit kerja ID#{$id}: {$name}");
    flash('success', "Unit berhasil diperbarui.");
    header('Location: ' . APP_URL . '/admin/units');
    exit;
}

function unit_delete(array $params): void
{
    auth_check();
    if (!verify_csrf()) { http_response_code(403); die('CSRF'); }
    if (!has_role('superadmin')) { flash('error', 'Akses ditolak.'); header('Location: ' . APP_URL . '/admin/units'); exit; }

    $db  = get_db();
    $id  = (int) ($params['id'] ?? 0);
    $row = $db->prepare("SELECT name FROM units WHERE id=:id");
    $row->execute([':id' => $id]);
    $unit = $row->fetch();
    if (!$unit) { flash('error', 'Unit tidak ditemukan.'); header('Location: ' . APP_URL . '/admin/units'); exit; }

    $db->prepare("DELETE FROM units WHERE id=:id")->execute([':id' => $id]);
    log_activity('DELETE_UNIT', "Menghapus unit kerja: {$unit['name']}");
    flash('success', "Unit '{$unit['name']}' berhasil dihapus.");
    header('Location: ' . APP_URL . '/admin/units');
    exit;
}
