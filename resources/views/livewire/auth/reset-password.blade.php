<div>
  <form wire:submit="resetPassword" class="space-y-4">
    <div class="text-center mb-2">
      <p class="text-sm text-slate-500">Buat kata sandi baru untuk akun Anda.</p>
    </div>

    <div>
      <label class="label" for="email">Email</label>
      <input id="email" type="email" wire:model="email" class="input" readonly autocomplete="email">
      @error('email')
        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
      @enderror
    </div>

    <div>
      <label class="label" for="password">Kata Sandi Baru</label>
      <x-password-input name="password" />
      <p class="text-xs text-slate-400 mt-1">Minimal 8 karakter</p>
    </div>

    <div>
      <label class="label" for="password_confirmation">Konfirmasi Kata Sandi</label>
      <x-password-input name="password_confirmation" />
    </div>

    <button type="submit" class="btn-primary w-full" wire:loading.attr="disabled">
      <span wire:loading.remove wire:target="resetPassword">Simpan Kata Sandi Baru</span>
      <span wire:loading wire:target="resetPassword">Menyimpan…</span>
    </button>
  </form>
</div>
