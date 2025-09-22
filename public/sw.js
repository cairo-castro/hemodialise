// Service Worker for Sistema de Hemodiálise
// Optimized for performance and device detection caching

const CACHE_NAME = 'hemodialise-v1.2';
const DEVICE_CACHE_NAME = 'hemodialise-device-cache-v1';

// Essential resources to cache immediately
const ESSENTIAL_CACHE = [
    '/',
    '/login',
    '/css/app.css',
    '/js/app.js',
    '/offline',
    '/manifest.json'
];

// Resources to cache on demand
const RUNTIME_CACHE = [
    '/mobile',
    '/mobile/ionic',
    '/desktop',
    '/desktop/preline',
    '/api/me'
];

// Network-first resources (always try network first)
const NETWORK_FIRST = [
    '/api/login',
    '/api/logout',
    '/api/checklists',
    '/api/patients',
    '/api/smart-route/detect'
];

self.addEventListener('install', event => {
    console.log('Service Worker installing...');
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => {
                console.log('Caching essential resources');
                return cache.addAll(ESSENTIAL_CACHE);
            })
            .then(() => {
                // Force activation
                return self.skipWaiting();
            })
    );
});

self.addEventListener('activate', event => {
    console.log('Service Worker activating...');
    event.waitUntil(
        Promise.all([
            // Clean up old caches
            caches.keys().then(cacheNames => {
                return Promise.all(
                    cacheNames.map(cacheName => {
                        if (cacheName !== CACHE_NAME && cacheName !== DEVICE_CACHE_NAME) {
                            console.log('Deleting old cache:', cacheName);
                            return caches.delete(cacheName);
                        }
                    })
                );
            }),
            // Take control of all clients
            self.clients.claim()
        ])
    );
});

self.addEventListener('fetch', event => {
    const { request } = event;
    const url = new URL(request.url);

    // Skip non-HTTP requests
    if (!url.protocol.startsWith('http')) {
        return;
    }

    // Handle different caching strategies based on resource type
    if (NETWORK_FIRST.some(path => url.pathname.startsWith(path))) {
        // Network first for API calls
        event.respondWith(networkFirst(request));
    } else if (request.destination === 'image') {
        // Cache first for images with fallback
        event.respondWith(cacheFirstWithFallback(request));
    } else if (url.pathname.includes('/api/')) {
        // Network only for other API calls not in NETWORK_FIRST
        event.respondWith(networkOnly(request));
    } else {
        // Stale while revalidate for HTML/CSS/JS
        event.respondWith(staleWhileRevalidate(request));
    }
});

// Cache first strategy with network fallback
async function cacheFirstWithFallback(request) {
    try {
        const cache = await caches.open(CACHE_NAME);
        const cachedResponse = await cache.match(request);

        if (cachedResponse) {
            return cachedResponse;
        }

        const networkResponse = await fetch(request);
        if (networkResponse.ok) {
            cache.put(request, networkResponse.clone());
        }
        return networkResponse;
    } catch (error) {
        console.error('Cache first failed:', error);
        return new Response('Offline', { status: 503 });
    }
}

// Network first strategy with cache fallback
async function networkFirst(request) {
    try {
        const networkResponse = await fetch(request);

        if (networkResponse.ok) {
            // Cache successful responses
            const cache = await caches.open(CACHE_NAME);
            cache.put(request, networkResponse.clone());
        }

        return networkResponse;
    } catch (error) {
        console.log('Network failed, trying cache:', request.url);
        const cache = await caches.open(CACHE_NAME);
        const cachedResponse = await cache.match(request);

        if (cachedResponse) {
            return cachedResponse;
        }

        // Return offline page for navigation requests
        if (request.mode === 'navigate') {
            return caches.match('/offline');
        }

        return new Response('Offline', { status: 503 });
    }
}

// Stale while revalidate strategy
async function staleWhileRevalidate(request) {
    const cache = await caches.open(CACHE_NAME);
    const cachedResponse = await cache.match(request);

    // Start fetch regardless of cache hit
    const fetchPromise = fetch(request).then(networkResponse => {
        if (networkResponse.ok) {
            cache.put(request, networkResponse.clone());
        }
        return networkResponse;
    }).catch(error => {
        console.log('Network failed for:', request.url);
        return null;
    });

    // Return cached version immediately if available
    if (cachedResponse) {
        return cachedResponse;
    }

    // Wait for network if no cache
    return await fetchPromise || new Response('Offline', { status: 503 });
}

// Network only strategy
async function networkOnly(request) {
    try {
        return await fetch(request);
    } catch (error) {
        return new Response('Network error', { status: 503 });
    }
}

// Handle device detection caching
self.addEventListener('message', event => {
    if (event.data && event.data.type === 'CACHE_DEVICE_INFO') {
        cacheDeviceInfo(event.data.deviceInfo);
    } else if (event.data && event.data.type === 'GET_DEVICE_INFO') {
        getDeviceInfo().then(deviceInfo => {
            event.ports[0].postMessage({ deviceInfo });
        });
    }
});

async function cacheDeviceInfo(deviceInfo) {
    try {
        const cache = await caches.open(DEVICE_CACHE_NAME);
        const response = new Response(JSON.stringify(deviceInfo));
        await cache.put('/device-info', response);
        console.log('Device info cached');
    } catch (error) {
        console.error('Failed to cache device info:', error);
    }
}

async function getDeviceInfo() {
    try {
        const cache = await caches.open(DEVICE_CACHE_NAME);
        const response = await cache.match('/device-info');
        if (response) {
            return await response.json();
        }
    } catch (error) {
        console.error('Failed to get cached device info:', error);
    }
    return null;
}

// Background sync for offline operations
self.addEventListener('sync', event => {
    if (event.tag === 'background-sync') {
        event.waitUntil(doBackgroundSync());
    }
});

async function doBackgroundSync() {
    console.log('Performing background sync...');

    try {
        // Sync any pending offline data
        const cache = await caches.open('offline-data');
        const requests = await cache.keys();

        for (const request of requests) {
            try {
                const response = await cache.match(request);
                const data = await response.json();

                // Attempt to send offline data
                await fetch(request.url, {
                    method: request.method,
                    headers: request.headers,
                    body: JSON.stringify(data)
                });

                // Remove from offline cache on success
                await cache.delete(request);
                console.log('Synced offline data:', request.url);
            } catch (error) {
                console.log('Failed to sync:', request.url, error);
            }
        }
    } catch (error) {
        console.error('Background sync failed:', error);
    }
}