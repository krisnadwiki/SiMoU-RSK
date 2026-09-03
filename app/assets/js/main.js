/**
 * main.js — SiMoU UI Utilities
 * Toast Notifications, SPA Progress Bar, Upload Loader, Custom Modals, Dark/Light Theme
 */

'use strict';

// Global Confirm Handler state
let confirmCallback = null;

document.addEventListener('DOMContentLoaded', () => {

    // ── 1. Theme Init ────────────────────────────────────────────────────────
    initTheme();

    // ── 2. SPA Top Progress Bar ──────────────────────────────────────────────
    initTopProgressBar();

    // ── 3. Auto Form Upload Loader ───────────────────────────────────────────
    initFormLoaders();

    // ── 4. Drag & Drop File Upload Areas ────────────────────────────────────
    initDragAndDrop();

    // ── 5. Global Chart.js Defaults ─────────────────────────────────────────
    if (typeof Chart !== 'undefined') {
        Chart.defaults.font.family = "'Inter', system-ui, sans-serif";
        Chart.defaults.font.size   = 12;
        Chart.defaults.color       = '#6b8fa3';
    }

    // ── 6. Auto-dismiss Flash Alerts ────────────────────────────────────────
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(el => {
            el.style.transition = 'opacity .5s ease';
            el.style.opacity    = '0';
            setTimeout(() => el.remove(), 500);
        });
    }, 5000);
});

/**
 * ── Dark / Light Theme System ──────────────────────────────────────────────
 */
function initTheme() {
    const saved = localStorage.getItem('simou-theme') || 'light';
    applyTheme(saved);
}

function applyTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    const icon = document.getElementById('themeIcon');
    if (icon) {
        icon.className = theme === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
    }
    localStorage.setItem('simou-theme', theme);
}

function toggleTheme() {
    const current = document.documentElement.getAttribute('data-theme') || 'light';
    applyTheme(current === 'dark' ? 'light' : 'dark');
}

/**
 * ── Toast Notification System (Contextual & Action-Representative) ──────────
 */
function resolveToastMetadata(message, type) {
    const lower = String(message).toLowerCase();
    let title = 'Informasi';
    let icon  = 'fa-circle-info';

    if (type === 'success') {
        title = 'Berhasil';
        icon  = 'fa-circle-check';

        if (lower.includes('selamat datang') || lower.includes('login')) {
            title = 'Login Berhasil';
            icon  = 'fa-right-to-bracket';
        } else if (lower.includes('ditambahkan') || lower.includes('disimpan')) {
            title = 'Data Tersimpan';
            icon  = 'fa-floppy-disk';
        } else if (lower.includes('diperbarui') || lower.includes('diubah')) {
            title = 'Perubahan Disimpan';
            icon  = 'fa-pen-to-square';
        } else if (lower.includes('dihapus')) {
            title = 'Data Dihapus';
            icon  = 'fa-trash-can';
        } else if (lower.includes('perpanjang') || lower.includes('addendum')) {
            title = 'MoU Diperpanjang';
            icon  = 'fa-rotate-right';
        } else if (lower.includes('password') || lower.includes('sandi')) {
            title = 'Keamanan Akun';
            icon  = 'fa-key';
        } else if (lower.includes('pulih') || lower.includes('backup') || lower.includes('restore') || lower.includes('unduh')) {
            title = 'Database & Cadangan';
            icon  = 'fa-database';
        } else if (lower.includes('upload') || lower.includes('berkas') || lower.includes('dokumen')) {
            title = 'Dokumen / Berkas';
            icon  = 'fa-file-circle-check';
        }
    } else if (type === 'error') {
        title = 'Gagal';
        icon  = 'fa-circle-xmark';

        if (lower.includes('akses') || lower.includes('ditolak') || lower.includes('terbatas')) {
            title = 'Akses Ditolak';
            icon  = 'fa-shield-halved';
        } else if (lower.includes('password') || lower.includes('salah')) {
            title = 'Autentikasi Gagal';
            icon  = 'fa-lock';
        } else if (lower.includes('format') || lower.includes('ukuran') || lower.includes('file') || lower.includes('pdf')) {
            title = 'Berkas Tidak Valid';
            icon  = 'fa-file-circle-xmark';
        } else if (lower.includes('kunci') || lower.includes('lockout')) {
            title = 'Akun Terkunci';
            icon  = 'fa-user-lock';
        }
    } else if (type === 'warning') {
        title = 'Perhatian';
        icon  = 'fa-triangle-exclamation';

        if (lower.includes('sesi') || lower.includes('berakhir') || lower.includes('expired')) {
            title = 'Sesi Berakhir';
            icon  = 'fa-clock-rotate-left';
        } else if (lower.includes('wajib') || lower.includes('isi') || lower.includes('cocok')) {
            title = 'Periksa Formulir';
            icon  = 'fa-circle-exclamation';
        }
    }

    return { title, icon };
}

function showToast(message, type = 'success', duration = 4000) {
    let container = document.getElementById('toastContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toastContainer';
        container.className = 'toast-container';
        document.body.appendChild(container);
    }

    const { title, icon } = resolveToastMetadata(message, type);

    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
        <div class="toast-icon-wrapper">
            <i class="fa-solid ${icon}"></i>
        </div>
        <div class="toast-body">
            <div class="toast-title">${title}</div>
            <div class="toast-message">${escapeHtml(message)}</div>
        </div>
        <button class="toast-close" type="button" aria-label="Tutup notifikasi">
            <i class="fa-solid fa-xmark"></i>
        </button>
        <div class="toast-progress" style="animation-duration: ${duration}ms;"></div>
    `;

    const closeBtn = toast.querySelector('.toast-close');
    let timer;

    const dismissToast = () => {
        clearTimeout(timer);
        toast.style.animation = 'toastFadeOut 0.3s cubic-bezier(0.4, 0, 1, 1) forwards';
        setTimeout(() => toast.remove(), 300);
    };

    closeBtn.addEventListener('click', dismissToast);

    container.appendChild(toast);

    timer = setTimeout(dismissToast, duration);

    // Pause countdown timer on hover
    toast.addEventListener('mouseenter', () => {
        clearTimeout(timer);
        const bar = toast.querySelector('.toast-progress');
        if (bar) bar.style.animationPlayState = 'paused';
    });

    toast.addEventListener('mouseleave', () => {
        const bar = toast.querySelector('.toast-progress');
        if (bar) bar.style.animationPlayState = 'running';
        timer = setTimeout(dismissToast, 2000);
    });
}

/**
 * ── Custom Alert Modal (Replaces browser alert()) ──────────────────────────
 */
function showAlertModal(message, title = 'Informasi') {
    const titleEl = document.getElementById('alertModalTitle');
    const msgEl   = document.getElementById('alertModalMessage');
    if (titleEl) titleEl.innerHTML = `<i class="fa-solid fa-circle-info" style="color:var(--primary);"></i> ${escapeHtml(title)}`;
    if (msgEl)   msgEl.textContent = message;
    openModal('globalAlertModal');
}

// Override global window.alert with custom UI modal
window.alert = function(msg) {
    showAlertModal(String(msg));
};

/**
 * ── Custom Confirm Modal (Replaces browser confirm()) ──────────────────────
 */
function showConfirmModal(message, title = 'Konfirmasi', onConfirm = null) {
    confirmCallback = onConfirm;
    const titleEl = document.getElementById('confirmModalTitle');
    const msgEl   = document.getElementById('confirmModalMessage');
    if (titleEl) titleEl.innerHTML = `<i class="fa-solid fa-circle-question" style="color:var(--warning);"></i> ${escapeHtml(title)}`;
    if (msgEl)   msgEl.textContent = message;
    openModal('globalConfirmModal');
}

function closeConfirmModal(result) {
    closeModal('globalConfirmModal');
    if (result && typeof confirmCallback === 'function') {
        confirmCallback();
    }
    confirmCallback = null;
}

/**
 * ── Global Fullscreen Loading / Upload Overlay ─────────────────────────────
 */
function showLoadingOverlay(title = 'Memproses Request…', desc = 'Mohon tunggu sebentar, sedang memproses data.') {
    const overlay = document.getElementById('globalLoadingOverlay');
    const tEl     = document.getElementById('loadingTitle');
    const dEl     = document.getElementById('loadingDesc');
    if (tEl) tEl.textContent = title;
    if (dEl) dEl.textContent = desc;
    if (overlay) overlay.classList.add('active');
}

function hideLoadingOverlay() {
    const overlay = document.getElementById('globalLoadingOverlay');
    if (overlay) overlay.classList.remove('active');
}

/**
 * ── SPA Top Progress Bar ───────────────────────────────────────────────────
 */
function initTopProgressBar() {
    const bar = document.getElementById('topProgressBar');
    if (!bar) return;

    document.querySelectorAll('a[href]:not([target="_blank"]):not([href^="#"]):not([href^="javascript"]):not([onclick])').forEach(link => {
        link.addEventListener('click', () => startProgressBar());
    });
}

function startProgressBar() {
    const bar = document.getElementById('topProgressBar');
    if (!bar) return;
    bar.style.width = '0%';
    bar.classList.add('active');
    setTimeout(() => bar.style.width = '45%', 50);
    setTimeout(() => bar.style.width = '80%', 200);
}

function finishProgressBar() {
    const bar = document.getElementById('topProgressBar');
    if (!bar) return;
    bar.style.width = '100%';
    setTimeout(() => {
        bar.classList.remove('active');
        bar.style.width = '0%';
    }, 400);
}

/**
 * ── Auto Form Upload Loader ────────────────────────────────────────────────
 */
function initFormLoaders() {
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function() {
            const fileInput = this.querySelector('input[type="file"]');
            if (fileInput && fileInput.files.length > 0) {
                showLoadingOverlay('Mengunggah Berkas PDF…', 'Mohon tunggu, berkas sedang diunggah dan diverifikasi.');
            } else {
                startProgressBar();
            }
        });
    });
}

/**
 * ── Drag & Drop File Upload Handler ────────────────────────────────────────
 */
function initDragAndDrop() {
    document.querySelectorAll('.file-upload-area').forEach(area => {
        const input = area.querySelector('input[type="file"]');
        if (!input) return;

        ['dragenter', 'dragover'].forEach(evt => {
            area.addEventListener(evt, e => {
                e.preventDefault();
                area.style.borderColor     = 'var(--primary)';
                area.style.backgroundColor = 'var(--primary-xl)';
            });
        });

        ['dragleave', 'drop'].forEach(evt => {
            area.addEventListener(evt, e => {
                e.preventDefault();
                area.style.borderColor     = '';
                area.style.backgroundColor = '';
                if (evt === 'drop' && e.dataTransfer?.files.length) {
                    input.files = e.dataTransfer.files;
                    input.dispatchEvent(new Event('change'));
                    showToast(`File "${e.dataTransfer.files[0].name}" dipilih`, 'info');
                }
            });
        });
    });
}

/**
 * Helper sanitasi HTML sederhana
 */
function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}
