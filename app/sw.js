/**
 * sw.js — SiMoU Service Worker
 * Strategy:
 *  - Static shell assets  → Cache-first (lama di-cache, pakai versi baru saat update)
 *  - Admin/API requests   → Network-first, fallback ke cache atau offline page
 *  - Navigasi offline     → Tampilkan halaman offline.html
 */

'use strict';

const CACHE_VERSION  = 'simou-v1';
const OFFLINE_URL    = '/offline.html';

// Aset statis yang di-precache saat install
const PRECACHE_ASSETS = [
    OFFLINE_URL,
    '/assets/css/style.css',
    '/assets/js/main.js',
    '/assets/image/logo.png',
    '/assets/image/favicon.png',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css',
    'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap',
    'https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.min.css',
    'https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js',
];

// ── Install: precache shell assets ───────────────────────────────────────
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_VERSION).then(cache => {
            // Cache assets satu per satu agar satu gagal tidak blok semua
            return Promise.allSettled(
                PRECACHE_ASSETS.map(url =>
                    cache.add(url).catch(err =>
                        console.warn('[SW] Gagal cache:', url, err)
                    )
                )
            );
        }).then(() => self.skipWaiting())
    );
});

// ── Activate: bersihkan cache lama ────────────────────────────────────────
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys =>
            Promise.all(
                keys
                    .filter(key => key !== CACHE_VERSION)
                    .map(key => {
                        console.log('[SW] Hapus cache lama:', key);
                        return caches.delete(key);
                    })
            )
        ).then(() => self.clients.claim())
    );
});

// ── Fetch: strategi per tipe request ─────────────────────────────────────
self.addEventListener('fetch', event => {
    const req = event.request;
    const url = new URL(req.url);

    // 1. Hanya tangani GET
    if (req.method !== 'GET') return;

    // 2. Jangan tangani request ke origin lain yang bukan CDN statis
    //    (biarkan browser handle langsung)
    const isExternalCDN = [
        'cdnjs.cloudflare.com',
        'fonts.googleapis.com',
        'fonts.gstatic.com',
        'cdn.jsdelivr.net',
    ].some(cdn => url.hostname.includes(cdn));

    if (!isExternalCDN && url.origin !== self.location.origin) return;

    // 3. Aset statis → Cache-first
    const isStaticAsset = url.pathname.startsWith('/assets/') || isExternalCDN;
    if (isStaticAsset) {
        event.respondWith(cacheFirst(req));
        return;
    }

    // 4. Request PDF dokumen → Network-only (sensitif, jangan di-cache)
    if (url.pathname.includes('/document') || url.pathname.startsWith('/uploads/')) {
        return; // biarkan browser handle
    }

    // 5. Navigasi halaman (HTML) → Network-first, fallback ke offline
    if (req.mode === 'navigate') {
        event.respondWith(networkFirstWithOfflineFallback(req));
        return;
    }

    // 6. Lainnya → Network-first with cache fallback
    event.respondWith(networkFirst(req));
});

// ── Strategy: Cache-First ─────────────────────────────────────────────────
async function cacheFirst(req) {
    const cached = await caches.match(req);
    if (cached) return cached;

    try {
        const response = await fetch(req);
        if (response.ok) {
            const cache = await caches.open(CACHE_VERSION);
            cache.put(req, response.clone());
        }
        return response;
    } catch {
        // Tidak ada di cache dan network gagal — kembalikan respons kosong
        return new Response('', { status: 408, statusText: 'Offline' });
    }
}

// ── Strategy: Network-First ───────────────────────────────────────────────
async function networkFirst(req) {
    try {
        const response = await fetch(req);
        if (response.ok) {
            const cache = await caches.open(CACHE_VERSION);
            cache.put(req, response.clone());
        }
        return response;
    } catch {
        const cached = await caches.match(req);
        return cached || new Response('', { status: 503, statusText: 'Service Unavailable' });
    }
}

// ── Strategy: Network-First + Offline Page Fallback (navigasi) ───────────
async function networkFirstWithOfflineFallback(req) {
    try {
        const response = await fetch(req);
        // Hanya update cache untuk respons HTML sukses
        if (response.ok) {
            const cache = await caches.open(CACHE_VERSION);
            cache.put(req, response.clone());
        }
        return response;
    } catch {
        const cached = await caches.match(req);
        if (cached) return cached;

        // Tampilkan halaman offline sebagai fallback terakhir
        return caches.match(OFFLINE_URL);
    }
}
