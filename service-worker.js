const CACHE_NAME = 'noxara-v1.0.0';
const OFFLINE_URL = '/errors/maintenance.php';

const APP_SHELL = [
    '/',
    '/assets/css/style.css',
    '/assets/css/premium.css',
    '/assets/css/animations.css',
    '/assets/css/mobile.css',
    '/assets/js/main.js',
    '/assets/js/animations.js',
    '/assets/js/mobile.js',
    '/assets/img/icons/icons.svg',
    '/assets/img/pwa/icon-192.svg',
    '/assets/img/pwa/icon-512.svg',
    '/manifest.json'
];

// Install - cache app shell
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(APP_SHELL);
        })
    );
    self.skipWaiting();
});

// Activate - clean old caches
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames
                    .filter((name) => name !== CACHE_NAME)
                    .map((name) => caches.delete(name))
            );
        })
    );
    self.clients.claim();
});

// Fetch strategy
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // Network-first for API calls
    if (url.pathname.startsWith('/api/')) {
        event.respondWith(networkFirst(request));
        return;
    }

    // Cache-first for static assets
    if (isStaticAsset(url.pathname)) {
        event.respondWith(cacheFirst(request));
        return;
    }

    // Network-first for pages
    event.respondWith(networkFirst(request));
});

function isStaticAsset(pathname) {
    return /\.(css|js|svg|png|jpg|jpeg|gif|ico|webp|woff|woff2|ttf|eot)$/i.test(pathname);
}

async function cacheFirst(request) {
    const cached = await caches.match(request);
    if (cached) {
        return cached;
    }
    try {
        const response = await fetch(request);
        if (response.ok) {
            const cache = await caches.open(CACHE_NAME);
            cache.put(request, response.clone());
        }
        return response;
    } catch (error) {
        return new Response('', { status: 503, statusText: 'Service Unavailable' });
    }
}

async function networkFirst(request) {
    try {
        const response = await fetch(request);
        if (response.ok) {
            const cache = await caches.open(CACHE_NAME);
            cache.put(request, response.clone());
        }
        return response;
    } catch (error) {
        const cached = await caches.match(request);
        if (cached) {
            return cached;
        }
        // Offline fallback for navigation requests
        if (request.mode === 'navigate') {
            return caches.match(OFFLINE_URL);
        }
        return new Response('', { status: 503, statusText: 'Service Unavailable' });
    }
}
