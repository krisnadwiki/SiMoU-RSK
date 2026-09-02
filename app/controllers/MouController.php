<?php
/**
 * MouController.php — Full CRUD MoU/MoA + Renewal
 * ID MoU menggunakan UUID CHAR(36) untuk keamanan URL
 */

require_once __DIR__ . '/../helpers/upload.php';

// ─── Helper: update computed statuses ────────────────────────────────────
function _sync_mou_statuses(PDO $db): void
{
    if (!empty($_SESSION['_last_status_sync']) && (time() - $_SESSION['_last_status_sync']) < 60) {
        return;
    }
    $_SESSION['_last_status_sync'] = time();

    // Do not overwrite 'terminated'
    $db->exec(
        "UPDATE mous
         SET status = CASE
             WHEN status = 'terminated' THEN 'terminated'
             WHEN end_date < CURDATE() THEN 'expired'
             WHEN DATEDIFF(end_date, CURDATE()) <= reminder_days THEN 'expiring_soon'
             ELSE 'active'
         END
         WHERE status != 'terminated'"
    );
}

// ─── Helper: validate UUID format ────────────────────────────────────────
function _is_valid_uuid(string $uuid): bool
{
    return (bool) preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $uuid);
}

// ─── Helper: load select options ─────────────────────────────────────────
function _mou_form_options(PDO $db): array
{
    return [
        'institutions' => $db->query("SELECT id, name FROM institutions ORDER BY name")->fetchAll(),
        'categories'   => $db->query("SELECT id, name FROM categories ORDER BY name")->fetchAll(),
        'units'        => $db->query("SELECT id, name FROM units ORDER BY name")->fetchAll(),
    ];
}

// ═══════════════════════════════════════════════════════════════════════════
// LIST
// ═══════════════════════════════════════════════════════════════════════════
function mou_index(array $params): void
{
    auth_check();
    $db = get_db();
    _sync_mou_statuses($db);

    $search   = sanitize($_GET['q'] ?? '');
    $status   = sanitize($_GET['status'] ?? '');
    $catId    = (int) ($_GET['cat'] ?? 0);
    $year     = (int) ($_GET['year'] ?? 0);
    $page     = max(1, (int) ($_GET['page'] ?? 1));
    $perPage  = 15;

    $where  = ['1=1'];
    $binds  = [];

    if ($search) {
        $where[]  = "(m.title LIKE :q1 OR m.mou_number LIKE :q2 OR m.mou_number_mitra LIKE :q3 OR i.name LIKE :q4)";
        $binds[':q1'] = "%{$search}%";
        $binds[':q2'] = "%{$search}%";
        $binds[':q3'] = "%{$search}%";
        $binds[':q4'] = "%{$search}%";
    }
    if ($status) {
        $where[]        = "m.status = :status";
        $binds[':status'] = $status;
    }
    if ($catId) {
        $where[]       = "m.category_id = :cat";
        $binds[':cat'] = $catId;
    }
    if ($year) {
        $where[]       = "YEAR(m.start_date) = :year";
        $binds[':year'] = $year;
    }

    $whereStr = implode(' AND ', $where);

    $totalStmt = $db->prepare(
        "SELECT COUNT(*) FROM mous m
         JOIN institutions i ON i.id = m.institution_id
         WHERE {$whereStr}"
    );
    $totalStmt->execute($binds);
    $total = (int) $totalStmt->fetchColumn();

    $pag = paginate($total, $perPage, $page, APP_URL . '/admin/mou?q=' . urlencode($search) . '&status=' . urlencode($status) . '&cat=' . $catId . '&year=' . $year . '&page={page}');

    $stmt = $db->prepare(
        "SELECT m.*, i.name AS institution_name, c.name AS category_name,
                u.name AS unit_name
         FROM mous m
         JOIN institutions i ON i.id = m.institution_id
         JOIN categories   c ON c.id = m.category_id
         LEFT JOIN units   u ON u.id = m.unit_id
         WHERE {$whereStr}
         ORDER BY m.created_at DESC
         LIMIT :limit OFFSET :offset"
    );
    foreach ($binds as $k => $v) $stmt->bindValue($k, $v);
    $stmt->bindValue(':limit',  $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $pag['offset'], PDO::PARAM_INT);
    $stmt->execute();
    $mous = $stmt->fetchAll();

    $categories = $db->query("SELECT id, name FROM categories ORDER BY name")->fetchAll();
    $years      = $db->query("SELECT DISTINCT YEAR(start_date) AS y FROM mous ORDER BY y DESC")->fetchAll(PDO::FETCH_COLUMN);

    $pageTitle  = 'Dokumen MoU / MoA';
    $activeMenu = 'mou';
    require __DIR__ . '/../views/admin/mou/index.php';
}

/**
 * Endpoint terproteksi untuk menyajikan file PDF dokumen MoU
 */
function mou_download_document(array $params): void
{
    auth_check();
    $db  = get_db();
    $id  = sanitize($params['id'] ?? '');

    if (!_is_valid_uuid($id)) {
        http_response_code(404);
        die('Dokumen tidak ditemukan.');
    }

    $stmt = $db->prepare("SELECT file_path, mou_number, title FROM mous WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $mou = $stmt->fetch();

    if (!$mou || empty($mou['file_path'])) {
        http_response_code(404);
        die('Dokumen PDF tidak ditemukan.');
    }

    $fullPath = UPLOAD_DIR . $mou['file_path'];
    if (!file_exists($fullPath) || !is_file($fullPath)) {
        http_response_code(404);
        die('File fisik tidak ditemukan pada server.');
    }

    $filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $mou['mou_number']) . '.pdf';

    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="' . $filename . '"');
    header('Content-Length: ' . filesize($fullPath));
    header('Cache-Control: private, max-age=3600, must-revalidate');

    readfile($fullPath);
    exit;
}

/**
 * Endpoint terproteksi untuk menyajikan file PDF dokumen Addendum / Perpanjangan
 */
function mou_download_addendum(array $params): void
{
    auth_check();
    $db        = get_db();
    $id        = sanitize($params['id'] ?? '');
    $renewalId = (int) ($params['renewal_id'] ?? 0);

    if (!_is_valid_uuid($id)) {
        http_response_code(404);
        die('Dokumen tidak ditemukan.');
    }

    $stmt = $db->prepare("SELECT r.*, m.mou_number FROM mou_renewals r JOIN mous m ON m.id = r.mou_id WHERE r.id = :rid AND r.mou_id = :mid");
    $stmt->execute([':rid' => $renewalId, ':mid' => $id]);
    $renewal = $stmt->fetch();

    if (!$renewal || empty($renewal['document_path'])) {
        http_response_code(404);
        die('Dokumen addendum PDF tidak ditemukan.');
    }

    $fullPath = UPLOAD_DIR . $renewal['document_path'];
    if (!file_exists($fullPath) || !is_file($fullPath)) {
        http_response_code(404);
        die('File fisik tidak ditemukan pada server.');
    }

    $filename = 'Addendum_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $renewal['renewal_number']) . '.pdf';

    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="' . $filename . '"');
    header('Content-Length: ' . filesize($fullPath));
    header('Cache-Control: private, max-age=3600, must-revalidate');

    readfile($fullPath);
    exit;
}

// ═══════════════════════════════════════════════════════════════════════════
// CREATE
// ═══════════════════════════════════════════════════════════════════════════
function mou_create_form(array $params): void
{
    auth_check();
    $db      = get_db();
    $options = _mou_form_options($db);
    $errors  = $_SESSION['_form_errors'] ?? [];
    $old     = $_SESSION['_form_old']    ?? [];
    unset($_SESSION['_form_errors'], $_SESSION['_form_old']);

    $pageTitle  = 'Tambah MoU / MoA';
    $activeMenu = 'mou';
    require __DIR__ . '/../views/admin/mou/create.php';
}

function mou_create_post(array $params): void
{
    auth_check();
    if (!verify_csrf()) { http_response_code(403); die('CSRF mismatch'); }

    $db = get_db();

    $data = [
        'mou_number'       => sanitize($_POST['mou_number'] ?? ''),
        'mou_number_mitra' => sanitize($_POST['mou_number_mitra'] ?? ''),
        'title'            => sanitize($_POST['title'] ?? ''),
        'institution_id'   => (int) ($_POST['institution_id'] ?? 0),
        'category_id'      => (int) ($_POST['category_id'] ?? 0),
        'unit_id'          => ($_POST['unit_id'] ?? '') !== '' ? (int) $_POST['unit_id'] : null,
        'doc_type'         => in_array($_POST['doc_type'] ?? '', ['MoU','MoA']) ? $_POST['doc_type'] : 'MoU',
        'start_date'       => sanitize($_POST['start_date'] ?? ''),
        'end_date'         => sanitize($_POST['end_date'] ?? ''),
        'reminder_days'    => max(1, (int) ($_POST['reminder_days'] ?? 60)),
        'description'      => sanitize($_POST['description'] ?? ''),
    ];

    // Validation
    $errors = [];
    if (!$data['mou_number']) $errors[] = 'Nomor Surat RSUD Kilisuci wajib diisi.';
    if (!$data['title'])      $errors[] = 'Judul wajib diisi.';
    if (!$data['institution_id']) $errors[] = 'Institusi wajib dipilih.';
    if (!$data['category_id'])    $errors[] = 'Kategori wajib dipilih.';
    if (!$data['start_date'])     $errors[] = 'Tanggal mulai wajib diisi.';
    if (!$data['end_date'])       $errors[] = 'Tanggal berakhir wajib diisi.';
    if ($data['start_date'] && $data['end_date'] && $data['end_date'] <= $data['start_date']) {
        $errors[] = 'Tanggal berakhir harus setelah tanggal mulai.';
    }

    // Check duplicate mou_number
    $dup = $db->prepare("SELECT id FROM mous WHERE mou_number = :n LIMIT 1");
    $dup->execute([':n' => $data['mou_number']]);
    if ($dup->fetch()) $errors[] = 'Nomor Surat RSUD Kilisuci sudah terdaftar.';

    // File upload
    $filePath = null;
    if (!empty($_FILES['document']['name'])) {
        $upload = handle_document_upload($_FILES['document'], 'mou_' . preg_replace('/[^a-z0-9]/i','_', $data['mou_number']));
        if (!$upload['success']) {
            $errors[] = $upload['error'];
        } else {
            $filePath = $upload['path'];
        }
    }

    if ($errors) {
        $_SESSION['_form_errors'] = $errors;
        $_SESSION['_form_old']    = $data;
        header('Location: ' . APP_URL . '/admin/mou/create');
        exit;
    }

    // Generate UUID
    $uuid = generate_uuid();

    // Compute status with reminder_days
    $data['status'] = compute_mou_status($data['end_date'], 'active', $data['reminder_days']);

    $stmt = $db->prepare(
        "INSERT INTO mous (id,mou_number,mou_number_mitra,title,institution_id,category_id,unit_id,doc_type,start_date,end_date,status,reminder_days,description,file_path,created_by)
         VALUES (:id,:mou_number,:mou_number_mitra,:title,:institution_id,:category_id,:unit_id,:doc_type,:start_date,:end_date,:status,:reminder_days,:description,:file_path,:created_by)"
    );
    $stmt->execute([
        ':id'               => $uuid,
        ':mou_number'       => $data['mou_number'],
        ':mou_number_mitra' => $data['mou_number_mitra'],
        ':title'            => $data['title'],
        ':institution_id'   => $data['institution_id'],
        ':category_id'      => $data['category_id'],
        ':unit_id'          => $data['unit_id'],
        ':doc_type'         => $data['doc_type'],
        ':start_date'       => $data['start_date'],
        ':end_date'         => $data['end_date'],
        ':status'           => $data['status'],
        ':reminder_days'    => $data['reminder_days'],
        ':description'      => $data['description'],
        ':file_path'        => $filePath,
        ':created_by'       => current_user()['id'] ?? null,
    ]);

    log_activity('CREATE_MOU', "Menambahkan MoU baru: {$data['mou_number']} — {$data['title']}");

    flash('success', "MoU '{$data['title']}' berhasil ditambahkan.");
    header('Location: ' . APP_URL . '/admin/mou');
    exit;
}

// ═══════════════════════════════════════════════════════════════════════════
// EDIT
// ═══════════════════════════════════════════════════════════════════════════
function mou_edit_form(array $params): void
{
    auth_check();
    $db  = get_db();
    $id  = sanitize($params['id'] ?? '');

    if (!_is_valid_uuid($id)) {
        http_response_code(404);
        require __DIR__ . '/../views/errors/404.php';
        exit;
    }

    $stmt = $db->prepare(
        "SELECT m.*, i.name AS institution_name FROM mous m
         JOIN institutions i ON i.id = m.institution_id
         WHERE m.id = :id"
    );
    $stmt->execute([':id' => $id]);
    $mou = $stmt->fetch();

    if (!$mou) { http_response_code(404); require __DIR__ . '/../views/errors/404.php'; exit; }

    $options = _mou_form_options($db);
    $errors  = $_SESSION['_form_errors'] ?? [];
    $old     = $_SESSION['_form_old']    ?? $mou;
    unset($_SESSION['_form_errors'], $_SESSION['_form_old']);

    // Load renewal history
    $renewals = $db->prepare("SELECT * FROM mou_renewals WHERE mou_id = :id ORDER BY created_at DESC");
    $renewals->execute([':id' => $id]);
    $renewals = $renewals->fetchAll();

    $pageTitle  = 'Edit MoU / MoA';
    $activeMenu = 'mou';
    require __DIR__ . '/../views/admin/mou/edit.php';
}

function mou_edit_post(array $params): void
{
    auth_check();
    if (!verify_csrf()) { http_response_code(403); die('CSRF mismatch'); }

    $db = get_db();
    $id = sanitize($params['id'] ?? '');

    if (!_is_valid_uuid($id)) { http_response_code(404); exit; }

    $stmt = $db->prepare("SELECT * FROM mous WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $existing = $stmt->fetch();
    if (!$existing) { http_response_code(404); exit; }

    $data = [
        'mou_number'       => sanitize($_POST['mou_number'] ?? ''),
        'mou_number_mitra' => sanitize($_POST['mou_number_mitra'] ?? ''),
        'title'            => sanitize($_POST['title'] ?? ''),
        'institution_id'   => (int) ($_POST['institution_id'] ?? 0),
        'category_id'      => (int) ($_POST['category_id'] ?? 0),
        'unit_id'          => ($_POST['unit_id'] ?? '') !== '' ? (int) $_POST['unit_id'] : null,
        'doc_type'         => in_array($_POST['doc_type'] ?? '', ['MoU','MoA']) ? $_POST['doc_type'] : 'MoU',
        'start_date'       => sanitize($_POST['start_date'] ?? ''),
        'end_date'         => sanitize($_POST['end_date'] ?? ''),
        'reminder_days'    => max(1, (int) ($_POST['reminder_days'] ?? 60)),
        'description'      => sanitize($_POST['description'] ?? ''),
        'status'           => sanitize($_POST['status'] ?? 'active'),
    ];

    $errors = [];
    if (!$data['mou_number']) $errors[] = 'Nomor Surat RSUD Kilisuci wajib diisi.';
    if (!$data['title'])      $errors[] = 'Judul wajib diisi.';

    // Check duplicate (exclude self)
    $dup = $db->prepare("SELECT id FROM mous WHERE mou_number = :n AND id != :id LIMIT 1");
    $dup->execute([':n' => $data['mou_number'], ':id' => $id]);
    if ($dup->fetch()) $errors[] = 'Nomor Surat RSUD Kilisuci sudah terdaftar oleh dokumen lain.';

    // File upload (optional replacement)
    $filePath = $existing['file_path'];
    if (!empty($_FILES['document']['name'])) {
        $upload = handle_document_upload($_FILES['document'], 'mou_' . substr($id, 0, 8));
        if (!$upload['success']) {
            $errors[] = $upload['error'];
        } else {
            // Delete old file
            delete_upload($existing['file_path']);
            $filePath = $upload['path'];
        }
    }

    if ($errors) {
        $_SESSION['_form_errors'] = $errors;
        $_SESSION['_form_old']    = $data;
        header('Location: ' . APP_URL . '/admin/mou/' . $id . '/edit');
        exit;
    }

    // Recalculate status unless manually set to 'terminated'
    if ($data['status'] !== 'terminated') {
        $data['status'] = compute_mou_status($data['end_date'], $data['status'], $data['reminder_days']);
    }

    $stmt = $db->prepare(
        "UPDATE mous SET
            mou_number=:mou_number, mou_number_mitra=:mou_number_mitra, title=:title,
            institution_id=:institution_id, category_id=:category_id,
            unit_id=:unit_id, doc_type=:doc_type,
            start_date=:start_date, end_date=:end_date,
            status=:status, reminder_days=:reminder_days,
            description=:description, file_path=:file_path
         WHERE id=:id"
    );
    $stmt->execute([
        ':mou_number'       => $data['mou_number'],
        ':mou_number_mitra' => $data['mou_number_mitra'],
        ':title'            => $data['title'],
        ':institution_id'   => $data['institution_id'],
        ':category_id'      => $data['category_id'],
        ':unit_id'          => $data['unit_id'],
        ':doc_type'         => $data['doc_type'],
        ':start_date'       => $data['start_date'],
        ':end_date'         => $data['end_date'],
        ':status'           => $data['status'],
        ':reminder_days'    => $data['reminder_days'],
        ':description'      => $data['description'],
        ':file_path'        => $filePath,
        ':id'               => $id,
    ]);

    log_activity('UPDATE_MOU', "Memperbarui MoU: {$data['mou_number']} - {$data['title']}");
    flash('success', "MoU '{$data['title']}' berhasil diperbarui.");
    header('Location: ' . APP_URL . '/admin/mou');
    exit;
}

// ═══════════════════════════════════════════════════════════════════════════
// DELETE
// ═══════════════════════════════════════════════════════════════════════════
function mou_delete(array $params): void
{
    auth_check();
    if (!verify_csrf()) { http_response_code(403); die('CSRF mismatch'); }
    if (!has_role('superadmin')) {
        flash('error', 'Hanya superadmin yang dapat menghapus dokumen.');
        header('Location: ' . APP_URL . '/admin/mou');
        exit;
    }

    $db = get_db();
    $id = sanitize($params['id'] ?? '');

    if (!_is_valid_uuid($id)) {
        flash('error', 'Dokumen tidak ditemukan.');
        header('Location: ' . APP_URL . '/admin/mou');
        exit;
    }

    $stmt = $db->prepare("SELECT * FROM mous WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $mou = $stmt->fetch();

    if (!$mou) {
        flash('error', 'Dokumen tidak ditemukan.');
        header('Location: ' . APP_URL . '/admin/mou');
        exit;
    }

    // Delete file if exists
    delete_upload($mou['file_path']);

    // Delete renewal files
    $renewalStmt = $db->prepare("SELECT document_path FROM mou_renewals WHERE mou_id = :id");
    $renewalStmt->execute([':id' => $id]);
    foreach ($renewalStmt->fetchAll() as $r) {
        delete_upload($r['document_path']);
    }

    $db->prepare("DELETE FROM mous WHERE id = :id")->execute([':id' => $id]);

    log_activity('DELETE_MOU', "Menghapus MoU: {$mou['mou_number']} — {$mou['title']}");
    flash('success', "Dokumen MoU '{$mou['title']}' berhasil dihapus.");
    header('Location: ' . APP_URL . '/admin/mou');
    exit;
}

// ═══════════════════════════════════════════════════════════════════════════
// RENEWAL
// ═══════════════════════════════════════════════════════════════════════════
function mou_renew_form(array $params): void
{
    auth_check();
    $db = get_db();
    $id = sanitize($params['id'] ?? '');

    if (!_is_valid_uuid($id)) {
        http_response_code(404);
        require __DIR__ . '/../views/errors/404.php';
        exit;
    }

    $stmt = $db->prepare("SELECT m.*, i.name AS institution_name FROM mous m JOIN institutions i ON i.id=m.institution_id WHERE m.id=:id");
    $stmt->execute([':id' => $id]);
    $mou = $stmt->fetch();
    if (!$mou) { http_response_code(404); require __DIR__ . '/../views/errors/404.php'; exit; }

    $errors = $_SESSION['_form_errors'] ?? [];
    unset($_SESSION['_form_errors']);

    $pageTitle  = 'Perpanjang MoU';
    $activeMenu = 'mou';
    require __DIR__ . '/../views/admin/mou/renew.php';
}

function mou_renew_post(array $params): void
{
    auth_check();
    if (!verify_csrf()) { http_response_code(403); die('CSRF mismatch'); }

    $db = get_db();
    $id = sanitize($params['id'] ?? '');

    if (!_is_valid_uuid($id)) { http_response_code(404); exit; }

    $stmt = $db->prepare("SELECT * FROM mous WHERE id=:id");
    $stmt->execute([':id' => $id]);
    $mou = $stmt->fetch();
    if (!$mou) { http_response_code(404); exit; }

    $renewNumber = sanitize($_POST['renewal_number'] ?? '');
    $newStart    = sanitize($_POST['new_start_date'] ?? '');
    $newEnd      = sanitize($_POST['new_end_date'] ?? '');
    $notes       = sanitize($_POST['notes'] ?? '');

    $errors = [];
    if (!$renewNumber) $errors[] = 'Nomor addendum wajib diisi.';
    if (!$newStart)    $errors[] = 'Tanggal mulai baru wajib diisi.';
    if (!$newEnd)      $errors[] = 'Tanggal berakhir baru wajib diisi.';
    if ($newEnd <= $newStart) $errors[] = 'Tanggal berakhir harus setelah tanggal mulai.';

    $docPath = null;
    if (!empty($_FILES['document']['name'])) {
        $upload = handle_document_upload($_FILES['document'], 'renewal_' . substr($id, 0, 8));
        if (!$upload['success']) $errors[] = $upload['error'];
        else $docPath = $upload['path'];
    }

    if ($errors) {
        $_SESSION['_form_errors'] = $errors;
        header('Location: ' . APP_URL . '/admin/mou/' . $id . '/renew');
        exit;
    }

    // Insert renewal record
    $ins = $db->prepare(
        "INSERT INTO mou_renewals (mou_id,renewal_number,new_start_date,new_end_date,document_path,notes)
         VALUES (:mou_id,:renewal_number,:new_start_date,:new_end_date,:document_path,:notes)"
    );
    $ins->execute([
        ':mou_id'         => $id,
        ':renewal_number' => $renewNumber,
        ':new_start_date' => $newStart,
        ':new_end_date'   => $newEnd,
        ':document_path'  => $docPath,
        ':notes'          => $notes,
    ]);

    // Update parent MoU dates & status
    $newStatus = compute_mou_status($newEnd);
    $upd = $db->prepare(
        "UPDATE mous SET start_date=:s, end_date=:e, status=:status WHERE id=:id"
    );
    $upd->execute([':s' => $newStart, ':e' => $newEnd, ':status' => $newStatus, ':id' => $id]);

    log_activity('RENEW_MOU', "Perpanjang MoU #{$id}: {$mou['mou_number']} s/d {$newEnd}");
    flash('success', "MoU berhasil diperpanjang hingga " . format_date_id($newEnd) . ".");
    header('Location: ' . APP_URL . '/admin/mou/' . $id . '/edit');
    exit;
}
