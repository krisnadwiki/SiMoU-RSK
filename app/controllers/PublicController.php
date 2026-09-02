<?php
/**
 * PublicController.php — Public Portal (No Auth Required)
 */

function public_home(array $params): void
{
    $db = get_db();

    // Update statuses first
    $db->exec(
        "UPDATE mous SET status = CASE
            WHEN status='terminated' THEN 'terminated'
            WHEN end_date < CURDATE() THEN 'expired'
            WHEN DATEDIFF(end_date,CURDATE()) <= 90 THEN 'expiring_soon'
            ELSE 'active'
         END WHERE status != 'terminated'"
    );

    $stats = [
        'total'        => (int) $db->query("SELECT COUNT(*) FROM mous")->fetchColumn(),
        'active'       => (int) $db->query("SELECT COUNT(*) FROM mous WHERE status='active'")->fetchColumn(),
        'expiring'     => (int) $db->query("SELECT COUNT(*) FROM mous WHERE status='expiring_soon'")->fetchColumn(),
        'institutions' => (int) $db->query("SELECT COUNT(*) FROM institutions")->fetchColumn(),
    ];

    // Chart: yearly
    $yearlyData = $db->query(
        "SELECT YEAR(start_date) AS year, COUNT(*) AS total
         FROM mous GROUP BY YEAR(start_date) ORDER BY year"
    )->fetchAll();

    // Chart: by category
    $categoryData = $db->query(
        "SELECT c.name, COUNT(m.id) AS total
         FROM categories c LEFT JOIN mous m ON m.category_id=c.id
         GROUP BY c.id ORDER BY total DESC"
    )->fetchAll();

    // Recent 6
    $recentMous = $db->query(
        "SELECT m.id,m.title,m.mou_number,m.end_date,m.status,m.doc_type,
                i.name AS institution_name, c.name AS category_name
         FROM mous m
         JOIN institutions i ON i.id=m.institution_id
         JOIN categories c ON c.id=m.category_id
         ORDER BY m.created_at DESC LIMIT 6"
    )->fetchAll();

    $pageTitle  = 'Beranda';
    $activePage = 'home';

    ob_start();
    require __DIR__ . '/../views/public/home.php';
    $content = ob_get_clean();

    require __DIR__ . '/../views/layouts/public_layout.php';
}

function public_catalog(array $params): void
{
    $db = get_db();

    $search  = sanitize($_GET['q'] ?? '');
    $status  = sanitize($_GET['status'] ?? '');
    $catId   = (int) ($_GET['cat'] ?? 0);
    $year    = (int) ($_GET['year'] ?? 0);
    $page    = max(1, (int) ($_GET['page'] ?? 1));
    $perPage = 12;

    $where = ['1=1'];
    $binds = [];

    if ($search) {
        $where[] = "(m.title LIKE :q OR m.mou_number LIKE :q OR i.name LIKE :q)";
        $binds[':q'] = "%{$search}%";
    }
    if ($status) { $where[] = "m.status=:status"; $binds[':status'] = $status; }
    if ($catId)  { $where[] = "m.category_id=:cat"; $binds[':cat'] = $catId; }
    if ($year)   { $where[] = "YEAR(m.start_date)=:year"; $binds[':year'] = $year; }

    $whereStr = implode(' AND ', $where);

    $totalStmt = $db->prepare(
        "SELECT COUNT(*) FROM mous m JOIN institutions i ON i.id=m.institution_id WHERE {$whereStr}"
    );
    $totalStmt->execute($binds);
    $total = (int) $totalStmt->fetchColumn();

    $pag = paginate($total, $perPage, $page,
        APP_URL . '/catalog?q=' . urlencode($search) . '&status=' . urlencode($status) . '&cat=' . $catId . '&year=' . $year . '&page={page}'
    );

    $stmt = $db->prepare(
        "SELECT m.id,m.title,m.mou_number,m.start_date,m.end_date,m.status,m.doc_type,
                i.name AS institution_name, c.name AS category_name
         FROM mous m
         JOIN institutions i ON i.id=m.institution_id
         JOIN categories   c ON c.id=m.category_id
         WHERE {$whereStr}
         ORDER BY m.created_at DESC
         LIMIT :limit OFFSET :offset"
    );
    foreach ($binds as $k => $v) $stmt->bindValue($k, $v);
    $stmt->bindValue(':limit',  $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $pag['offset'], PDO::PARAM_INT);
    $stmt->execute();
    $mous = $stmt->fetchAll();

    $categories = $db->query("SELECT id,name FROM categories ORDER BY name")->fetchAll();
    $years      = $db->query("SELECT DISTINCT YEAR(start_date) AS y FROM mous ORDER BY y DESC")->fetchAll(PDO::FETCH_COLUMN);

    $pageTitle  = 'Katalog MoU';
    $activePage = 'catalog';

    ob_start();
    require __DIR__ . '/../views/public/catalog.php';
    $content = ob_get_clean();

    require __DIR__ . '/../views/layouts/public_layout.php';
}

function public_detail(array $params): void
{
    $db = get_db();
    $id = (int) ($params['id'] ?? 0);

    $stmt = $db->prepare(
        "SELECT m.*,
                i.name AS institution_name, i.category AS institution_category,
                i.contact_person, i.phone AS institution_phone, i.email AS institution_email,
                c.name AS category_name,
                u.name AS unit_name
         FROM mous m
         JOIN institutions i ON i.id=m.institution_id
         JOIN categories   c ON c.id=m.category_id
         LEFT JOIN units   u ON u.id=m.unit_id
         WHERE m.id=:id"
    );
    $stmt->execute([':id' => $id]);
    $mou = $stmt->fetch();

    if (!$mou) { http_response_code(404); require __DIR__ . '/../views/errors/404.php'; exit; }

    // Renewals
    $renewals = $db->prepare("SELECT * FROM mou_renewals WHERE mou_id=:id ORDER BY created_at DESC");
    $renewals->execute([':id' => $id]);
    $renewals = $renewals->fetchAll();

    $pageTitle  = e($mou['title']);
    $metaDesc   = "Detail MoU: {$mou['title']} — {$mou['institution_name']}";
    $activePage = 'catalog';

    ob_start();
    require __DIR__ . '/../views/public/detail.php';
    $content = ob_get_clean();

    require __DIR__ . '/../views/layouts/public_layout.php';
}
