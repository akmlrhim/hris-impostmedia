<x-layouts.app :title="$title ?? 'Login'">
  <div
    class="min-h-screen flex items-center justify-center px-4 py-12 bg-gradient-to-br from-brand-50 via-white to-slate-100">
    <div class="w-full max-w-md">
      <div class="text-center mb-8">
        <h1 class="mt-4 text-2xl font-bold text-slate-900">Impost Media</h1>
        <p class="text-sm text-slate-500">Human Resource Information System</p>
      </div>
      <div class="card p-6">
        {{ $slot }}
      </div>
    </div>
  </div>
</x-layouts.app>
