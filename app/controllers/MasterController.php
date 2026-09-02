<?php
/**
 * MasterController.php — Master Data: Kategori MoU
 * CRUD untuk data referensi aplikasi (Kategori)
 * Institusi & Unit Kerja sudah dikelola di InstitutionController & UnitController
 */

// ═══════════════════════════════════════════════════════════════════════════
// KATEGORI MoU
// ═══════════════════════════════════════════════════════════════════════════
function master_categories_index(array $params): void
{
    auth_check();
    $db = get_db();

    $categories = $db->query(
        "SELECT c.*, (SELECT COUNT(*) FROM mous m WHERE m.category_id = c.id) AS mou_count
         FROM categories c ORDER BY c.name"
    )->fetchAll();

    $errors = $_SESSION['_form_errors'] ?? [];
    unset($_SESSION['_form_errors']);

    $pageTitle  = 'Master Data — Kategori MoU';
    $activeMenu = 'master';
    require __DIR__ . '/../views/admin/master/categories.php';
}

function master_categories_create(array $params): void
{
    auth_check();
    if (!verify_csrf()) { http_response_code(403); die('CSRF mismatch'); }
    if (!has_role('superadmin')) {
        flash('error', 'Hanya superadmin yang dapat menambah kategori.');
        header('Location: ' . APP_URL . '/admin/master/categories');
        exit;
    }

    $db   = get_db();
    $name = sanitize($_POST['name'] ?? '');
    $desc = sanitize($_POST['description'] ?? '');

    if (!$name) {
        flash('error', 'Nama kategori wajib diisi.');
        header('Location: ' . APP_URL . '/admin/master/categories');
        exit;
    }

    $db->prepare("INSERT INTO categories (name, description) VALUES (:name, :desc)")
       ->execute([':name' => $name, ':desc' => $desc]);

    log_activity('CREATE_CATEGORY', "Menambahkan kategori MoU: {$name}");
    flash('success', "Kategori '{$name}' berhasil ditambahkan.");
    header('Location: ' . APP_URL . '/admin/master/categories');
    exit;
}

function master_categories_edit(array $params): void
{
    auth_check();
    if (!verify_csrf()) { http_response_code(403); die('CSRF mismatch'); }
    if (!has_role('superadmin')) {
        flash('error', 'Hanya superadmin yang dapat mengubah kategori.');
        header('Location: ' . APP_URL . '/admin/master/categories');
        exit;
    }

    $db   = get_db();
    $id   = (int) ($params['id'] ?? 0);
    $name = sanitize($_POST['name'] ?? '');
    $desc = sanitize($_POST['description'] ?? '');

    if (!$name) {
        flash('error', 'Nama kategori wajib diisi.');
        header('Location: ' . APP_URL . '/admin/master/categories');
        exit;
    }

    $db->prepare("UPDATE categories SET name=:name, description=:desc WHERE id=:id")
       ->execute([':name' => $name, ':desc' => $desc, ':id' => $id]);

    log_activity('UPDATE_CATEGORY', "Memperbarui kategori MoU ID#{$id}: {$name}");
    flash('success', "Kategori '{$name}' berhasil diperbarui.");
    header('Location: ' . APP_URL . '/admin/master/categories');
    exit;
}

function master_categories_delete(array $params): void
{
    auth_check();
    if (!verify_csrf()) { http_response_code(403); die('CSRF mismatch'); }
    if (!has_role('superadmin')) {
        flash('error', 'Hanya superadmin yang dapat menghapus kategori.');
        header('Location: ' . APP_URL . '/admin/master/categories');
        exit;
    }

    $db  = get_db();
    $id  = (int) ($params['id'] ?? 0);
    $row = $db->prepare("SELECT name FROM categories WHERE id=:id");
    $row->execute([':id' => $id]);
    $cat = $row->fetch();

    if (!$cat) {
        flash('error', 'Kategori tidak ditemukan.');
        header('Location: ' . APP_URL . '/admin/master/categories');
        exit;
    }

    // Check usage
    $used = $db->prepare("SELECT COUNT(*) FROM mous WHERE category_id=:id");
    $used->execute([':id' => $id]);
    if ((int)$used->fetchColumn() > 0) {
        flash('error', "Kategori '{$cat['name']}' sedang digunakan oleh MoU dan tidak dapat dihapus.");
        header('Location: ' . APP_URL . '/admin/master/categories');
        exit;
    }

    $db->prepare("DELETE FROM categories WHERE id=:id")->execute([':id' => $id]);
    log_activity('DELETE_CATEGORY', "Menghapus kategori MoU: {$cat['name']}");
    flash('success', "Kategori '{$cat['name']}' berhasil dihapus.");
    header('Location: ' . APP_URL . '/admin/master/categories');
    exit;
}
