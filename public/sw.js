const CACHE = 'hris-im-v8';

const PRECACHE = [
	'/offline',
	'/manifest.json',
	'/icons/icon-192.png',
	'/icons/icon-512.png',
];

// Aset ber-hash / statis yang isinya tidak pernah berubah untuk URL yang sama.
// Hanya ini yang boleh cache-first.
const IMMUTABLE = /^\/(build\/|icons\/|favicon\.ico|logo\.webp|face-models\/)/i;
const IMMUTABLE_EXT = /\.(woff2?|ttf|otf|png|jpg|jpeg|webp|svg|ico)$/i;

const STATIC_EXT = /\.(css|js|woff2?|ttf|otf|png|jpg|jpeg|webp|svg|ico)$/i;

// Endpoint milik Vite dev server bila kebetulan dilayani dari origin yang sama.
const DEV_PATH = /^\/(@vite|@id|@fs|resources\/|node_modules\/|__vite)/i;

// Safely put a response in cache — clone first, catch errors silently
function tryCachePut(request, response) {
	if (!response || response.status === 0) return;
	try {
		const clone = response.clone();
		caches.open(CACHE).then((c) => c.put(request, clone).catch(() => {}));
	} catch (_) {}
}

function isImmutable(url) {
	return IMMUTABLE.test(url.pathname) || IMMUTABLE_EXT.test(url.pathname);
}

self.addEventListener('install', (e) => {
	e.waitUntil(
		caches.open(CACHE).then((c) => c.addAll(PRECACHE).catch(() => {}))
	);
	self.skipWaiting();
});

self.addEventListener('activate', (e) => {
	e.waitUntil(
		// Hapus hanya cache versi lama, pertahankan cache aktif
		caches.keys().then((keys) =>
			Promise.all(keys.filter((k) => k !== CACHE).map((k) => caches.delete(k)))
		).then(() => self.clients.claim())
	);
});

self.addEventListener('fetch', (e) => {
	const { request } = e;
	if (request.method !== 'GET') return;

	const url = new URL(request.url);

	// Never intercept Livewire requests (Livewire 4 uses randomized prefix like /livewire-XXXX/)
	if (/^\/livewire(-[a-z0-9]+)?\//i.test(url.pathname)) return;

	// Jangan sentuh apa pun yang berasal dari Vite dev server
	if (DEV_PATH.test(url.pathname)) return;

	// Permintaan yang sengaja bypass cache (reload paksa, cache-buster) diteruskan apa adanya
	if (request.cache === 'no-store' || request.cache === 'reload') return;

	// Aset ber-hash / statis (same origin) — cache first, then network
	if (url.origin === self.location.origin && isImmutable(url)) {
		e.respondWith(
			caches.match(request).then((cached) => {
				if (cached) return cached;
				return fetch(request).then((res) => {
					if (res && res.ok) tryCachePut(request, res);
					return res;
				});
			})
		);
		return;
	}

	// CSS/JS non-hash — network first supaya perubahan langsung terlihat,
	// cache hanya dipakai saat offline.
	if (url.origin === self.location.origin && STATIC_EXT.test(url.pathname)) {
		e.respondWith(
			fetch(request)
				.then((res) => {
					if (res && res.ok) tryCachePut(request, res);
					return res;
				})
				.catch(() =>
					caches.match(request).then((r) => {
						if (r) return r;
						throw new Error('offline');
					})
				)
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
