<?php
/**
 * views/public/home.php — Public Landing Page
 * Variables: $stats, $yearlyData, $categoryData, $recentMous
 */

$yearLabels = json_encode(array_column($yearlyData, 'year'));
$yearValues = json_encode(array_column($yearlyData, 'total'));
$catLabels  = json_encode(array_column($categoryData, 'name'));
$catValues  = json_encode(array_column($categoryData, 'total'));
?>

<!-- ── Hero ─────────────────────────────────────────────────── -->
<section class="hero">
    <div class="hero-content">
        <div class="hero-eyebrow">
            <i class="fa-solid fa-handshake"></i>
            RSUD Kilisuci — Kota Kediri
        </div>
        <h1>Repositori Dokumen<br>MoU &amp; MoA</h1>
        <p>
            Platform terpadu pengelolaan Memorandum of Understanding dan Memorandum of Agreement
            RSUD Kilisuci. Temukan, pantau, dan akses dokumen kerjasama secara transparan.
        </p>
        <div style="display:flex; gap:12px; justify-content:center; flex-wrap:wrap; margin-bottom:40px;">
            <a href="<?= APP_URL ?>/catalog" class="btn btn-primary btn-lg">
                <i class="fa-solid fa-folder-open"></i> Jelajahi Katalog MoU
            </a>
            <a href="<?= APP_URL ?>/auth/login" class="btn btn-outline btn-lg"
               style="border-color:rgba(255,255,255,.4); color:#fff; background:rgba(255,255,255,.1);">
                <i class="fa-solid fa-lock"></i> Login Admin
            </a>
        </div>

        <div class="hero-stats">
            <div class="hero-stat-item">
                <div class="hero-stat-value"><?= $stats['total'] ?></div>
                <div class="hero-stat-label">Total Dokumen</div>
            </div>
            <div class="hero-divider"></div>
            <div class="hero-stat-item">
                <div class="hero-stat-value"><?= $stats['active'] ?></div>
                <div class="hero-stat-label">MoU Aktif</div>
            </div>
            <div class="hero-divider"></div>
            <div class="hero-stat-item">
                <div class="hero-stat-value"><?= $stats['expiring'] ?></div>
                <div class="hero-stat-label">Segera Berakhir</div>
            </div>
            <div class="hero-divider"></div>
            <div class="hero-stat-item">
                <div class="hero-stat-value"><?= $stats['institutions'] ?></div>
                <div class="hero-stat-label">Mitra Institusi</div>
            </div>
        </div>
    </div>
</section>

<!-- ── Charts Section ────────────────────────────────────────── -->
<section class="section" style="background:var(--bg-surface); border-bottom:1px solid var(--border-light);">
    <div class="public-container">
        <div class="section-header">
            <div>
                <div class="section-title">Statistik Kerjasama</div>
                <div class="section-desc">Ringkasan data dokumen MoU &amp; MoA RSUD Kilisuci</div>
            </div>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:24px;">
            <div class="card">
                <div class="card-header">
                    <div class="card-title"><i class="fa-solid fa-chart-bar"></i> MoU per Tahun</div>
                </div>
                <div class="card-body">
                    <div class="chart-container"><canvas id="chartYearly"></canvas></div>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <div class="card-title"><i class="fa-solid fa-chart-pie"></i> Distribusi per Kategori</div>
                </div>
                <div class="card-body">
                    <div class="chart-container"><canvas id="chartCategory"></canvas></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ── Recent MoUs ────────────────────────────────────────────── -->
<section class="section">
    <div class="public-container">
        <div class="section-header">
            <div>
                <div class="section-title">MoU Terbaru</div>
                <div class="section-desc">Dokumen kerjasama yang baru ditambahkan</div>
            </div>
            <a href="<?= APP_URL ?>/catalog" class="btn btn-primary">
                Lihat Semua <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>

        <div class="mou-grid">
            <?php foreach ($recentMous as $m): ?>
            <a href="<?= APP_URL ?>/mou/<?= $m['id'] ?>" class="mou-card <?= e($m['status']) ?>"
               style="text-decoration:none;">
                <div class="mou-card-header">
                    <div>
                        <div class="mou-card-number"><?= e($m['mou_number']) ?></div>
                        <div class="mou-card-title"><?= e(mb_strimwidth($m['title'], 0, 70, '…')) ?></div>
                    </div>
                    <span class="badge <?= $m['doc_type'] === 'MoA' ? 'badge-moa' : 'badge-mou' ?>"><?= e($m['doc_type']) ?></span>
                </div>
                <div class="mou-card-institution">
                    <i class="fa-solid fa-building"></i>
                    <?= e($m['institution_name']) ?>
                </div>
                <div style="font-size:.72rem; color:var(--text-subtle);">
                    <i class="fa-solid fa-tag"></i> <?= e($m['category_name']) ?>
                </div>
                <div class="mou-card-footer">
                    <div class="mou-card-date">
                        <i class="fa-solid fa-calendar"></i>
                        s/d <?= format_date_id($m['end_date'], 'medium') ?>
                    </div>
                    <?= status_badge($m['status']) ?>
                </div>
            </a>
            <?php endforeach; ?>

            <?php if (empty($recentMous)): ?>
            <div class="empty-state" style="grid-column:1/-1;">
                <i class="fa-solid fa-folder-open"></i>
                <h3>Belum ada dokumen MoU</h3>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php
$extraScript = <<<JS
document.addEventListener('DOMContentLoaded', () => {
    const palette = ['#0a7ea4','#0d9488','#16a34a','#d97706','#dc2626','#7c3aed','#db2777','#0891b2'];

    const ctxY = document.getElementById('chartYearly')?.getContext('2d');
    if (ctxY) {
        new Chart(ctxY, {
            type: 'bar',
            data: {
                labels: {$yearLabels},
                datasets: [{ label:'Jumlah MoU', data:{$yearValues},
                    backgroundColor:'rgba(10,126,164,.85)', borderRadius:8, borderSkipped:false }]
            },
            options: {
                responsive:true, maintainAspectRatio:false,
                plugins:{ legend:{display:false} },
                scales:{
                    y:{ beginAtZero:true, ticks:{precision:0}, grid:{color:'rgba(0,0,0,.05)'} },
                    x:{ grid:{display:false} }
                }
            }
        });
    }

    const ctxC = document.getElementById('chartCategory')?.getContext('2d');
    if (ctxC) {
        new Chart(ctxC, {
            type: 'doughnut',
            data: {
                labels:{$catLabels},
                datasets:[{ data:{$catValues}, backgroundColor:palette, borderWidth:2, borderColor:'#fff', hoverOffset:8 }]
            },
            options:{
                responsive:true, maintainAspectRatio:false,
                plugins:{
                    legend:{ position:'right', labels:{font:{size:11}, padding:12, boxWidth:12} }
                },
                cutout:'65%'
            }
        });
    }
});
JS;
?>
