<?php
/**
 * LogController.php — Audit Trail Viewer
 */

function logs_index(array $params): void
{
    auth_check();

    $db      = get_db();
    $action  = sanitize($_GET['action'] ?? '');
    $page    = max(1, (int) ($_GET['page'] ?? 1));
    $perPage = 30;

    $where = ['1=1'];
    $binds = [];
    if ($action) { $where[] = "l.action=:action"; $binds[':action'] = $action; }

    $whereStr = implode(' AND ', $where);

    $totalStmt = $db->prepare("SELECT COUNT(*) FROM activity_logs l WHERE {$whereStr}");
    $totalStmt->execute($binds);
    $total = (int) $totalStmt->fetchColumn();

    $pag = paginate($total, $perPage, $page,
        APP_URL . '/admin/logs?action=' . urlencode($action) . '&page={page}'
    );

    $stmt = $db->prepare(
        "SELECT l.*, u.name AS user_name, u.username
         FROM activity_logs l
         LEFT JOIN users u ON u.id=l.user_id
         WHERE {$whereStr}
         ORDER BY l.created_at DESC
         LIMIT :lim OFFSET :off"
    );
    foreach ($binds as $k => $v) $stmt->bindValue($k, $v);
    $stmt->bindValue(':lim', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':off', $pag['offset'], PDO::PARAM_INT);
    $stmt->execute();
    $logs = $stmt->fetchAll();

    $actions = $db->query("SELECT DISTINCT action FROM activity_logs ORDER BY action")->fetchAll(PDO::FETCH_COLUMN);

    $pageTitle  = 'Audit Log';
    $activeMenu = 'logs';
    require __DIR__ . '/../views/admin/logs.php';
}
