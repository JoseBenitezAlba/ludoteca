// Service worker: assets y portadas en cache; las paginas siempre de red (con aviso sin conexion).
const VERSION = 'v1';
const ESTATICO = `estatico-${VERSION}`;
const IMAGENES = `imagenes-${VERSION}`;

self.addEventListener('install', (e) => {
    e.waitUntil(caches.open(ESTATICO).then((c) => c.add('/offline.html')));
    self.skipWaiting();
});

self.addEventListener('activate', (e) => {
    e.waitUntil(
        caches.keys().then((ks) => Promise.all(ks.filter((k) => ![ESTATICO, IMAGENES].includes(k)).map((k) => caches.delete(k)))),
    );
    self.clients.claim();
});

self.addEventListener('fetch', (e) => {
    const req = e.request;
    if (req.method !== 'GET') return;
    const url = new URL(req.url);

    if (req.mode === 'navigate') {
        e.respondWith(fetch(req).catch(() => caches.match('/offline.html')));
        return;
    }
    if (url.pathname.startsWith('/build/') || url.hostname === 'fonts.bunny.net' || url.hostname.endsWith('.bunny.net')) {
        e.respondWith(caches.open(ESTATICO).then(async (c) => {
            const hit = await c.match(req);
            if (hit) return hit;
            const res = await fetch(req);
            if (res.ok) c.put(req, res.clone());
            return res;
        }));
        return;
    }
    if (url.hostname === 'media.rawg.io') {
        e.respondWith(caches.open(IMAGENES).then(async (c) => {
            const hit = await c.match(req);
            if (hit) return hit;
            try {
                const res = await fetch(req);
                if (res.ok || res.type === 'opaque') c.put(req, res.clone());
                return res;
            } catch (err) {
                return hit || Response.error();
            }
        }));
    }
});
