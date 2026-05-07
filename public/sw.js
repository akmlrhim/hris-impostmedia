const CACHE = 'hris-im-v2';

const PRECACHE = [
	'/offline',
	'/manifest.json',
	'/icons/icon-192.png',
	'/icons/icon-512.png',
];

// Static asset extensions to cache aggressively
const STATIC_EXT = /\.(css|js|woff2?|ttf|otf|png|jpg|jpeg|webp|svg|ico)$/i;

self.addEventListener('install', (e) => {
	e.waitUntil(
		caches.open(CACHE).then((c) => c.addAll(PRECACHE).catch(() => null))
	);
	self.skipWaiting();
});

self.addEventListener('activate', (e) => {
	e.waitUntil(
		caches.keys().then((keys) =>
			Promise.all(keys.filter((k) => k !== CACHE).map((k) => caches.delete(k)))
		)
	);
	self.clients.claim();
});

self.addEventListener('fetch', (e) => {
	const { request } = e;
	if (request.method !== 'GET') return;

	const url = new URL(request.url);

	// Never intercept Livewire internal requests
	if (url.pathname.startsWith('/livewire/')) return;

	// Static assets — cache first, then network
	if (STATIC_EXT.test(url.pathname) && url.origin === self.location.origin) {
		e.respondWith(
			caches.match(request).then(
				(cached) =>
					cached ||
					fetch(request).then((res) => {
						if (res.ok) {
							caches.open(CACHE).then((c) => c.put(request, res.clone()));
						}
						return res;
					})
			)
		);
		return;
	}

	// Navigation — network first, fall back to offline page
	if (request.mode === 'navigate') {
		e.respondWith(
			fetch(request).catch(() => caches.match('/offline'))
		);
		return;
	}

	// Everything else — network first, cache as fallback
	e.respondWith(
		fetch(request)
			.then((res) => {
				if (res.ok && url.origin === self.location.origin) {
					caches.open(CACHE).then((c) => c.put(request, res.clone()));
				}
				return res;
			})
			.catch(() => caches.match(request))
	);
});
