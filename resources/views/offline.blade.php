<x-layouts.app title="Offline">
  <div class="min-h-screen flex items-center justify-center px-6 text-center">
    <div>
      <div class="w-20 h-20 mx-auto rounded-full bg-slate-100 flex items-center justify-center mb-4">
        <svg class="w-10 h-10 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round"
            d="M1 1l22 22M16.72 11.06A10.94 10.94 0 0 1 19 12.55M5 12.55a10.94 10.94 0 0 1 5.17-2.39M10.71 5.05A16 16 0 0 1 22.58 9M1.42 9a15.91 15.91 0 0 1 4.7-2.88M8.53 16.11a6 6 0 0 1 6.95 0M12 20h.01" />
        </svg>
      </div>
      <h1 class="text-2xl font-bold text-slate-900">Tidak ada koneksi</h1>
      <p class="text-slate-500 mt-2">Anda sedang offline. Beberapa fitur mungkin tidak tersedia.</p>
      <button onclick="window.location.reload()" class="btn-primary mt-6">Coba lagi</button>
    </div>
  </div>
</x-layouts.app>
