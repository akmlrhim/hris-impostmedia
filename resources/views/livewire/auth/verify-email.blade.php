<div class="text-center space-y-4">
  <div class="w-12 h-12 rounded-full bg-amber-100 flex items-center justify-center mx-auto">
    <x-icon name="mail" class="w-6 h-6 text-amber-600" />
  </div>

  <div>
    <p class="font-semibold text-slate-900">Verifikasi Email Anda</p>
    <p class="text-sm text-slate-500 mt-1">
      Kami telah mengirimkan tautan verifikasi ke
      <strong>{{ auth()->user()?->email }}</strong>.
      Buka email dan klik tautan tersebut untuk mengaktifkan akun Anda.
    </p>
  </div>

  @if ($sent)
    <div class="p-3 rounded-lg bg-emerald-50 text-emerald-700 text-sm">
      Email verifikasi berhasil dikirim ulang. Periksa inbox Anda.
    </div>
  @endif

  <div class="space-y-2 pt-2">
    <button wire:click="resend" wire:loading.attr="disabled"
      class="btn-primary w-full">
      <span wire:loading.remove wire:target="resend">Kirim Ulang Email Verifikasi</span>
      <span wire:loading wire:target="resend">Mengirim…</span>
    </button>

    <form method="POST" action="{{ route('logout') }}">
      @csrf
      <button type="submit" class="btn-secondary w-full">Keluar</button>
    </form>
  </div>
</div>
