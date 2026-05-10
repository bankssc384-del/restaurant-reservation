/**
 * Service Worker — Réservation Restaurant PWA
 * Le placeholder __SLUG__ est remplacé par PHP au moment de servir.
 */

const SLUG       = '__SLUG__';
const CACHE_NAME = 'rr-pwa-v1-' + SLUG;
const APP_SHELL  = [
  '/' + SLUG,
  '/' + SLUG + '/manifest.json',
  '/' + SLUG + '/icon-192.png',
  '/' + SLUG + '/icon-512.png',
];

self.addEventListener('install', event => {
  self.skipWaiting();
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => cache.addAll(APP_SHELL).catch(() => {}))
  );
});

self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(keys =>
      Promise.all(keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k)))
    ).then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', event => {
  const url = new URL(event.request.url);

  // API : toujours réseau, jamais de cache
  if (url.pathname.includes('/wp-json/rr/')) return;

  // App shell : cache-first
  if (url.pathname.startsWith('/' + SLUG)) {
    event.respondWith(
      caches.match(event.request).then(cached => {
        return cached || fetch(event.request).then(response => {
          if (response.ok) {
            const clone = response.clone();
            caches.open(CACHE_NAME).then(cache => cache.put(event.request, clone));
          }
          return response;
        }).catch(() => cached);
      })
    );
  }
});
