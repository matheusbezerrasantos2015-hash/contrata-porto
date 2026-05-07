const CACHE_NAME = 'contrataporto-v1';
const ASSETS = [
  '/frontend/pages/index.html',
  '/frontend/css/style.css',
  '/frontend/css/base.css',
  '/frontend/css/components.css',
  '/frontend/css/layout.css',
  '/frontend/css/pages.css',
  '/frontend/js/api.js',
  '/frontend/js/auth.js',
  '/frontend/js/ui.js',
  '/frontend/assets/favicon.png',
  '/frontend/assets/textlogo.png'
];

self.addEventListener('install', e => {
  e.waitUntil(caches.open(CACHE_NAME)
    .then(c => c.addAll(ASSETS)));
});

self.addEventListener('fetch', e => {
  e.respondWith(
    caches.match(e.request)
      .then(r => r || fetch(e.request))
  );
});
