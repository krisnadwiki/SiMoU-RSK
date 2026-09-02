<?php
/**
 * views/admin/dashboard.php — Admin Dashboard Analytics
 * Variables: $stats, $alertMous, $yearlyData, $categoryData, $statusData, $recentMous
 */

require __DIR__ . '/../layouts/header.php';

// Prepare chart data
$yearLabels   = json_encode(array_column($yearlyData, 'year'));
$yearValues   = json_encode(array_column($yearlyData, 'total'));
$catLabels    = json_encode(array_column($categoryData, 'name'));
$catValues    = json_encode(array_column($categoryData, 'total'));
$statusMap    = array_column($statusData, 'total', 'status');
?>

<div class="page-body">

    <!-- Stat Cards -->
    <div class="stats-grid">
        <div class="stat-card primary">
            <div class="stat-icon"><i class="fa-solid fa-file-contract"></i></div>
            <div class="stat-data">
                <div class="stat-value"><?= $stats['total'] ?></div>
                <div class="stat-label">Total Dokumen</div>
            </div>
        </div>
        <div class="stat-card success">
            <div class="stat-icon"><i class="fa-solid fa-circle-check"></i></div>
            <div class="stat-data">
                <div class="stat-value"><?= $stats['active'] ?></div>
                <div class="stat-label">MoU Aktif</div>
            </div>
        </div>
        <div class="stat-card warning" onclick="openModal('modalExpiringMous')" style="cursor:pointer;" title="Klik untuk membuka daftar MoU segera berakhir">
            <div class="stat-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
            <div class="stat-data">
                <div class="stat-value"><?= $stats['expiring'] ?></div>
                <div class="stat-label">Segera Berakhir (Klik Detail)</div>
            </div>
        </div>
        <div class="stat-card danger">
            <div class="stat-icon"><i class="fa-solid fa-ban"></i></div>
            <div class="stat-data">
                <div class="stat-value"><?= $stats['expired'] ?></div>
                <div class="stat-label">Dokumen Berakhir</div>
            </div>
        </div>
        <div class="stat-card primary">
            <div class="stat-icon"><i class="fa-solid fa-building"></i></div>
            <div class="stat-data">
                <div class="stat-value"><?= $stats['institutions'] ?></div>
                <div class="stat-label">Institusi Mitra</div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px; margin-bottom:24px;">

        <!-- Chart: MoU per Tahun -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <i class="fa-solid fa-chart-bar"></i>
                    MoU per Tahun
                </div>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="chartYearly"></canvas>
                </div>
            </div>
        </div>

        <!-- Chart: MoU per Kategori -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <i class="fa-solid fa-chart-pie"></i>
                    Distribusi per Kategori
                </div>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="chartCategory"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Table: Expiring MoUs -->
    <?php if (!empty($alertMous)): ?>
    <div class="card mb-4">
        <div class="card-header">
            <div class="card-title">
                <i class="fa-solid fa-bell" style="color: var(--warning);"></i>
                Dokumen Mendekati Masa Berakhir
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-warning btn-sm" onclick="openModal('modalExpiringMous')">
                    <i class="fa-solid fa-list-check"></i> Modal Detail
                </button>
                <a href="<?= APP_URL ?>/admin/mou?status=expiring_soon" class="btn btn-outline btn-sm">
                    Lihat Semua
                </a>
            </div>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Nomor MoU</th>
                        <th>Judul</th>
                        <th>Institusi</th>
                        <th>Berakhir</th>
                        <th>Sisa Hari</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($alertMous as $am):
                    $days = (int) $am['days_left'];
                    $rowClass = $days <= 7 ? 'expiry-row-7' : ($days <= 30 ? 'expiry-row-30' : 'expiry-row-90');
                ?>
                    <tr class="<?= $rowClass ?>">
                        <td><code style="font-size:.74rem;"><?= e($am['mou_number']) ?></code></td>
                        <td>
                            <a href="<?= APP_URL ?>/admin/mou/<?= $am['id'] ?>/edit" style="font-weight:600; color:var(--text-main);">
                                <?= e($am['title']) ?>
                            </a>
                        </td>
                        <td><?= e($am['institution_name']) ?></td>
                        <td><?= format_date_id($am['end_date']) ?></td>
                        <td>
                            <?php if ($days <= 7): ?>
                                <span class="badge badge-expired"><i class="fa-solid fa-fire"></i> <?= $days ?> hari</span>
                            <?php elseif ($days <= 30): ?>
                                <span class="badge badge-expiring"><?= $days ?> hari</span>
                            <?php else: ?>
                                <span class="badge badge-primary"><?= $days ?> hari</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?= APP_URL ?>/admin/mou/<?= $am['id'] ?>/renew"
                               class="btn btn-accent btn-sm">
                                <i class="fa-solid fa-rotate-right"></i> Perpanjang
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

<!-- ── Modal List MoU Segera Berakhir ────────────────────────────────────────── -->
<div class="modal-overlay" id="modalExpiringMous">
    <div class="modal" style="max-width:850px; width:95%;">
        <div class="modal-header">
            <div class="modal-title">
                <i class="fa-solid fa-triangle-exclamation" style="color:var(--warning);"></i>
                Daftar Dokumen MoU / MoA Segera Berakhir
            </div>
            <button class="modal-close" onclick="closeModal('modalExpiringMous')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body" style="max-height:65vh; overflow-y:auto; padding:0;">
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Nomor</th>
                            <th>Judul MoU</th>
                            <th>Institusi</th>
                            <th>Berakhir</th>
                            <th>Sisa Hari</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($alertMous)): ?>
                        <?php foreach ($alertMous as $am):
                            $days = (int) $am['days_left'];
                        ?>
                            <tr>
                                <td><code style="font-size:.74rem;"><?= e($am['mou_number']) ?></code></td>
                                <td>
                                    <a href="<?= APP_URL ?>/admin/mou/<?= $am['id'] ?>/edit" style="font-weight:600; color:var(--text-main);">
                                        <?= e($am['title']) ?>
                                    </a>
                                </td>
                                <td><?= e($am['institution_name']) ?></td>
                                <td style="white-space:nowrap; font-size:.78rem;"><?= format_date_id($am['end_date']) ?></td>
                                <td style="white-space:nowrap;">
                                    <?php if ($days <= 7): ?>
                                        <span class="badge badge-expired"><i class="fa-solid fa-fire"></i> <?= $days ?> hari lagi</span>
                                    <?php else: ?>
                                        <span class="badge badge-expiring"><i class="fa-solid fa-clock"></i> <?= $days ?> hari lagi</span>
                                    <?php endif; ?>
                                </td>
                                <td style="white-space:nowrap;">
                                    <div class="d-flex gap-1">
                                        <a href="<?= APP_URL ?>/admin/mou/<?= $am['id'] ?>/edit" class="btn btn-outline btn-sm" title="Edit">
                                            <i class="fa-solid fa-pen-to-square"></i> Edit
                                        </a>
                                        <a href="<?= APP_URL ?>/admin/mou/<?= $am['id'] ?>/renew" class="btn btn-accent btn-sm" title="Perpanjang">
                                            <i class="fa-solid fa-rotate-right"></i> Perpanjang
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align:center; padding:30px; color:var(--text-muted);">
                                Tidak ada dokumen MoU yang mendekati masa berakhir saat ini.
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeModal('modalExpiringMous')">Tutup</button>
            <a href="<?= APP_URL ?>/admin/mou?status=expiring_soon" class="btn btn-primary">Buka Semua di Halaman MoU</a>
        </div>
    </div>
</div>

    <!-- Recent MoUs -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="fa-solid fa-clock-rotate-left"></i>
                MoU Terbaru
            </div>
            <a href="<?= APP_URL ?>/admin/mou" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-plus"></i> Tambah MoU
            </a>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Nomor</th>
                        <th>Tipe</th>
                        <th>Judul</th>
                        <th>Institusi</th>
                        <th>Kategori</th>
                        <th>Berlaku</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($recentMous as $m): ?>
                    <tr>
                        <td><code style="font-size:.72rem;"><?= e($m['mou_number']) ?></code></td>
                        <td><span class="badge <?= $m['doc_type'] === 'MoA' ? 'badge-moa' : 'badge-mou' ?>"><?= e($m['doc_type']) ?></span></td>
                        <td>
                            <a href="<?= APP_URL ?>/admin/mou/<?= $m['id'] ?>/edit"
                               style="font-weight:600; color:var(--text-main);">
                                <?= e(mb_strimwidth($m['title'], 0, 55, '…')) ?>
                            </a>
                        </td>
                        <td><?= e($m['institution_name']) ?></td>
                        <td><?= e($m['category_name']) ?></td>
                        <td style="white-space:nowrap; font-size:.78rem;">
                            <?= format_date_id($m['start_date'], 'short') ?> –
                            <?= format_date_id($m['end_date'], 'short') ?>
                        </td>
                        <td><?= status_badge($m['status']) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($recentMous)): ?>
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <i class="fa-solid fa-folder-open"></i>
                                <h3>Belum ada dokumen MoU</h3>
                                <p>Mulai dengan menambahkan dokumen MoU pertama</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div><!-- /.page-body -->

<?php
$extraScript = <<<JS
document.addEventListener('DOMContentLoaded', () => {
    const palette = ['#0a7ea4','#0d9488','#16a34a','#d97706','#dc2626','#7c3aed','#db2777','#0891b2'];

    // Bar Chart — per Tahun
    const ctxY = document.getElementById('chartYearly')?.getContext('2d');
    if (ctxY) {
        new Chart(ctxY, {
            type: 'bar',
            data: {
                labels: {$yearLabels},
                datasets: [{
                    label: 'Jumlah MoU',
                    data: {$yearValues},
                    backgroundColor: 'rgba(10,126,164,.85)',
                    borderRadius: 8,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 },
                         grid: { color: 'rgba(0,0,0,.05)' } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    // Doughnut — per Kategori
    const ctxC = document.getElementById('chartCategory')?.getContext('2d');
    if (ctxC) {
        new Chart(ctxC, {
            type: 'doughnut',
            data: {
                labels: {$catLabels},
                datasets: [{
                    data: {$catValues},
                    backgroundColor: palette,
                    borderWidth: 2,
                    borderColor: '#fff',
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: { font: { size: 11 }, padding: 12, boxWidth: 12 }
                    }
                },
                cutout: '65%'
            }
        });
    }
});
JS;

require __DIR__ . '/../layouts/footer.php';
?>
