<div>
  @if ($sent)
    <div class="text-center space-y-4">
      <div class="w-12 h-12 rounded-full bg-emerald-100 flex items-center justify-center mx-auto">
        <x-icon name="mail" class="w-6 h-6 text-emerald-600" />
      </div>
      <div>
        <p class="font-semibold text-slate-900">Email terkirim!</p>
        <p class="text-sm text-slate-500 mt-1">
          Tautan reset kata sandi telah dikirim ke <strong>{{ $email }}</strong>.
          Periksa folder spam jika tidak menemukan email tersebut.
        </p>
      </div>
      <a href="{{ route('login') }}" wire:navigate class="text-sm text-brand-600 hover:underline">
        Kembali ke halaman masuk
      </a>
    </div>
  @else
    <form wire:submit="sendLink" class="space-y-4">
      <div class="text-center mb-2">
        <p class="text-sm text-slate-500">Masukkan email Anda dan kami akan mengirimkan tautan untuk reset kata sandi.</p>
      </div>

      <div>
        <label class="label" for="email">Email</label>
        <input id="email" type="email" wire:model.blur="email" class="input @error('email') border-red-400 @enderror" placeholder="Masukkan alamat email" autocomplete="email" autofocus>
        @error('email')
          <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
        @enderror
      </div>

      <button type="submit" class="btn-primary w-full" wire:loading.attr="disabled">
        <span wire:loading.remove wire:target="sendLink">Kirim Tautan Reset</span>
        <span wire:loading wire:target="sendLink">Mengirim…</span>
      </button>

      <div class="text-center">
        <a href="{{ route('login') }}" wire:navigate class="text-sm text-slate-500 hover:text-slate-700">
          Kembali ke halaman masuk
        </a>
      </div>
    </form>
  @endif
</div>
