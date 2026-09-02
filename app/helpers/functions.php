<?php
/**
 * functions.php — Utility Helpers
 * Sanitasi, format, audit log, flash messages
 */

// ── XSS Sanitization ─────────────────────────────────────────────────────

function e(string $str): string
{
    return htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function sanitize(mixed $input): mixed
{
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return trim(strip_tags((string) $input));
}

// ── Date / Time Formatting ────────────────────────────────────────────────

/**
 * Format tanggal ke format Indonesia panjang.
 * Contoh: 1 Januari 2025
 */
function format_date_id(string|null $dateStr, string $format = 'long'): string
{
    if (empty($dateStr)) return '-';
    $ts = strtotime($dateStr);
    if ($ts === false) return $dateStr;

    $bulan = [
        1  => 'Januari',  2  => 'Februari', 3  => 'Maret',
        4  => 'April',    5  => 'Mei',       6  => 'Juni',
        7  => 'Juli',     8  => 'Agustus',   9  => 'September',
        10 => 'Oktober',  11 => 'November',  12 => 'Desember',
    ];

    $d = (int) date('j', $ts);
    $m = (int) date('n', $ts);
    $y = date('Y', $ts);

    return match($format) {
        'short'  => date('d/m/Y', $ts),
        'medium' => "{$d} {$bulan[$m]} {$y}",
        default  => "{$d} {$bulan[$m]} {$y}",
    };
}

/**
 * Hitung sisa hari hingga tanggal target.
 * Nilai negatif = sudah lewat.
 */
function days_until(string $dateStr): int
{
    $today  = strtotime(date('Y-m-d'));
    $target = strtotime($dateStr);
    return (int) round(($target - $today) / 86400);
}

/**
 * Tentukan status MoU berdasarkan tanggal akhir dan pengingat (reminder_days).
 */
function compute_mou_status(string $endDate, string $currentStatus = 'active', int $reminderDays = 60): string
{
    if ($currentStatus === 'terminated') return 'terminated';
    $days = days_until($endDate);
    if ($days < 0)              return 'expired';
    if ($days <= $reminderDays) return 'expiring_soon';
    return 'active';
}

/**
 * Badge HTML untuk status MoU.
 */
function status_badge(string $status): string
{
    $map = [
        'active'        => ['label' => 'Aktif',            'class' => 'badge-active'],
        'expiring_soon' => ['label' => 'Segera Berakhir',  'class' => 'badge-expiring'],
        'expired'       => ['label' => 'Berakhir',         'class' => 'badge-expired'],
        'terminated'    => ['label' => 'Dihentikan',       'class' => 'badge-terminated'],
    ];
    $s = $map[$status] ?? ['label' => $status, 'class' => 'badge-secondary'];
    return '<span class="badge ' . $s['class'] . '">' . e($s['label']) . '</span>';
}

// ── File size formatting ──────────────────────────────────────────────────

function format_bytes(int $bytes): string
{
    if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' MB';
    if ($bytes >= 1024)    return round($bytes / 1024, 1) . ' KB';
    return $bytes . ' B';
}

// ── Audit Log ─────────────────────────────────────────────────────────────

function log_activity(
    string $action,
    string $description,
    int|null $userId = null
): void {
    try {
        $db = get_db();
        $uid = $userId ?? ($_SESSION['user']['id'] ?? null);
        $ip  = $_SERVER['HTTP_X_FORWARDED_FOR']
            ?? $_SERVER['REMOTE_ADDR']
            ?? '0.0.0.0';
        $ip  = explode(',', $ip)[0];

        $stmt = $db->prepare(
            "INSERT INTO activity_logs (user_id, action, description, ip_address)
             VALUES (:uid, :action, :desc, :ip)"
        );
        $stmt->execute([
            ':uid'    => $uid,
            ':action' => substr($action, 0, 50),
            ':desc'   => $description,
            ':ip'     => substr($ip, 0, 45),
        ]);
    } catch (Throwable $e) {
        error_log('[SiMoU] log_activity error: ' . $e->getMessage());
    }
}

// ── Flash Messages ────────────────────────────────────────────────────────

function flash(string $key, string $message): void
{
    $_SESSION['_flash'][$key] = $message;
}

function get_flash(string $key): string
{
    $msg = $_SESSION['_flash'][$key] ?? '';
    unset($_SESSION['_flash'][$key]);
    return $msg;
}

function has_flash(string $key): bool
{
    return isset($_SESSION['_flash'][$key]);
}

// ── Pagination ────────────────────────────────────────────────────────────

function paginate(int $total, int $perPage, int $currentPage, string $urlPattern = '?page={page}'): array
{
    $totalPages  = max(1, (int) ceil($total / $perPage));
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset      = ($currentPage - 1) * $perPage;

    return [
        'total'        => $total,
        'per_page'     => $perPage,
        'current_page' => $currentPage,
        'total_pages'  => $totalPages,
        'offset'       => $offset,
        'url_pattern'  => $urlPattern,
    ];
}

// ── IP Helper ────────────────────────────────────────────────────────────

function get_client_ip(): string
{
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR']
        ?? $_SERVER['REMOTE_ADDR']
        ?? '0.0.0.0';
    return trim(explode(',', $ip)[0]);
}

// ── UUID v4 Generator ─────────────────────────────────────────────────────

function generate_uuid(): string
{
    $data    = random_bytes(16);
    $data[6] = chr((ord($data[6]) & 0x0f) | 0x40); // version 4
    $data[8] = chr((ord($data[8]) & 0x3f) | 0x80); // variant bits
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}
