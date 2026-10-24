const CACHE_NAME = 'house-of-cards-v1';
const ASSETS = [
  './jogo.php',
  './jogo.css',
  './jogo-swipe.js',
  './manifest.json'
];

self.addEventListener('install', (e) => {
  e.waitUntil(
    caches.open(CACHE_NAME).then((cache) => cache.addAll(ASSETS))
  );
});

self.addEventListener('fetch', (e) => {
  e.respondWith(
    caches.match(e.request).then((response) => response || fetch(e.request))
  );
});