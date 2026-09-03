        <!-- Application Footer (Sticky Copyright Bar) -->
        <footer class="app-footer">
            <div class="app-footer-inner">
                <span class="footer-copyright">
                    <strong>SiMoU</strong>
                    <span class="footer-sep">&middot;</span>
                    &copy; <?= date('Y') ?> ICT RSUD Kilisuci Kota Kediri
                </span>
                <span class="footer-version">
                    v<?= e(APP_VERSION) ?>
                </span>
            </div>
        </footer>


        <!-- /page body -->
    </div><!-- /.main-content -->
</div><!-- /.app-shell -->

<!-- Top SPA Progress Bar -->
<div id="topProgressBar"></div>

<!-- Toast Container -->
<div class="toast-container" id="toastContainer"></div>

<!-- Fullscreen Upload / Processing Loading Overlay -->
<div class="global-loading-overlay" id="globalLoadingOverlay">
    <div class="global-loading-card">
        <div class="spinner-lg"></div>
        <div>
            <h4 style="font-size:1rem; font-weight:700; color:var(--text-main); margin-bottom:4px;" id="loadingTitle">Memproses Request...</h4>
            <p style="font-size:.8rem; color:var(--text-muted); margin:0;" id="loadingDesc">Mohon tunggu sebentar, sedang memproses data.</p>
        </div>
    </div>
</div>

<!-- Custom UI Alert Modal (Replaces browser alert()) -->
<div class="modal-overlay" id="globalAlertModal">
    <div class="modal" style="max-width:440px;">
        <div class="modal-header">
            <div class="modal-title" id="alertModalTitle"><i class="fa-solid fa-circle-info" style="color:var(--primary);"></i> Perhatian</div>
            <button class="modal-close" onclick="closeModal('globalAlertModal')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body" id="alertModalMessage" style="font-size:.88rem; line-height:1.6; color:var(--text-main);"></div>
        <div class="modal-footer">
            <button type="button" class="btn btn-primary" onclick="closeModal('globalAlertModal')">OK, Mengerti</button>
        </div>
    </div>
</div>

<!-- Custom UI Confirm Modal (Generic - replaces browser confirm()) -->
<div class="modal-overlay" id="globalConfirmModal">
    <div class="modal" style="max-width:440px;">
        <div class="modal-header">
            <div class="modal-title" id="confirmModalTitle"><i class="fa-solid fa-circle-question" style="color:var(--warning);"></i> Konfirmasi</div>
            <button class="modal-close" onclick="closeConfirmModal(false)"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body" id="confirmModalMessage" style="font-size:.88rem; line-height:1.6; color:var(--text-main);">
            Apakah Anda yakin ingin melanjutkan tindakan ini?
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeConfirmModal(false)">Batal</button>
            <button type="button" class="btn btn-primary" id="confirmModalSubmitBtn" onclick="closeConfirmModal(true)">Ya, Lanjutkan</button>
        </div>
    </div>
</div>

<!-- Logout Confirmation Modal -->
<div class="modal-overlay" id="logoutConfirmModal">
    <div class="modal" style="max-width:440px;">
        <div class="modal-header">
            <div class="modal-title">
                <i class="fa-solid fa-right-from-bracket" style="color:var(--danger);"></i>
                Konfirmasi Keluar
            </div>
            <button class="modal-close" onclick="closeModal('logoutConfirmModal')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body" style="font-size:.88rem; line-height:1.7; color:var(--text-main);">
            <p>Apakah Anda yakin ingin <strong>keluar</strong> dari aplikasi SiMoU?</p>
            <p style="color:var(--text-muted); font-size:.82rem; margin-top:8px;">
                Sesi aktif Anda akan diakhiri dan Anda perlu login kembali untuk mengakses sistem.
            </p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeModal('logoutConfirmModal')">
                <i class="fa-solid fa-xmark"></i> Batal
            </button>
            <a href="<?= APP_URL ?>/logout" class="btn btn-primary" style="background:var(--danger); border-color:var(--danger);">
                <i class="fa-solid fa-right-from-bracket"></i> Ya, Keluar
            </a>
        </div>
    </div>
</div>

<!-- Modal Update Password (Profile Menu) -->
<div class="modal-overlay" id="modalUpdatePassword">
    <div class="modal" style="max-width:460px;">
        <div class="modal-header">
            <div class="modal-title"><i class="fa-solid fa-key" style="color:var(--primary);"></i> Ubah Password Saya</div>
            <button class="modal-close" onclick="closeModal('modalUpdatePassword')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form method="POST" action="<?= APP_URL ?>/admin/profile/password" onsubmit="return handleSelfPasswordSubmit(this)">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="alert alert-info mb-3" style="font-size:.78rem; padding:10px 14px;">
                    <i class="fa-solid fa-shield-halved"></i>
                    <span><strong>Syarat Password Kuat:</strong> Minimal 8 karakter, kombinasi huruf besar (A-Z), huruf kecil (a-z), dan angka (0-9).</span>
                </div>
                <div class="form-group">
                    <label class="form-label">Password saat ini (Lama) <span style="color:var(--danger);">*</span></label>
                    <input type="password" name="current_password" id="self_current_password" class="form-control" placeholder="Masukkan password Anda saat ini" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Password Baru <span style="color:var(--danger);">*</span></label>
                    <input type="password" name="password" id="self_password" class="form-control" placeholder="Masukkan password baru" required minlength="8">
                </div>
                <div class="form-group">
                    <label class="form-label">Ulangi Password Baru <span style="color:var(--danger);">*</span></label>
                    <input type="password" name="password_confirm" id="self_password_confirm" class="form-control" placeholder="Ketik ulang password baru" required minlength="8">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('modalUpdatePassword')">Batal</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-save"></i> Perbarui Password
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<!-- Tom Select JS -->
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<!-- SiMoU JS (with cache buster) -->
<script src="<?= APP_URL ?>/assets/js/main.js?v=<?= APP_VERSION ?>.<?= time() ?>"></script>

<script>
/* ── Sidebar toggle (mobile) ───────────────────────────── */
function toggleSidebar() {
    document.getElementById('sidebar')?.classList.toggle('open');
    document.getElementById('sidebarOverlay')?.classList.toggle('open');
}
function closeSidebar() {
    document.getElementById('sidebar')?.classList.remove('open');
    document.getElementById('sidebarOverlay')?.classList.remove('open');
}

/* ── Modal helpers ─────────────────────────────────────── */
function openModal(id) {
    const el = document.getElementById(id);
    if (el) { el.classList.add('open'); document.body.style.overflow = 'hidden'; }
}
function closeModal(id) {
    const el = document.getElementById(id);
    if (el) { el.classList.remove('open'); document.body.style.overflow = ''; }
}

// Close modal on overlay click
document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.remove('open');
            document.body.style.overflow = '';
        }
    });
});

/* ── Profile Dropdown Helpers ──────────────────────────── */
function toggleProfileDropdown(e) {
    e.stopPropagation();
    const menu = document.getElementById('profileDropdownMenu');
    if (menu) menu.classList.toggle('show');
}
function closeProfileDropdown() {
    const menu = document.getElementById('profileDropdownMenu');
    if (menu) menu.classList.remove('show');
}
document.addEventListener('click', function(e) {
    const wrapper = document.querySelector('.profile-dropdown-wrapper');
    if (wrapper && !wrapper.contains(e.target)) {
        closeProfileDropdown();
    }
});

/* ── Self Password Verification ────────────────────────── */
function handleSelfPasswordSubmit(form) {
    const cur = document.getElementById('self_current_password').value;
    const p1  = document.getElementById('self_password').value;
    const p2  = document.getElementById('self_password_confirm').value;

    if (!cur) {
        showToast('Password lama (saat ini) wajib diisi!', 'error');
        return false;
    }
    if (p1 !== p2) {
        showToast('Konfirmasi password baru tidak cocok!', 'error');
        return false;
    }
    if (p1.length < 8) {
        showToast('Password terlalu pendek (minimal 8 karakter).', 'error');
        return false;
    }
    if (!/[A-Z]/.test(p1)) {
        showToast('Password harus mengandung minimal 1 huruf besar (A-Z).', 'error');
        return false;
    }
    if (!/[a-z]/.test(p1)) {
        showToast('Password harus mengandung minimal 1 huruf kecil (a-z).', 'error');
        return false;
    }
    if (!/[0-9]/.test(p1)) {
        showToast('Password harus mengandung minimal 1 angka (0-9).', 'error');
        return false;
    }
    startProgressBar();
    return true;
}

/* ── Logout Confirm Modal ──────────────────────────────── */
function confirmLogout(e) {
    if (e && e.preventDefault) e.preventDefault();
    openModal('logoutConfirmModal');
}

/* ── Auto-dismiss Flash Alerts ────────────────────────── */
setTimeout(() => {
    document.querySelectorAll('.alert').forEach(a => {
        a.style.transition = 'opacity .5s ease';
        a.style.opacity = '0';
        setTimeout(() => a.remove(), 500);
    });
}, 5000);

/* ── Tom Select: init all select.form-control with dropdown_input search plugin ── */
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('select.form-control, select[data-tom]').forEach(el => {
        if (el.tomselect || el.hasAttribute('data-no-tom')) return;
        new TomSelect(el, {
            plugins: ['dropdown_input'],
            allowEmptyOption: true,
            placeholder: el.getAttribute('placeholder') || el.querySelector('option')?.textContent || 'Pilih...',
            maxOptions: 300
        });
    });
});
</script>

<?php if (isset($extraScript)): ?>
<script><?= $extraScript ?></script>
<?php endif; ?>

</body>
</html>
