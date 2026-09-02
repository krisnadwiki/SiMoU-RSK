<?php
/**
 * BackupController.php — Database & Document Backup & Restore Management
 * Mengelola ekspor dump SQL database, zip berkas PDF dokumen, dan impor restore (Superadmin)
 */

function backup_index(array $params): void
{
    auth_check();
    if (!has_role('superadmin')) {
        http_response_code(403);
        die('Akses ditolak. Fitur Backup & Restore hanya untuk Superadmin.');
    }

    $db = get_db();

    // Stats
    $mousCount = (int) $db->query("SELECT COUNT(*) FROM mous")->fetchColumn();
    $instCount = (int) $db->query("SELECT COUNT(*) FROM institutions")->fetchColumn();
    $catCount  = (int) $db->query("SELECT COUNT(*) FROM categories")->fetchColumn();
    $unitCount = (int) $db->query("SELECT COUNT(*) FROM units")->fetchColumn();
    $userCount = (int) $db->query("SELECT COUNT(*) FROM users")->fetchColumn();

    // Database size estimate
    $dbname = env('DB_DATABASE', 'simou_db');
    $sizeStmt = $db->prepare(
        "SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb
         FROM information_schema.tables
         WHERE table_schema = :dbname"
    );
    $sizeStmt->execute([':dbname' => $dbname]);
    $dbSizeMb = (float) ($sizeStmt->fetchColumn() ?: 0);

    // Document Upload Stats
    $docDir = UPLOAD_DIR . 'documents/';
    $docCount = 0;
    $docTotalBytes = 0;
    if (is_dir($docDir)) {
        $pdfFiles = glob($docDir . '*.pdf') ?: [];
        $docCount = count($pdfFiles);
        foreach ($pdfFiles as $f) {
            $docTotalBytes += filesize($f);
        }
    }
    $docSizeMb = round($docTotalBytes / 1024 / 1024, 2);

    // Last backup activity log
    $lastBackup = $db->query(
        "SELECT created_at FROM activity_logs
         WHERE action IN ('BACKUP_DATABASE', 'BACKUP_DOCUMENTS', 'BACKUP_FULL', 'RESTORE_DATABASE')
         ORDER BY id DESC LIMIT 1"
    )->fetchColumn();

    $pageTitle  = 'Backup & Restore System';
    $activeMenu = 'backup';
    require __DIR__ . '/../views/admin/backup.php';
}

function _generate_sql_dump(PDO $db): string
{
    $tables = ['users', 'institutions', 'categories', 'units', 'mous', 'mou_renewals', 'activity_logs'];

    $dump  = "-- ============================================================\n";
    $dump .= "-- SiMoU RSUD Kilisuci — Database Backup Dump\n";
    $dump .= "-- Tanggal Ekspor : " . date('Y-m-d H:i:s') . "\n";
    $dump .= "-- Host           : " . env('DB_HOST', 'simou-db') . "\n";
    $dump .= "-- Database       : " . env('DB_DATABASE', 'simou_db') . "\n";
    $dump .= "-- Versi Aplikasi : " . APP_VERSION . "\n";
    $dump .= "-- ============================================================\n\n";

    $dump .= "SET FOREIGN_KEY_CHECKS = 0;\n";
    $dump .= "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n";
    $dump .= "SET NAMES utf8mb4;\n\n";

    foreach ($tables as $table) {
        $dump .= "-- ────────────────────────────────────────────────────────────\n";
        $dump .= "-- Structure for table `{$table}`\n";
        $dump .= "-- ────────────────────────────────────────────────────────────\n";
        $dump .= "DROP TABLE IF EXISTS `{$table}`;\n";

        $createStmt = $db->query("SHOW CREATE TABLE `{$table}`")->fetch(PDO::FETCH_NUM);
        if (!empty($createStmt[1])) {
            $dump .= $createStmt[1] . ";\n\n";
        }

        // Fetch data
        $rows = $db->query("SELECT * FROM `{$table}`")->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($rows)) {
            $dump .= "-- Data for table `{$table}` (" . count($rows) . " rows)\n";
            $columns = array_keys($rows[0]);
            $colNames = implode('`, `', array_map(fn($c) => str_replace('`', '``', $c), $columns));

            foreach ($rows as $row) {
                $vals = [];
                foreach ($row as $val) {
                    if ($val === null) {
                        $vals[] = 'NULL';
                    } else {
                        $vals[] = $db->quote($val);
                    }
                }
                $valStr = implode(', ', $vals);
                $dump .= "INSERT INTO `{$table}` (`{$colNames}`) VALUES ({$valStr});\n";
            }
            $dump .= "\n";
        }
    }

    $dump .= "SET FOREIGN_KEY_CHECKS = 1;\n";
    $dump .= "-- End of dump\n";

    return $dump;
}

function backup_export(array $params): void
{
    auth_check();
    if (!has_role('superadmin')) { http_response_code(403); die('Akses ditolak.'); }
    if (!verify_csrf()) { http_response_code(403); die('CSRF mismatch.'); }

    $db   = get_db();
    $dump = _generate_sql_dump($db);

    log_activity('BACKUP_DATABASE', 'Mengunduh salinan cadangan (backup) database SQL');

    $filename = 'simou_backup_' . date('Y-m-d_His') . '.sql';

    header('Content-Type: application/sql');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($dump));
    header('Cache-Control: private, no-transform, no-store, must-revalidate');

    echo $dump;
    exit;
}

function backup_export_documents(array $params): void
{
    auth_check();
    if (!has_role('superadmin')) { http_response_code(403); die('Akses ditolak.'); }
    if (!verify_csrf()) { http_response_code(403); die('CSRF mismatch.'); }

    $docDir   = UPLOAD_DIR . 'documents/';
    $tempFile = tempnam(sys_get_temp_dir(), 'simou_docs_') . '.zip';

    $zip = new ZipArchive();
    if ($zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        flash('error', 'Gagal membuat berkas arsip ZIP dokumen.');
        header('Location: ' . APP_URL . '/admin/backup');
        exit;
    }

    $pdfFiles = is_dir($docDir) ? (glob($docDir . '*.pdf') ?: []) : [];
    if (!empty($pdfFiles)) {
        foreach ($pdfFiles as $filePath) {
            $zip->addFile($filePath, 'documents/' . basename($filePath));
        }
    } else {
        $zip->addFromString('README.txt', "Belum ada berkas dokumen PDF yang diunggah.\nTanggal Backup: " . date('Y-m-d H:i:s'));
    }

    $zip->close();

    log_activity('BACKUP_DOCUMENTS', 'Mengunduh salinan arsip ZIP berkas dokumen PDF');

    $filename = 'simou_documents_backup_' . date('Y-m-d_His') . '.zip';

    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . filesize($tempFile));
    header('Cache-Control: private, no-transform, no-store, must-revalidate');

    readfile($tempFile);
    @unlink($tempFile);
    exit;
}

function backup_export_all(array $params): void
{
    auth_check();
    if (!has_role('superadmin')) { http_response_code(403); die('Akses ditolak.'); }
    if (!verify_csrf()) { http_response_code(403); die('CSRF mismatch.'); }

    $db       = get_db();
    $dump     = _generate_sql_dump($db);
    $docDir   = UPLOAD_DIR . 'documents/';
    $tempFile = tempnam(sys_get_temp_dir(), 'simou_full_') . '.zip';

    $zip = new ZipArchive();
    if ($zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        flash('error', 'Gagal membuat berkas arsip ZIP backup lengkap.');
        header('Location: ' . APP_URL . '/admin/backup');
        exit;
    }

    // Add SQL database dump file
    $sqlFileName = 'database_simou_' . date('Y-m-d_His') . '.sql';
    $zip->addFromString($sqlFileName, $dump);

    // Add document PDF files
    $pdfFiles = is_dir($docDir) ? (glob($docDir . '*.pdf') ?: []) : [];
    if (!empty($pdfFiles)) {
        foreach ($pdfFiles as $filePath) {
            $zip->addFile($filePath, 'documents/' . basename($filePath));
        }
    }

    $zip->close();

    log_activity('BACKUP_FULL', 'Mengunduh salinan backup lengkap (Database SQL + Berkas Dokumen PDF)');

    $filename = 'simou_full_backup_' . date('Y-m-d_His') . '.zip';

    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . filesize($tempFile));
    header('Cache-Control: private, no-transform, no-store, must-revalidate');

    readfile($tempFile);
    @unlink($tempFile);
    exit;
}

function backup_import(array $params): void
{
    auth_check();
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
              || (!empty($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'))
              || (!empty($_POST['is_ajax']) && $_POST['is_ajax'] === '1');

    if (!has_role('superadmin')) {
        if ($isAjax) {
            header('Content-Type: application/json');
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Akses ditolak. Hanya superadmin yang dapat melakukan restore database.']);
            exit;
        }
        http_response_code(403); die('Akses ditolak.');
    }
    if (!verify_csrf()) {
        if ($isAjax) {
            header('Content-Type: application/json');
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Token keamanan CSRF tidak valid. Silakan muat ulang halaman.']);
            exit;
        }
        http_response_code(403); die('CSRF mismatch.');
    }

    $db = get_db();

    if (empty($_FILES['backup_file']['name'])) {
        _respond_restore(false, 'Silakan pilih berkas backup (.sql) terlebih dahulu.', $isAjax);
    }

    $file = $_FILES['backup_file'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        _respond_restore(false, 'Gagal mengunggah berkas backup.', $isAjax);
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($ext !== 'sql') {
        _respond_restore(false, 'Format berkas tidak valid. Berkas backup harus berekstensi .sql', $isAjax);
    }

    $sqlContent = file_get_contents($file['tmp_name']);
    if (empty(trim($sqlContent))) {
        _respond_restore(false, 'Berkas backup kosong / tidak memiliki perintah SQL.', $isAjax);
    }

    try {
        $db->exec($sqlContent);
        log_activity('RESTORE_DATABASE', "Memulihkan database dari berkas: {$file['name']}");
        _respond_restore(true, "Database berhasil dipulihkan dari berkas '{$file['name']}'. Seluruh data dan skema tabel telah disinkronkan.", $isAjax);
    } catch (Throwable $e) {
        error_log('[Backup Restore Error] ' . $e->getMessage());
        _respond_restore(false, 'Gagal memulihkan database: ' . $e->getMessage(), $isAjax);
    }
}

function _respond_restore(bool $success, string $message, bool $isAjax): void
{
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode([
            'success'   => $success,
            'message'   => $message,
            'timestamp' => date('d M Y H:i:s')
        ]);
        exit;
    }

    flash($success ? 'success' : 'error', $message);
    header('Location: ' . APP_URL . '/admin/backup');
    exit;
}
