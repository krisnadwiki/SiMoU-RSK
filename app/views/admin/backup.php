<?php
require __DIR__ . '/../layouts/header.php';
?>

<div class="page-body">

    <!-- Header / Toolbar -->
    <div class="d-flex align-center justify-between mb-3">
        <div>
            <h3 style="font-size:1rem; font-weight:700;">
                <i class="fa-solid fa-database" style="color:var(--primary);"></i>
                Backup &amp; Restore System
            </h3>
            <p class="text-muted fs-sm">Manajemen pemulihan database dan cadangan berkas dokumen MoU RSUD Kilisuci</p>
        </div>
    </div>

    <?php if (has_flash('success')): ?>
    <div class="alert alert-success d-flex align-center gap-2 mb-3">
        <i class="fa-solid fa-circle-check"></i>
        <div><?= flash('success') ?></div>
    </div>
    <?php endif; ?>

    <?php if (has_flash('error')): ?>
    <div class="alert alert-danger d-flex align-center gap-2 mb-3">
        <i class="fa-solid fa-circle-exclamation"></i>
        <div><?= flash('error') ?></div>
    </div>
    <?php endif; ?>

    <!-- Summary Stat Cards -->
    <div class="stats-grid mb-4">
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(37,99,235,.1); color:var(--primary);">
                <i class="fa-solid fa-server"></i>
            </div>
            <div class="stat-info">
                <div class="stat-label">Ukuran Database</div>
                <div class="stat-value"><?= number_format($dbSizeMb, 2) ?> MB</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(239,68,68,.1); color:var(--danger);">
                <i class="fa-solid fa-file-pdf"></i>
            </div>
            <div class="stat-info">
                <div class="stat-label">Arsip PDF Dokumen</div>
                <div class="stat-value"><?= $docCount ?> <small style="font-size:.72rem; font-weight:600; color:var(--text-muted);">(<?= number_format($docSizeMb, 2) ?> MB)</small></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(16,185,129,.1); color:var(--success);">
                <i class="fa-solid fa-file-contract"></i>
            </div>
            <div class="stat-info">
                <div class="stat-label">Total Dokumen MoU</div>
                <div class="stat-value"><?= $mousCount ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(139,92,246,.1); color:#8b5cf6;">
                <i class="fa-solid fa-clock-rotate-left"></i>
            </div>
            <div class="stat-info">
                <div class="stat-label">Terakhir Backup / Restore</div>
                <div class="stat-value" style="font-size: .85rem; font-weight:700;">
                    <?= $lastBackup ? format_date_id($lastBackup, 'medium') : 'Belum Ada' ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Backup Options Grid -->
    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap:20px; align-items:stretch; margin-bottom:24px;">

        <!-- Option 1: Backup Database (.SQL) -->
        <div class="card" style="display:flex; flex-direction:column; justify-content:space-between;">
            <div class="card-header">
                <div class="card-title">
                    <i class="fa-solid fa-database" style="color:var(--primary);"></i> Backup Database (.SQL)
                </div>
            </div>
            <div class="card-body" style="flex:1; display:flex; flex-direction:column; justify-content:space-between;">
                <div>
                    <p style="font-size:.84rem; color:var(--text-muted); margin-bottom:12px;">
                        Mengunduh skrip SQL cadangan berisi struktur seluruh tabel, data master, daftar MoU, dan audit log.
                    </p>
                    <div class="alert alert-info py-2 px-3 mb-3" style="font-size:.78rem;">
                        <i class="fa-solid fa-circle-info me-1"></i> Format <code>.sql</code> standar MariaDB / MySQL.
                    </div>
                </div>
                <form id="formBackupDb" method="POST" action="<?= APP_URL ?>/admin/backup/export">
                    <?= csrf_field() ?>
                    <button type="button" class="btn btn-primary" style="width:100%; justify-content:center; padding:10px;"
                            onclick="startInteractiveDownload('formBackupDb', 'Database SQL (.sql)', 'Membaca skema tabel dan menyusun skrip data SQL...')">
                        <i class="fa-solid fa-file-arrow-down"></i> Unduh Database (.SQL)
                    </button>
                </form>
            </div>
        </div>

        <!-- Option 2: Backup Document PDFs (.ZIP) -->
        <div class="card" style="display:flex; flex-direction:column; justify-content:space-between;">
            <div class="card-header">
                <div class="card-title">
                    <i class="fa-solid fa-file-zipper" style="color:var(--danger);"></i> Backup Berkas Dokumen (.ZIP)
                </div>
            </div>
            <div class="card-body" style="flex:1; display:flex; flex-direction:column; justify-content:space-between;">
                <div>
                    <p style="font-size:.84rem; color:var(--text-muted); margin-bottom:12px;">
                        Mengompres dan mengunduh seluruh berkas PDF dokumen MoU &amp; Addendum fisik ke dalam 1 file arsip ZIP.
                    </p>
                    <div class="alert alert-info py-2 px-3 mb-3" style="font-size:.78rem;">
                        <i class="fa-solid fa-file-pdf me-1" style="color:var(--danger);"></i> Total <?= $docCount ?> berkas PDF (<?= number_format($docSizeMb, 2) ?> MB).
                    </div>
                </div>
                <form id="formBackupDocs" method="POST" action="<?= APP_URL ?>/admin/backup/export-documents">
                    <?= csrf_field() ?>
                    <button type="button" class="btn btn-outline" style="width:100%; justify-content:center; padding:10px; color:var(--danger); border-color:var(--danger);"
                            onclick="startInteractiveDownload('formBackupDocs', 'Arsip Dokumen PDF (.zip)', 'Mengompresi berkas fisik PDF ke dalam arsip ZIP...')">
                        <i class="fa-solid fa-file-zipper"></i> Unduh Arsip PDF (.ZIP)
                    </button>
                </form>
            </div>
        </div>

        <!-- Option 3: Full Backup (Database + PDFs ZIP) -->
        <div class="card" style="display:flex; flex-direction:column; justify-content:space-between;">
            <div class="card-header">
                <div class="card-title">
                    <i class="fa-solid fa-box-archive" style="color:var(--success);"></i> Backup Lengkap System (.ZIP)
                </div>
            </div>
            <div class="card-body" style="flex:1; display:flex; flex-direction:column; justify-content:space-between;">
                <div>
                    <p style="font-size:.84rem; color:var(--text-muted); margin-bottom:12px;">
                        Mengunduh paket cadangan lengkap yang berisi database <code>.sql</code> sekaligus seluruh berkas PDF fisik.
                    </p>
                    <div class="alert alert-success py-2 px-3 mb-3" style="font-size:.78rem;">
                        <i class="fa-solid fa-shield-halved me-1"></i> Paket komplit untuk migrasi atau pencadangan total.
                    </div>
                </div>
                <form id="formBackupFull" method="POST" action="<?= APP_URL ?>/admin/backup/export-all">
                    <?= csrf_field() ?>
                    <button type="button" class="btn btn-success" style="width:100%; justify-content:center; padding:10px;"
                            onclick="startInteractiveDownload('formBackupFull', 'Full System Backup (.zip)', 'Menggabungkan skrip database SQL dan dokumen PDF ke arsip ZIP...')">
                        <i class="fa-solid fa-cloud-arrow-down"></i> Unduh Full Backup (.ZIP)
                    </button>
                </form>
            </div>
        </div>

    </div>

    <!-- Restore Section Card -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="fa-solid fa-rotate" style="color:var(--warning);"></i> Pemulihan Database (Restore SQL)
            </div>
        </div>
        <div class="card-body">
            <p style="font-size:.86rem; color:var(--text-muted); margin-bottom:16px;">
                Unggah berkas cadangan berekstensi <code>.sql</code> untuk memulihkan seluruh data dan struktur database sistem SiMoU.
            </p>

            <form id="restoreForm" method="POST" action="<?= APP_URL ?>/admin/backup/import" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <input type="hidden" name="is_ajax" value="1">

                <div class="form-group mb-3">
                    <label class="form-label">Pilih Berkas Backup Database (.sql) <span class="form-required">*</span></label>
                    <div class="file-upload-area" id="restoreZone">
                        <input type="file" name="backup_file" id="restoreFileInput" accept=".sql" required onchange="updateBackupLabel(this)">
                        <div class="file-upload-icon"><i class="fa-solid fa-file-code"></i></div>
                        <div class="file-upload-text" id="backupLabel">
                            Klik atau drag &amp; drop file .SQL di sini
                        </div>
                        <div class="file-upload-hint">Format berkas .sql</div>
                    </div>
                </div>

                <button type="button" class="btn btn-danger" style="width:100%; justify-content:center; padding:11px;" onclick="triggerRestoreModal()">
                    <i class="fa-solid fa-rotate"></i> Jalankan Pemulihan Database
                </button>
            </form>
        </div>
    </div>

</div>

<!-- ── Modal Konfirmasi Restore Database ────────────────────────── -->
<div class="modal-overlay" id="modalRestoreConfirm">
    <div class="modal" style="max-width:480px;">
        <div class="modal-header">
            <div class="modal-title">
                <i class="fa-solid fa-triangle-exclamation" style="color:var(--warning);"></i>
                Konfirmasi Pemulihan Database
            </div>
            <button type="button" class="modal-close" onclick="closeModal('modalRestoreConfirm')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body" style="font-size:.88rem; line-height:1.6; color:var(--text-main);">
            <p>Proses ini akan <strong>menimpa data yang ada di database saat ini</strong> dengan data dari berkas SQL yang Anda pilih.</p>
            <div style="background:var(--bg-surface-2); border:1px solid var(--border); border-radius:var(--radius-md); padding:10px 14px; margin-top:12px; font-size:.82rem; word-break:break-all;">
                <i class="fa-solid fa-file-code" style="color:var(--primary); margin-right:6px;"></i>
                <span id="modalFileName" style="font-weight:600;">file_backup.sql</span>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeModal('modalRestoreConfirm')">
                <i class="fa-solid fa-xmark"></i> Batal
            </button>
            <button type="button" class="btn btn-primary" style="background:var(--danger); border-color:var(--danger);" onclick="executeRestoreAjax()">
                <i class="fa-solid fa-rotate"></i> Ya, Lanjutkan Restore
            </button>
        </div>
    </div>
</div>

<!-- ── Modal Progress & Hasil Pemulihan Restore (Interactive Live Modal) ─ -->
<div class="modal-overlay" id="modalRestoreProgress">
    <div class="modal" style="max-width:460px; text-align:center;">
        <div class="modal-body" style="padding:28px 24px;">

            <!-- Icon Container -->
            <div id="restoreIconContainer" style="margin-bottom:16px;">
                <div style="width:64px; height:64px; border-radius:50%; background:rgba(37,99,235,.1); color:var(--primary); display:inline-flex; align-items:center; justify-content:center; font-size:1.8rem;">
                    <i class="fa-solid fa-database fa-spin" id="restoreIcon"></i>
                </div>
            </div>

            <!-- Title & Subtitle -->
            <h4 id="restoreProgressTitle" style="font-size:1.08rem; font-weight:700; color:var(--text-main); margin-bottom:6px;">
                Memulihkan Database...
            </h4>
            <p id="restoreProgressSub" style="font-size:.82rem; color:var(--text-muted); line-height:1.5; margin-bottom:20px;">
                Mohon tunggu beberapa saat, sistem sedang membaca dan menimpa skema database.
            </p>

            <!-- Dynamic Progress Bar -->
            <div style="background:var(--bg-surface-2); border-radius:8px; height:10px; overflow:hidden; margin-bottom:16px; position:relative;">
                <div id="restoreProgressBar" style="width:20%; height:100%; background:var(--primary); border-radius:8px; transition:width .4s ease, background-color .4s ease;"></div>
            </div>

            <!-- Step Progress Checklist -->
            <div style="font-size:.78rem; text-align:left; background:var(--bg-surface-2); border:1px solid var(--border); border-radius:var(--radius-md); padding:12px 16px;" id="restoreStepsContainer">
                <div id="rstep1" style="color:var(--primary); font-weight:600; margin-bottom:6px;">
                    <i class="fa-solid fa-spinner fa-spin me-1"></i> <span>Memverifikasi berkas backup .sql...</span>
                </div>
                <div id="rstep2" style="color:var(--text-muted); margin-bottom:6px;">
                    <i class="fa-regular fa-circle me-1"></i> <span>Membaca skema tabel &amp; mengeksekusi kueri SQL...</span>
                </div>
                <div id="rstep3" style="color:var(--text-muted);">
                    <i class="fa-regular fa-circle me-1"></i> <span>Menyinkronkan data &amp; mencatat log aktivitas...</span>
                </div>
            </div>

            <!-- Detailed Error Box (Hidden by default) -->
            <div id="restoreErrorBox" style="display:none; text-align:left; background:rgba(239,68,68,.08); border:1px solid rgba(239,68,68,.25); border-radius:var(--radius-md); padding:12px 14px; margin-top:16px; font-size:.8rem; color:var(--danger); word-break:break-word;">
                <i class="fa-solid fa-triangle-exclamation me-1"></i> <strong id="restoreErrorText">Detail error terjadi</strong>
            </div>

            <!-- Completion Actions (Hidden initially) -->
            <div id="restoreActions" style="display:none; margin-top:20px;">
                <button type="button" id="btnRestoreFinish" class="btn btn-primary" style="width:100%; justify-content:center; padding:10px;" onclick="window.location.reload()">
                    <i class="fa-solid fa-rotate-right"></i> Muat Ulang Halaman
                </button>
            </div>

        </div>
    </div>
</div>

<!-- ── Modal Interactive Download Loader ────────────────────────── -->
<div class="modal-overlay" id="modalDownloadProgress">
    <div class="modal" style="max-width:440px; text-align:center;">
        <div class="modal-body" style="padding:28px 24px;">

            <!-- Animated Status Icon -->
            <div id="dlIconContainer" style="margin-bottom:16px;">
                <div style="width:60px; height:60px; border-radius:50%; background:rgba(37,99,235,.1); color:var(--primary); display:inline-flex; align-items:center; justify-content:center; font-size:1.6rem;">
                    <i class="fa-solid fa-cloud-arrow-down fa-bounce" id="dlIcon"></i>
                </div>
            </div>

            <!-- Title & Subtitle -->
            <h4 id="dlProgressTitle" style="font-size:1.05rem; font-weight:700; color:var(--text-main); margin-bottom:6px;">
                Mempersiapkan Berkas...
            </h4>
            <p id="dlProgressSub" style="font-size:.82rem; color:var(--text-muted); line-height:1.5; margin-bottom:20px;">
                Mohon tunggu beberapa saat, sistem sedang menyusun berkas pencadangan.
            </p>

            <!-- Dynamic Progress Bar -->
            <div style="background:var(--bg-surface-2); border-radius:8px; height:10px; overflow:hidden; margin-bottom:16px; position:relative;">
                <div id="dlProgressBar" style="width:10%; height:100%; background:linear-gradient(90deg, var(--primary), var(--primary-d)); border-radius:8px; transition:width .4s ease;"></div>
            </div>

            <!-- Step Progress Checklist -->
            <div style="font-size:.78rem; text-align:left; background:var(--bg-surface-2); border:1px solid var(--border); border-radius:var(--radius-md); padding:10px 14px;" id="dlStepsContainer">
                <div id="step1" style="color:var(--primary); font-weight:600; margin-bottom:4px;">
                    <i class="fa-solid fa-spinner fa-spin me-1" id="step1Icon"></i> <span>Inisialisasi permintaan pencadangan...</span>
                </div>
                <div id="step2" style="color:var(--text-muted); margin-bottom:4px;">
                    <i class="fa-regular fa-circle me-1" id="step2Icon"></i> <span>Membaca &amp; kompresi berkas...</span>
                </div>
                <div id="step3" style="color:var(--text-muted);">
                    <i class="fa-regular fa-circle me-1" id="step3Icon"></i> <span>Pengiriman berkas ke browser...</span>
                </div>
            </div>

            <!-- Completion Action Button (hidden initially) -->
            <div id="dlCompleteAction" style="display:none; margin-top:20px;">
                <button type="button" class="btn btn-primary" style="width:100%; justify-content:center;" onclick="closeModal('modalDownloadProgress')">
                    <i class="fa-solid fa-check"></i> Selesai &amp; Tutup
                </button>
            </div>

        </div>
    </div>
</div>

<?php
$extraScript = <<<'JS'
function updateBackupLabel(input) {
    const label = document.getElementById('backupLabel');
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const ext = file.name.split('.').pop().toLowerCase();

        if (ext !== 'sql') {
            showToast('Format berkas tidak valid! Hanya berkas backup .sql yang diizinkan.', 'error');
            input.value = '';
            label.textContent = 'Klik atau drag & drop file .SQL di sini';
            return;
        }

        const size = (file.size / 1024 / 1024).toFixed(2);
        label.innerHTML = `<i class="fa-solid fa-file-code" style="color:var(--primary);"></i> <strong>${file.name}</strong> (${size} MB)`;
    } else {
        label.textContent = 'Klik atau drag & drop file .SQL di sini';
    }
}

function triggerRestoreModal() {
    const fileInput = document.getElementById('restoreFileInput');
    if (!fileInput.files || !fileInput.files[0]) {
        showToast('Silakan pilih berkas backup .sql terlebih dahulu!', 'error');
        return;
    }

    const file = fileInput.files[0];
    const size = (file.size / 1024 / 1024).toFixed(2);
    document.getElementById('modalFileName').textContent = `${file.name} (${size} MB)`;

    openModal('modalRestoreConfirm');
}

function executeRestoreAjax() {
    closeModal('modalRestoreConfirm');

    const form = document.getElementById('restoreForm');
    const formData = new FormData(form);

    // Reset Restore Progress Modal
    document.getElementById('restoreProgressTitle').textContent = 'Memulihkan Database...';
    document.getElementById('restoreProgressSub').textContent = 'Mohon tunggu beberapa saat, sistem sedang membaca dan menimpa skema database.';
    document.getElementById('restoreProgressBar').style.width = '25%';
    document.getElementById('restoreProgressBar').style.backgroundColor = 'var(--primary)';
    document.getElementById('restoreErrorBox').style.display = 'none';
    document.getElementById('restoreActions').style.display = 'none';

    document.getElementById('restoreIconContainer').innerHTML = `
        <div style="width:64px; height:64px; border-radius:50%; background:rgba(37,99,235,.1); color:var(--primary); display:inline-flex; align-items:center; justify-content:center; font-size:1.8rem;">
            <i class="fa-solid fa-database fa-spin"></i>
        </div>`;

    updateRStep('rstep1', 'done', 'Berkas backup .sql diverifikasi');
    updateRStep('rstep2', 'active', 'Membaca skema tabel & mengeksekusi kueri SQL...');
    updateRStep('rstep3', 'pending', 'Menyinkronkan data & mencatat log aktivitas');

    openModal('modalRestoreProgress');

    // Simulate progress while waiting for server response
    const timer1 = setTimeout(() => {
        document.getElementById('restoreProgressBar').style.width = '65%';
        updateRStep('rstep2', 'active', 'Proses penulisan ulang database sedang berjalan...');
    }, 1000);

    // Fetch AJAX Request
    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json().catch(() => ({ success: false, message: 'Respon server tidak dapat diproses.' })))
    .then(data => {
        clearTimeout(timer1);
        document.getElementById('restoreProgressBar').style.width = '100%';

        if (data.success) {
            updateRStep('rstep2', 'done', 'Eksekusi kueri SQL selesai 100%');
            updateRStep('rstep3', 'done', 'Status database & audit log berhasil disinkronkan');

            document.getElementById('restoreProgressTitle').textContent = 'Pemulihkan Database Berhasil!';
            document.getElementById('restoreProgressSub').textContent = data.message || 'Database telah berhasil dipulihkan secara utuh.';

            document.getElementById('restoreIconContainer').innerHTML = `
                <div style="width:64px; height:64px; border-radius:50%; background:rgba(16,185,129,.15); color:var(--success); display:inline-flex; align-items:center; justify-content:center; font-size:2rem;">
                    <i class="fa-solid fa-circle-check"></i>
                </div>`;

            document.getElementById('btnRestoreFinish').className = 'btn btn-primary';
            document.getElementById('btnRestoreFinish').innerHTML = '<i class="fa-solid fa-rotate-right"></i> Muat Ulang Halaman';
            document.getElementById('btnRestoreFinish').onclick = () => window.location.reload();
            document.getElementById('restoreActions').style.display = 'block';
        } else {
            document.getElementById('restoreProgressBar').style.backgroundColor = 'var(--danger)';
            updateRStep('rstep2', 'error', 'Gagal mengeksekusi kueri SQL');
            updateRStep('rstep3', 'error', 'Pemulihan database terhenti');

            document.getElementById('restoreProgressTitle').textContent = 'Pemulihan Database Gagal!';
            document.getElementById('restoreProgressSub').textContent = 'Terjadi kesalahan saat memulihkan database.';

            document.getElementById('restoreIconContainer').innerHTML = `
                <div style="width:64px; height:64px; border-radius:50%; background:rgba(239,68,68,.15); color:var(--danger); display:inline-flex; align-items:center; justify-content:center; font-size:2rem;">
                    <i class="fa-solid fa-circle-xmark"></i>
                </div>`;

            document.getElementById('restoreErrorText').textContent = data.message || 'Terjadi kesalahan pada server saat mengeksekusi berkas SQL.';
            document.getElementById('restoreErrorBox').style.display = 'block';

            document.getElementById('btnRestoreFinish').className = 'btn btn-outline';
            document.getElementById('btnRestoreFinish').innerHTML = '<i class="fa-solid fa-xmark"></i> Tutup &amp; Coba Lagi';
            document.getElementById('btnRestoreFinish').onclick = () => closeModal('modalRestoreProgress');
            document.getElementById('restoreActions').style.display = 'block';
        }
    })
    .catch(err => {
        clearTimeout(timer1);
        document.getElementById('restoreProgressBar').style.width = '100%';
        document.getElementById('restoreProgressBar').style.backgroundColor = 'var(--danger)';

        updateRStep('rstep2', 'error', 'Koneksi ke server terputus');
        updateRStep('rstep3', 'error', 'Pemulihan terhenti');

        document.getElementById('restoreProgressTitle').textContent = 'Gagal Memproses Restore';
        document.getElementById('restoreProgressSub').textContent = 'Tidak dapat terhubung ke server.';

        document.getElementById('restoreIconContainer').innerHTML = `
            <div style="width:64px; height:64px; border-radius:50%; background:rgba(239,68,68,.15); color:var(--danger); display:inline-flex; align-items:center; justify-content:center; font-size:2rem;">
                <i class="fa-solid fa-wifi"></i>
            </div>`;

        document.getElementById('restoreErrorText').textContent = 'Gagal menghubungkan ke server: ' + err.message;
        document.getElementById('restoreErrorBox').style.display = 'block';

        document.getElementById('btnRestoreFinish').className = 'btn btn-outline';
        document.getElementById('btnRestoreFinish').innerHTML = '<i class="fa-solid fa-xmark"></i> Tutup &amp; Coba Lagi';
        document.getElementById('btnRestoreFinish').onclick = () => closeModal('modalRestoreProgress');
        document.getElementById('restoreActions').style.display = 'block';
    });
}

function updateRStep(stepId, state, text) {
    const el = document.getElementById(stepId);
    if (!el) return;

    if (state === 'done') {
        el.style.color = 'var(--success)';
        el.style.fontWeight = '600';
        el.innerHTML = `<i class="fa-solid fa-circle-check me-1" style="color:var(--success);"></i> <span>${text}</span>`;
    } else if (state === 'active') {
        el.style.color = 'var(--primary)';
        el.style.fontWeight = '600';
        el.innerHTML = `<i class="fa-solid fa-spinner fa-spin me-1" style="color:var(--primary);"></i> <span>${text}</span>`;
    } else if (state === 'error') {
        el.style.color = 'var(--danger)';
        el.style.fontWeight = '600';
        el.innerHTML = `<i class="fa-solid fa-circle-xmark me-1" style="color:var(--danger);"></i> <span>${text}</span>`;
    } else {
        el.style.color = 'var(--text-muted)';
        el.style.fontWeight = 'normal';
        el.innerHTML = `<i class="fa-regular fa-circle me-1"></i> <span>${text}</span>`;
    }
}

function startInteractiveDownload(formId, itemTitle, subDesc) {
    const form = document.getElementById(formId);
    if (!form) return;

    document.getElementById('dlProgressTitle').textContent = `Mengunduh ${itemTitle}...`;
    document.getElementById('dlProgressSub').textContent = subDesc;
    document.getElementById('dlProgressBar').style.width = '15%';
    document.getElementById('dlCompleteAction').style.display = 'none';

    const iconContainer = document.getElementById('dlIconContainer');
    iconContainer.innerHTML = `
        <div style="width:60px; height:60px; border-radius:50%; background:rgba(37,99,235,.1); color:var(--primary); display:inline-flex; align-items:center; justify-content:center; font-size:1.6rem;">
            <i class="fa-solid fa-cloud-arrow-down fa-bounce"></i>
        </div>`;

    updateStep('step1', 'done', 'Inisialisasi permintaan pencadangan');
    updateStep('step2', 'active', 'Membaca & kompresi berkas...');
    updateStep('step3', 'pending', 'Pengiriman berkas ke browser');

    openModal('modalDownloadProgress');

    form.submit();

    setTimeout(() => {
        document.getElementById('dlProgressBar').style.width = '65%';
        updateStep('step2', 'done', 'Membaca & kompresi berkas selesai');
        updateStep('step3', 'active', 'Pengiriman berkas ke browser...');
    }, 1200);

    setTimeout(() => {
        document.getElementById('dlProgressBar').style.width = '100%';
        updateStep('step3', 'done', 'Berkas berhasil dikirim');

        document.getElementById('dlProgressTitle').textContent = 'Unduhan Berhasil Dimulai!';
        document.getElementById('dlProgressSub').textContent = `Berkas ${itemTitle} telah disiapkan dan sedang diunduh oleh browser Anda.`;

        iconContainer.innerHTML = `
            <div style="width:60px; height:60px; border-radius:50%; background:rgba(16,185,129,.15); color:var(--success); display:inline-flex; align-items:center; justify-content:center; font-size:1.8rem;">
                <i class="fa-solid fa-circle-check"></i>
            </div>`;

        document.getElementById('dlCompleteAction').style.display = 'block';
    }, 2800);
}

function updateStep(stepId, state, text) {
    const el = document.getElementById(stepId);
    if (!el) return;

    if (state === 'done') {
        el.style.color = 'var(--success)';
        el.style.fontWeight = '600';
        el.innerHTML = `<i class="fa-solid fa-circle-check me-1" style="color:var(--success);"></i> <span>${text}</span>`;
    } else if (state === 'active') {
        el.style.color = 'var(--primary)';
        el.style.fontWeight = '600';
        el.innerHTML = `<i class="fa-solid fa-spinner fa-spin me-1" style="color:var(--primary);"></i> <span>${text}</span>`;
    } else {
        el.style.color = 'var(--text-muted)';
        el.style.fontWeight = 'normal';
        el.innerHTML = `<i class="fa-regular fa-circle me-1"></i> <span>${text}</span>`;
    }
}
JS;

require __DIR__ . '/../layouts/footer.php';
?>
