<?php
/**
 * InstitutionController.php — Manage Partner Institutions
 */

function inst_index(array $params): void
{
    auth_check();
    $db = get_db();

    $search  = sanitize($_GET['q'] ?? '');
    $page    = max(1, (int) ($_GET['page'] ?? 1));
    $perPage = 20;
    $binds   = [];
    $where   = '1=1';

    if ($search) {
        $where        = "(name LIKE :q OR contact_person LIKE :q OR email LIKE :q)";
        $binds[':q']  = "%{$search}%";
    }

    $total = (int) $db->prepare("SELECT COUNT(*) FROM institutions WHERE {$where}")
                       ->execute($binds) ? (int) $db->prepare("SELECT COUNT(*) FROM institutions WHERE {$where}")->execute($binds) : 0;

    $totalStmt = $db->prepare("SELECT COUNT(*) FROM institutions WHERE {$where}");
    $totalStmt->execute($binds);
    $total = (int) $totalStmt->fetchColumn();

    $pag = paginate($total, $perPage, $page,
        APP_URL . '/admin/institutions?q=' . urlencode($search) . '&page={page}'
    );

    $stmt = $db->prepare(
        "SELECT i.*, (SELECT COUNT(*) FROM mous m WHERE m.institution_id=i.id) AS mou_count
         FROM institutions i WHERE {$where}
         ORDER BY i.name LIMIT :lim OFFSET :off"
    );
    foreach ($binds as $k => $v) $stmt->bindValue($k, $v);
    $stmt->bindValue(':lim', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':off', $pag['offset'], PDO::PARAM_INT);
    $stmt->execute();
    $institutions = $stmt->fetchAll();

    $errors = $_SESSION['_form_errors'] ?? [];
    unset($_SESSION['_form_errors']);

    $pageTitle  = 'Institusi & Mitra';
    $activeMenu = 'institutions';
    require __DIR__ . '/../views/admin/institutions.php';
}

function inst_create(array $params): void
{
    auth_check();
    if (!verify_csrf()) { http_response_code(403); die('CSRF mismatch'); }

    $db   = get_db();
    $data = [
        'name'           => sanitize($_POST['name'] ?? ''),
        'category'       => sanitize($_POST['category'] ?? 'Pendidikan'),
        'address'        => sanitize($_POST['address'] ?? ''),
        'contact_person' => sanitize($_POST['contact_person'] ?? ''),
        'phone'          => sanitize($_POST['phone'] ?? ''),
        'email'          => sanitize($_POST['email'] ?? ''),
    ];

    if (!$data['name']) {
        flash('error', 'Nama institusi wajib diisi.');
        header('Location: ' . APP_URL . '/admin/institutions');
        exit;
    }

    $stmt = $db->prepare(
        "INSERT INTO institutions (name,category,address,contact_person,phone,email)
         VALUES (:name,:category,:address,:contact_person,:phone,:email)"
    );
    $stmt->execute($data);

    log_activity('CREATE_INSTITUTION', "Menambahkan institusi: {$data['name']}");
    flash('success', "Institusi '{$data['name']}' berhasil ditambahkan.");
    header('Location: ' . APP_URL . '/admin/institutions');
    exit;
}

function inst_edit(array $params): void
{
    auth_check();
    if (!verify_csrf()) { http_response_code(403); die('CSRF mismatch'); }

    $db   = get_db();
    $id   = (int) ($params['id'] ?? 0);
    $data = [
        'name'           => sanitize($_POST['name'] ?? ''),
        'category'       => sanitize($_POST['category'] ?? ''),
        'address'        => sanitize($_POST['address'] ?? ''),
        'contact_person' => sanitize($_POST['contact_person'] ?? ''),
        'phone'          => sanitize($_POST['phone'] ?? ''),
        'email'          => sanitize($_POST['email'] ?? ''),
    ];

    $stmt = $db->prepare(
        "UPDATE institutions SET name=:name,category=:category,address=:address,
         contact_person=:contact_person,phone=:phone,email=:email WHERE id=:id"
    );
    $data[':id'] = $id;
    $stmt->execute(array_merge(
        [':name'=>$data['name'],':category'=>$data['category'],':address'=>$data['address'],
         ':contact_person'=>$data['contact_person'],':phone'=>$data['phone'],':email'=>$data['email']],
        [':id'=>$id]
    ));

    log_activity('UPDATE_INSTITUTION', "Memperbarui institusi ID#{$id}: {$data['name']}");
    flash('success', "Institusi berhasil diperbarui.");
    header('Location: ' . APP_URL . '/admin/institutions');
    exit;
}

function inst_delete(array $params): void
{
    auth_check();
    if (!verify_csrf()) { http_response_code(403); die('CSRF mismatch'); }
    if (!has_role('superadmin')) {
        flash('error', 'Hanya superadmin yang dapat menghapus institusi.');
        header('Location: ' . APP_URL . '/admin/institutions');
        exit;
    }

    $db  = get_db();
    $id  = (int) ($params['id'] ?? 0);
    $row = $db->prepare("SELECT name FROM institutions WHERE id=:id");
    $row->execute([':id' => $id]);
    $inst = $row->fetch();

    if (!$inst) { flash('error', 'Institusi tidak ditemukan.'); header('Location: ' . APP_URL . '/admin/institutions'); exit; }

    $db->prepare("DELETE FROM institutions WHERE id=:id")->execute([':id' => $id]);
    log_activity('DELETE_INSTITUTION', "Menghapus institusi: {$inst['name']}");
    flash('success', "Institusi '{$inst['name']}' berhasil dihapus.");
    header('Location: ' . APP_URL . '/admin/institutions');
    exit;
}
