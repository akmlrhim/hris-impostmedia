<div>
  <h2 class="text-xl font-semibold text-slate-900 mb-1">Masuk ke akun Anda</h2>
  <p class="text-sm text-slate-500 mb-6">Gunakan email dan kata sandi yang diberikan HR.</p>

  <form wire:submit="login" class="space-y-4">
    <div>
      <label class="label" for="email">Email</label>
      <input id="email" type="email" wire:model="email" class="input" autocomplete="email" autofocus>
      @error('email')
        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
      @enderror
    </div>

    <div>
      <label class="label" for="password">Kata Sandi</label>
      <input id="password" type="password" wire:model="password" class="input" autocomplete="current-password">
      @error('password')
        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
      @enderror
    </div>

    <label class="flex items-center gap-2 text-sm text-slate-600">
      <input type="checkbox" wire:model="remember" class="rounded border-slate-300">
      Ingat saya
    </label>

    <button type="submit" class="btn-primary w-full" wire:loading.attr="disabled">
      <span wire:loading.remove wire:target="login">Masuk</span>
      <span wire:loading wire:target="login">Memproses…</span>
    </button>
  </form>
</div>
