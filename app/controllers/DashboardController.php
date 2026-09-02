<?php
/**
 * DashboardController.php — Admin Dashboard Analytics
 */

function dashboard_index(array $params): void
{
    auth_check();

    $db = get_db();

    // ── Stat Counts ──────────────────────────────────────────
    $stats = [];

    $stats['total']    = (int) $db->query("SELECT COUNT(*) FROM mous")->fetchColumn();
    $stats['active']   = (int) $db->query("SELECT COUNT(*) FROM mous WHERE status='active'")->fetchColumn();
    $stats['expiring'] = (int) $db->query("SELECT COUNT(*) FROM mous WHERE status='expiring_soon'")->fetchColumn();
    $stats['expired']  = (int) $db->query("SELECT COUNT(*) FROM mous WHERE status='expired'")->fetchColumn();
    $stats['institutions'] = (int) $db->query("SELECT COUNT(*) FROM institutions")->fetchColumn();

    // ── Expiring Alert Table (dynamic reminder_days) ─────────
    $alertMous = $db->query(
        "SELECT m.id, m.title, m.mou_number, m.end_date, m.status, m.reminder_days,
                i.name AS institution_name,
                DATEDIFF(m.end_date, CURDATE()) AS days_left
         FROM mous m
         JOIN institutions i ON i.id = m.institution_id
         WHERE m.status = 'expiring_soon'
            OR (m.status = 'active' AND DATEDIFF(m.end_date, CURDATE()) <= m.reminder_days AND DATEDIFF(m.end_date, CURDATE()) >= 0)
         ORDER BY days_left ASC"
    )->fetchAll();

    // ── Chart: MoU per Tahun ─────────────────────────────────
    $yearlyData = $db->query(
        "SELECT YEAR(start_date) AS year, COUNT(*) AS total
         FROM mous
         GROUP BY YEAR(start_date)
         ORDER BY year"
    )->fetchAll();

    // ── Chart: MoU per Kategori ──────────────────────────────
    $categoryData = $db->query(
        "SELECT c.name, COUNT(m.id) AS total
         FROM categories c
         LEFT JOIN mous m ON m.category_id = c.id
         GROUP BY c.id, c.name
         ORDER BY total DESC"
    )->fetchAll();

    // ── Chart: Status Distribution ───────────────────────────
    $statusData = $db->query(
        "SELECT status, COUNT(*) AS total FROM mous GROUP BY status"
    )->fetchAll();

    // ── Recent MoUs ──────────────────────────────────────────
    $recentMous = $db->query(
        "SELECT m.id, m.title, m.mou_number, m.start_date, m.end_date, m.status, m.doc_type,
                i.name AS institution_name,
                c.name AS category_name
         FROM mous m
         JOIN institutions i ON i.id = m.institution_id
         JOIN categories c ON c.id = m.category_id
         ORDER BY m.created_at DESC
         LIMIT 8"
    )->fetchAll();

    $pageTitle  = 'Dashboard';
    $activeMenu = 'dashboard';

    require __DIR__ . '/../views/admin/dashboard.php';
}
