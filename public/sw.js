// sw.js

self.addEventListener('install', event => {
    console.log('[ServiceWorker] Installation...');
    // Force l'activation immédiate
    self.skipWaiting();
});

self.addEventListener('activate', event => {
    console.log('[ServiceWorker] Activation...');
    // Prend le contrôle des pages ouvertes
    self.clients.claim();
    console.log('[ServiceWorker] Prêt à gérer les fetch');
});

self.addEventListener('push', function (event) {
    console.log('[ServiceWorker] Réception d\'une notification push');
    const data = event.data.json();
    const options = {
        body: data.body,
        icon: '/logo_keplr.png',
    };
    event.waitUntil(self.registration.showNotification(data.title, options));
});