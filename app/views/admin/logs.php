<?php
/**
 * views/admin/logs.php — Audit Activity Log Viewer
 */

require __DIR__ . '/../layouts/header.php';

$actionIcons = [
    'LOGIN'               => ['fa-right-to-bracket', 'var(--success)'],
    'LOGOUT'              => ['fa-right-from-bracket','var(--text-muted)'],
    'LOGIN_FAILED'        => ['fa-ban',              'var(--danger)'],
    'LOGIN_BLOCKED'       => ['fa-lock',             'var(--danger)'],
    'CREATE_MOU'          => ['fa-plus-circle',      'var(--primary)'],
    'UPDATE_MOU'          => ['fa-pen-to-square',    'var(--warning)'],
    'DELETE_MOU'          => ['fa-trash',            'var(--danger)'],
    'RENEW_MOU'           => ['fa-rotate-right',     'var(--accent)'],
    'CREATE_INSTITUTION'  => ['fa-building',         'var(--primary)'],
    'UPDATE_INSTITUTION'  => ['fa-pen-to-square',    'var(--warning)'],
    'DELETE_INSTITUTION'  => ['fa-trash',            'var(--danger)'],
    'CREATE_UNIT'         => ['fa-hospital',         'var(--primary)'],
    'UPDATE_UNIT'         => ['fa-pen-to-square',    'var(--warning)'],
    'DELETE_UNIT'         => ['fa-trash',            'var(--danger)'],
    'EXPORT_CSV'          => ['fa-file-export',      'var(--info)'],
];
?>

<div class="page-body">

    <div class="d-flex align-center justify-between mb-3">
        <div>
            <h3 style="font-size:1rem; font-weight:700;">
                <i class="fa-solid fa-clipboard-list" style="color:var(--primary);"></i>
                Audit Activity Log
            </h3>
            <p class="text-muted fs-sm">
                Total <?= number_format($total) ?> entri log
            </p>
        </div>
        <a href="<?= APP_URL ?>/admin/export?type=logs" class="btn btn-outline btn-sm">
            <i class="fa-solid fa-download"></i> Export
        </a>
    </div>

    <!-- Filter -->
    <form method="GET" action="<?= APP_URL ?>/admin/logs" class="filter-bar mb-3">
        <select name="action" class="form-control" style="width:auto; min-width:200px;">
            <option value="">Semua Aksi</option>
            <?php foreach ($actions as $act): ?>
            <option value="<?= e($act) ?>" <?= ($action ?? '') === $act ? 'selected' : '' ?>>
                <?= e($act) ?>
            </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-filter"></i> Filter
        </button>
        <a href="<?= APP_URL ?>/admin/logs" class="btn btn-outline btn-sm">Reset</a>
    </form>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>Aksi</th>
                    <th>Pengguna</th>
                    <th>Deskripsi</th>
                    <th>IP Address</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($logs as $log):
                [$icon, $color] = $actionIcons[$log['action']] ?? ['fa-circle-dot', 'var(--text-muted)'];
            ?>
                <tr>
                    <td style="white-space:nowrap; font-size:.76rem; color:var(--text-muted);">
                        <?= date('d/m/Y', strtotime($log['created_at'])) ?><br>
                        <strong><?= date('H:i:s', strtotime($log['created_at'])) ?></strong>
                    </td>
                    <td>
                        <span style="display:inline-flex; align-items:center; gap:6px;
                                     font-size:.72rem; font-weight:700; color:<?= $color ?>;">
                            <i class="fa-solid <?= $icon ?>"></i>
                            <?= e($log['action']) ?>
                        </span>
                    </td>
                    <td style="font-size:.8rem;">
                        <?php if ($log['user_name']): ?>
                        <div style="font-weight:600;"><?= e($log['user_name']) ?></div>
                        <div style="color:var(--text-muted); font-size:.72rem;">@<?= e($log['username'] ?? '') ?></div>
                        <?php else: ?>
                        <span style="color:var(--text-subtle);">—</span>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:.8rem; max-width:360px;"><?= e($log['description']) ?></td>
                    <td><code style="font-size:.75rem; color:var(--text-muted);"><?= e($log['ip_address']) ?></code></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($logs)): ?>
                <tr><td colspan="5">
                    <div class="empty-state">
                        <i class="fa-solid fa-clipboard-list"></i>
                        <h3>Tidak ada log ditemukan</h3>
                    </div>
                </td></tr>
            <?php endif; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <?php if ($pag['total_pages'] > 1): ?>
        <div class="pagination">
            <span>
                Halaman <?= $pag['current_page'] ?> dari <?= $pag['total_pages'] ?>
                (<?= number_format($total) ?> entri)
            </span>
            <div class="pagination-links">
                <?php for ($i = max(1, $pag['current_page']-2); $i <= min($pag['total_pages'], $pag['current_page']+2); $i++): ?>
                <?php $url = str_replace('{page}', $i, $pag['url_pattern']); ?>
                <?php if ($i === $pag['current_page']): ?>
                    <span class="active"><?= $i ?></span>
                <?php else: ?>
                    <a href="<?= $url ?>"><?= $i ?></a>
                <?php endif; ?>
                <?php endfor; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
