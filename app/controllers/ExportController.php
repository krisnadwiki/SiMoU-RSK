<?php
/**
 * ExportController.php — Export MoU data to CSV
 */

function export_csv(array $params): void
{
    auth_check();

    $db     = get_db();
    $status = sanitize($_GET['status'] ?? '');
    $catId  = (int) ($_GET['cat'] ?? 0);
    $year   = (int) ($_GET['year'] ?? 0);

    $where = ['1=1'];
    $binds = [];
    if ($status) { $where[] = "m.status=:status"; $binds[':status'] = $status; }
    if ($catId)  { $where[] = "m.category_id=:cat"; $binds[':cat'] = $catId; }
    if ($year)   { $where[] = "YEAR(m.start_date)=:year"; $binds[':year'] = $year; }

    $whereStr = implode(' AND ', $where);

    $stmt = $db->prepare(
        "SELECT m.mou_number, m.doc_type, m.title,
                i.name AS institution, i.category AS institution_category,
                c.name AS category, u.name AS unit,
                m.start_date, m.end_date,
                DATEDIFF(m.end_date, CURDATE()) AS days_remaining,
                m.status, m.description, m.created_at
         FROM mous m
         JOIN institutions i ON i.id=m.institution_id
         JOIN categories   c ON c.id=m.category_id
         LEFT JOIN units   u ON u.id=m.unit_id
         WHERE {$whereStr}
         ORDER BY m.end_date ASC"
    );
    $stmt->execute($binds);
    $rows = $stmt->fetchAll();

    log_activity('EXPORT_CSV', 'Export data MoU ke CSV. Filter: status=' . $status . ', cat=' . $catId . ', year=' . $year);

    $filename = 'SiMoU_Export_' . date('Ymd_His') . '.csv';

    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, no-store');
    header('Pragma: no-cache');

    // BOM for Excel UTF-8 compatibility
    echo "\xEF\xBB\xBF";

    $out = fopen('php://output', 'w');

    // Header row
    fputcsv($out, [
        'Nomor MoU', 'Tipe Dokumen', 'Judul Kerjasama',
        'Institusi', 'Kategori Institusi',
        'Kategori MoU', 'Unit Kerja',
        'Tanggal Mulai', 'Tanggal Berakhir', 'Sisa Hari',
        'Status', 'Keterangan', 'Tanggal Dibuat',
    ], ',');

    $statusLabel = [
        'active'        => 'Aktif',
        'expiring_soon' => 'Segera Berakhir',
        'expired'       => 'Berakhir',
        'terminated'    => 'Dihentikan',
    ];

    foreach ($rows as $row) {
        fputcsv($out, [
            $row['mou_number'],
            $row['doc_type'],
            $row['title'],
            $row['institution'],
            $row['institution_category'],
            $row['category'],
            $row['unit'] ?? '-',
            date('d/m/Y', strtotime($row['start_date'])),
            date('d/m/Y', strtotime($row['end_date'])),
            $row['days_remaining'],
            $statusLabel[$row['status']] ?? $row['status'],
            $row['description'],
            date('d/m/Y H:i', strtotime($row['created_at'])),
        ], ',');
    }

    fclose($out);
    exit;
}
