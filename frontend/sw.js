const CACHE_NAME = 'contrataporto-v1';
const ASSETS = [
  '/ContrataPorto/frontend/pages/index.html',
  '/ContrataPorto/frontend/css/index.css',
  '/ContrataPorto/frontend/js/api.js',
  '/ContrataPorto/frontend/js/auth.js',
  '/ContrataPorto/frontend/js/ui.js',
  '/ContrataPorto/frontend/assets/favicon.png',
  '/ContrataPorto/frontend/assets/textlogo.png'
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
