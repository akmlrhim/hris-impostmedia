<div>
  @if ($showPanelSelector)
    {{-- Pilih panel setelah login berhasil --}}
    <div class="space-y-3">
      <div class="text-center mb-5">
        <div class="w-12 h-12 rounded-full bg-emerald-100 flex items-center justify-center mx-auto mb-3">
          <x-icon name="check" class="w-6 h-6 text-emerald-600" />
        </div>
        <p class="font-semibold text-slate-900">Login berhasil!</p>
        <p class="text-sm text-slate-500 mt-1">Anda punya akses ke lebih dari satu panel.<br>Pilih yang ingin dibuka.</p>
      </div>

      <button wire:click="choosePanel('admin')"
        class="w-full flex items-center gap-4 p-4 rounded-xl border-2 border-slate-200 hover:border-brand-500 hover:bg-brand-50 transition group text-left">
        <div>
          <p class="font-semibold text-slate-900 text-sm">Panel Admin</p>
          <p class="text-xs text-slate-400">Kelola karyawan, payroll, absensi</p>
        </div>
        <x-icon name="chevron-right" class="w-4 h-4 text-slate-300 group-hover:text-brand-400 ml-auto" />
      </button>

      <button wire:click="choosePanel('employee')"
        class="w-full flex items-center gap-4 p-4 rounded-xl border-2 border-slate-200 hover:border-emerald-500 hover:bg-emerald-50 transition group text-left">
        <div>
          <p class="font-semibold text-slate-900 text-sm">Mode Karyawan</p>
          <p class="text-xs text-slate-400">Absensi, slip gaji, profil</p>
        </div>
        <x-icon name="chevron-right" class="w-4 h-4 text-slate-300 group-hover:text-emerald-400 ml-auto" />
      </button>
    </div>
  @else
    <form wire:submit="login" class="space-y-4">
      <div>
        <label class="label" for="email">Email</label>
        <input id="email" type="email" wire:model="email" class="input" placeholder="Masukkan alamat email"
          autocomplete="email" autofocus>
        @error('email')
          <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
        @enderror
      </div>

      <div>
        <label class="label" for="password">Kata Sandi</label>
        <x-password-input name="password" />
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
  @endif
</div>
