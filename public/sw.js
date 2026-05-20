/**
 * RE-OS Service Worker
 *
 * Strategy:
 *  - Statik asset'ler (CDN font/script): cache-first
 *  - Admin sayfaları: network-first, fallback offline shell
 *  - API çağrıları: network-only (cache yapma — fresh data lazım)
 *  - POST/PUT/DELETE: SW'ye dokunmadan geçer
 *
 * Cache versionu değiştirilince eski cache silinir.
 */

const CACHE_VERSION = 'reos-v1';
const OFFLINE_URL = '/offline.html';

const STATIC_ASSETS = [
    OFFLINE_URL,
    '/manifest.json',
];

// Install: pre-cache offline shell
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_VERSION).then((cache) => cache.addAll(STATIC_ASSETS))
    );
    self.skipWaiting();
});

// Activate: clean old caches
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(
                keys
                    .filter((k) => k !== CACHE_VERSION)
                    .map((k) => caches.delete(k))
            )
        )
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    const req = event.request;

    // Only handle GET — mutations go straight through
    if (req.method !== 'GET') return;

    const url = new URL(req.url);

    // Don't intercept cross-origin (CDN, API'ler)
    if (url.origin !== self.location.origin) return;

    // /api/* — network-only, fresh data critical
    if (url.pathname.startsWith('/api/')) return;

    // /storage/* — cache-first (kullanıcı upload'ları)
    if (url.pathname.startsWith('/storage/')) {
        event.respondWith(cacheFirst(req));
        return;
    }

    // Admin sayfaları + diğer: network-first, offline fallback
    event.respondWith(
        fetch(req)
            .then((res) => {
                // Successful HTML — cache'le
                if (res.ok && req.headers.get('accept')?.includes('text/html')) {
                    const clone = res.clone();
                    caches.open(CACHE_VERSION).then((cache) => cache.put(req, clone));
                }
                return res;
            })
            .catch(() => caches.match(req).then((cached) => cached || caches.match(OFFLINE_URL)))
    );
});

async function cacheFirst(req) {
    const cached = await caches.match(req);
    if (cached) return cached;
    try {
        const res = await fetch(req);
        if (res.ok) {
            const cache = await caches.open(CACHE_VERSION);
            cache.put(req, res.clone());
        }
        return res;
    } catch (e) {
        return new Response('', { status: 504 });
    }
}
