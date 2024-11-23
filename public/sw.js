const CACHE_NAME = 'keplr-cache-v1';
const urlsToCache = [
    '/',
    '/styles/app.css',
    '/logo_keplr.png',
    '/manifest.json'
];

self.addEventListener('install', event => {
    console.log('[ServiceWorker] Installation...');
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => {
                console.log('[ServiceWorker] Mise en cache globale');
                return cache.addAll(urlsToCache);
            })
            .then(() => {
                console.log('[ServiceWorker] Installation terminée');
                return self.skipWaiting();
            })
    );
});

self.addEventListener('fetch', event => {
    event.respondWith(
        caches.match(event.request)
            .then(response => {
                // Cache hit - return response
                if (response) {
                    return response;
                }

                return fetch(event.request).then(
                    response => {
                        // Check if we received a valid response
                        if (!response || response.status !== 200 || response.type !== 'basic') {
                            return response;
                        }

                        // Clone the response
                        const responseToCache = response.clone();

                        caches.open(CACHE_NAME)
                            .then(cache => {
                                cache.put(event.request, responseToCache);
                            });

                        return response;
                    }
                );
            })
    );
});

self.addEventListener('activate', event => {
    console.log('[ServiceWorker] Activation...');
    event.waitUntil(
        Promise.all([
            // Nettoyage des anciens caches
            caches.keys().then(cacheNames => {
                return Promise.all(
                    cacheNames.map(cacheName => {
                        if (cacheName !== CACHE_NAME) {
                            return caches.delete(cacheName);
                        }
                    })
                );
            }),
            // Prise de contrôle immédiate
            self.clients.claim()
        ]).then(() => {
            console.log('[ServiceWorker] Activation terminée');
        })
    );
});


self.addEventListener('push', function (event) {
    const data = event.data.json();
    const options = {
        body: data.body,
        icon: '/logo_keplr.png',
    };
    event.waitUntil(self.registration.showNotification(data.title, options));
});
