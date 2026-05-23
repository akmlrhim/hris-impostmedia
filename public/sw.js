const CACHE = 'hris-im-v7';

const PRECACHE = [
	'/offline',
	'/manifest.json',
	'/icons/icon-192.png',
	'/icons/icon-512.png',
];

const STATIC_EXT = /\.(css|js|woff2?|ttf|otf|png|jpg|jpeg|webp|svg|ico)$/i;

// Safely put a response in cache — clone first, catch errors silently
function tryCachePut(request, response) {
	if (!response || response.status === 0) return;
	try {
		const clone = response.clone();
		caches.open(CACHE).then((c) => c.put(request, clone).catch(() => {}));
	} catch (_) {}
}

self.addEventListener('install', (e) => {
	e.waitUntil(
		caches.open(CACHE).then((c) => c.addAll(PRECACHE).catch(() => {}))
	);
	self.skipWaiting();
});

self.addEventListener('activate', (e) => {
	e.waitUntil(
		// Hapus SEMUA cache lama tanpa terkecuali
		caches.keys().then((keys) =>
			Promise.all(keys.map((k) => caches.delete(k)))
		).then(() => self.clients.claim())
	);
});

self.addEventListener('fetch', (e) => {
	const { request } = e;
	if (request.method !== 'GET') return;

	const url = new URL(request.url);

	// Never intercept Livewire requests (Livewire 4 uses randomized prefix like /livewire-XXXX/)
	if (/^\/livewire(-[a-z0-9]+)?\//i.test(url.pathname)) return;

	// Static assets (same origin) — cache first, then network
	if (STATIC_EXT.test(url.pathname) && url.origin === self.location.origin) {
		e.respondWith(
			caches.match(request).then((cached) => {
				if (cached) return cached;
				return fetch(request).then((res) => {
					if (res && res.ok) tryCachePut(request, res);
					return res;
				}).catch(() => new Response('', { status: 503 }));
			})
		);
		return;
	}

	// Navigation — network first, fall back to offline page
	if (request.mode === 'navigate') {
		e.respondWith(
			fetch(request).catch(() =>
				caches.match('/offline').then((r) => r || new Response('Offline', { status: 503 }))
			)
		);
		return;
	}

	// Other same-origin requests — network first, cache as fallback
	if (url.origin === self.location.origin) {
		e.respondWith(
			fetch(request)
				.then((res) => {
					if (res && res.ok) tryCachePut(request, res);
					return res;
				})
				.catch(() =>
					caches.match(request).then((r) => r || new Response('', { status: 503 }))
				)
		);
	}
});
