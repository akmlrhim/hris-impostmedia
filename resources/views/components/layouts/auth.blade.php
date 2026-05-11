<x-layouts.app :title="$title ?? 'Login'">
  <div
    class="min-h-screen flex items-center justify-center px-4 py-12 bg-gradient-to-br from-brand-50 via-white to-slate-100">
    <div class="w-full max-w-md">
      <div class="text-center mb-8">
        <img src="{{ asset('logo.webp') }}" alt="Logo" class="w-16 h-16 rounded-2xl mx-auto object-cover shadow">
        <h1 class="mt-3 text-2xl font-bold text-black">Impost Media</h1>
        <p class="text-sm text-black">Human Resource Information System</p>
      </div>
      <div class="card p-6">
        {{ $slot }}
      </div>
    </div>
  </div>
</x-layouts.app>
