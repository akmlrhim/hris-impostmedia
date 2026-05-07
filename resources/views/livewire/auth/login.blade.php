<div>
  <form wire:submit="login" class="space-y-4">
    <div>
      <label class="label" for="email">Email</label>
      <input id="email" type="email" wire:model="email" class="input" placeholder="nama@gmail.com" autocomplete="email"
        autofocus>
      @error('email')
        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
      @enderror
    </div>

    <div>
      <label class="label" for="password">Kata Sandi</label>
      <div class="relative" x-data="{ show: false }">
        <input id="password" :type="show ? 'text' : 'password'" wire:model="password" placeholder="********"
          class="input pr-10" autocomplete="current-password">
        <button type="button" @click="show = !show" tabindex="-1"
          class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 hover:text-slate-600"
          :aria-label="show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'">
          <x-icon name="eye" class="w-5 h-5" x-show="!show" />
          <x-icon name="eye-off" class="w-5 h-5" x-show="show" x-cloak />
        </button>
      </div>
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
