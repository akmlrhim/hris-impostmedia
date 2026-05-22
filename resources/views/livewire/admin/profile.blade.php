<div class="space-y-6 max-w-3xl">
  <div>
    <h2 class="text-base font-semibold text-slate-900">Profil Saya</h2>
    <p class="text-sm text-slate-500">Kelola informasi akun & kata sandi Anda.</p>
  </div>

  {{-- Account info --}}
  <form wire:submit="saveProfile" class="card p-5 sm:p-6 space-y-4">
    <div class="flex items-center gap-4">
      <div class="w-14 h-14 rounded-full bg-slate-900 text-white flex items-center justify-center text-xl font-bold">
        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
      </div>
      <div>
        <p class="font-semibold text-slate-900">{{ auth()->user()->name }}</p>
        <p class="text-xs text-slate-500">{{ auth()->user()->role?->label() }}</p>
      </div>
    </div>

    <div class="pt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label class="label">Nama Lengkap</label>
        <input wire:model="name" class="input" placeholder="Masukkan nama lengkap">
        @error('name')
          <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
        @enderror
      </div>
      <div>
        <label class="label">Email</label>
        <input type="email" wire:model="email" class="input" placeholder="Masukkan alamat email">
        @error('email')
          <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
        @enderror
      </div>
    </div>

    <div class="flex justify-end">
      <button type="submit" class="btn-primary" wire:loading.attr="disabled" wire:target="saveProfile">
        <span wire:loading.remove wire:target="saveProfile">Simpan Perubahan</span>
        <span wire:loading wire:target="saveProfile">Menyimpan…</span>
      </button>
    </div>
  </form>

  {{-- Password --}}
  <form wire:submit="changePassword" class="card p-5 sm:p-6 space-y-4">
    <div>
      <h3 class="text-sm font-semibold text-slate-900">Ubah Kata Sandi</h3>
      <p class="text-xs text-slate-500 mt-0.5">Minimal 8 karakter.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div class="sm:col-span-2">
        <x-password-input name="current_password" label="Kata Sandi Saat Ini" />
      </div>
      <x-password-input name="new_password" label="Kata Sandi Baru" autocomplete="new-password" />
      <x-password-input name="new_password_confirmation" label="Konfirmasi Kata Sandi Baru" autocomplete="new-password" />
    </div>

    <div class="flex justify-end">
      <button type="submit" class="btn-primary" wire:loading.attr="disabled" wire:target="changePassword">
        <span wire:loading.remove wire:target="changePassword">Ubah Kata Sandi</span>
        <span wire:loading wire:target="changePassword">Memproses…</span>
      </button>
    </div>
  </form>
</div>
