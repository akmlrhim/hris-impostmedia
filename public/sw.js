const CACHE_NAME = 'hris-im-v1';
const PRECACHE_URLS = [
	'/m',
	'/offline',
	'/manifest.json',
];

self.addEventListener('install', (event) => {
	event.waitUntil(
		caches.open(CACHE_NAME).then((cache) => cache.addAll(PRECACHE_URLS).catch(() => null))
	);
	self.skipWaiting();
});

self.addEventListener('activate', (event) => {
	event.waitUntil(
		caches.keys().then((keys) =>
			Promise.all(keys.filter((k) => k !== CACHE_NAME).map((k) => caches.delete(k)))
		)
	);
	self.clients.claim();
});

self.addEventListener('fetch', (event) => {
	const { request } = event;

	if (request.method !== 'GET') return;

	const url = new URL(request.url);
	if (url.pathname.startsWith('/livewire/') || url.pathname.startsWith('/admin')) return;

	if (request.mode === 'navigate') {
		event.respondWith(
			fetch(request).catch(() => caches.match('/offline'))
		);
		return;
	}

	event.respondWith(
		caches.match(request).then((cached) => {
			return (
				cached ||
				fetch(request)
					.then((response) => {
						if (response.ok && url.origin === self.location.origin) {
							const clone = response.clone();
							caches.open(CACHE_NAME).then((cache) => cache.put(request, clone));
						}
						return response;
					})
					.catch(() => caches.match('/offline'))
			);
		})
	);
});
