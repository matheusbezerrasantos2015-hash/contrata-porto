const CACHE_NAME = 'contrata-porto-v3';
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
  e.waitUntil(
    caches.open(CACHE_NAME)
      .then(c => c.addAll(ASSETS))
  );
  self.skipWaiting();
});

self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(keys =>
      Promise.all(
        keys
          .filter(key => key !== CACHE_NAME)
          .map(key => caches.delete(key))
      )
    )
  );
  self.clients.claim();
});

self.addEventListener('fetch', event => {
  const url = new URL(event.request.url);
  
  // Arquivos JS e CSS: sempre buscar da rede primeiro
  if (url.pathname.endsWith('.js') || url.pathname.endsWith('.css')) {
    event.respondWith(
      fetch(event.request)
        .then(response => {
          const clone = response.clone();
          caches.open(CACHE_NAME).then(cache => cache.put(event.request, clone));
          return response;
        })
        .catch(() => caches.match(event.request))
    );
    return;
  }

  // Demais recursos: cache first
  event.respondWith(
    caches.match(event.request).then(cached => cached || fetch(event.request))
  );
});
