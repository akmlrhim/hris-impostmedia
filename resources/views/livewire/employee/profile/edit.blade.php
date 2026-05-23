<div>
  <div class="px-5 pt-6 pb-4 bg-white border-b border-slate-200 flex items-center gap-3 sticky top-0 z-10">
    <a wire:navigate href="{{ route('mobile.profile') }}" class="p-2 -ml-2 rounded-lg hover:bg-slate-100">
      <x-icon name="arrow-left" class="w-5 h-5" />
    </a>
    <h1 class="text-lg font-bold text-slate-900">Edit Profil</h1>
  </div>

  <div class="p-4 space-y-4 pb-32">
    {{-- Profile form --}}
    <form wire:submit="saveProfile" class="space-y-4">

      {{-- Avatar (auto-save) --}}
      <div class="card p-5" x-data="{ preview: null }">
        <div class="flex items-center gap-4">
          <div class="w-20 h-20 rounded-full bg-slate-200 overflow-hidden flex items-center justify-center text-2xl font-bold text-slate-500 shrink-0 relative">
            <template x-if="preview">
              <img :src="preview" class="w-full h-full object-cover">
            </template>
            <template x-if="!preview">
              @if ($employee?->avatar_path)
                <img src="{{ route('files.avatar', $employee) }}" class="w-full h-full object-cover">
              @else
                <span>{{ strtoupper(substr($full_name ?: $name ?: '?', 0, 1)) }}</span>
              @endif
            </template>
            <div wire:loading wire:target="avatar"
              class="absolute inset-0 bg-black/40 flex items-center justify-center rounded-full">
              <svg class="w-5 h-5 text-white animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
              </svg>
            </div>
          </div>
          <div class="flex-1">
            <p class="text-sm font-medium text-slate-900 mb-1">Foto Profil</p>
            <label class="block w-full px-3 py-2 rounded-lg border border-slate-300 text-xs text-slate-600 bg-white cursor-pointer hover:bg-slate-50 transition text-center">
              <span wire:loading.remove wire:target="avatar">Pilih foto baru</span>
              <span wire:loading wire:target="avatar">Menyimpan…</span>
              <input type="file" wire:model="avatar" accept="image/*" class="hidden"
                @change="
                  const f = $event.target.files[0];
                  if (f) { const r = new FileReader(); r.onload = e => preview = e.target.result; r.readAsDataURL(f); }
                ">
            </label>
            <p class="text-[11px] text-slate-400 mt-1">JPG/PNG, max 2MB. Tersimpan otomatis.</p>
            @error('avatar') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
          </div>
        </div>
      </div>

      {{-- Akun --}}
      <div class="card p-5 space-y-4">
        <h3 class="text-sm font-semibold text-slate-900 -mb-1">Akun</h3>

        <div>
          <label class="label">Nama Tampilan</label>
          <input wire:model="name" class="input" placeholder="Masukkan nama tampilan">
          @error('name')
            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
          @enderror
        </div>

        <div>
          <label class="label">Email</label>
          <input type="email" wire:model="email" class="input" inputmode="email" placeholder="Masukkan alamat email">
          @error('email')
            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
          @enderror
        </div>

        <div>
          <label class="label">Nama Lengkap</label>
          <input wire:model="full_name" class="input" placeholder="Masukkan nama lengkap">
          @error('full_name')
            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
          @enderror
        </div>

        <div>
          <label class="label">Nama Panggilan</label>
          <input wire:model="nickname" class="input" placeholder="Masukkan nama panggilan">
        </div>

        <div>
          <label class="label">Telepon</label>
          <input wire:model="phone" class="input" inputmode="tel" placeholder="Masukkan nomor telepon">
        </div>

        <div>
          <label class="label">NIK <span class="text-red-500">*</span></label>
          <input wire:model="nik" class="input" inputmode="numeric" maxlength="20"
            placeholder="Masukkan NIK (KTP)">
          @error('nik')
            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
          @enderror
        </div>
      </div>

      {{-- Data pribadi --}}
      <div class="card p-5 space-y-4">
        <h3 class="text-sm font-semibold text-slate-900 -mb-1">Data Pribadi</h3>

        <div>
          <label class="label">Jenis Kelamin</label>
          <select wire:model="gender" class="input">
            <option value="">- Pilih -</option>
            <option value="male">Laki-laki</option>
            <option value="female">Perempuan</option>
          </select>
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="label">Tanggal Lahir</label>
            <input type="date" onclick="this.showPicker()" wire:model="date_of_birth" class="input">
            @error('date_of_birth')
              <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
            @enderror
          </div>
          <div>
            <label class="label">Tempat Lahir</label>
            <input wire:model="place_of_birth" class="input" placeholder="Masukkan tempat lahir">
          </div>
        </div>

        <div>
          <label class="label">Alamat</label>
          <textarea wire:model="address" rows="2" class="input" placeholder="Masukkan alamat lengkap"></textarea>
          @error('address')
            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
          @enderror
        </div>
      </div>

      {{-- Pendidikan --}}
      <div class="card p-5 space-y-4">
        <h3 class="text-sm font-semibold text-slate-900 -mb-1">Pendidikan</h3>

        <div>
          <label class="label">Pendidikan Terakhir <span class="text-red-500">*</span></label>
          <select wire:model="last_education" class="input">
            <option value="">- Pilih -</option>
            <option value="SD">SD</option>
            <option value="SMP">SMP</option>
            <option value="SMA">SMA</option>
            <option value="SMK">SMK</option>
            <option value="D3">D3</option>
            <option value="D4">D4</option>
            <option value="S1">S1</option>
            <option value="S2">S2</option>
            <option value="S3">S3</option>
          </select>
          @error('last_education')
            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
          @enderror
        </div>

        <div>
          <label class="label">Jurusan / Sekolah - Universitas <span class="text-red-500">*</span></label>
          <input wire:model="major_school_university" class="input"
            placeholder="Mis. Teknik Informatika - Universitas Indonesia">
          @error('major_school_university')
            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
          @enderror
        </div>
      </div>

      {{-- Kontak Darurat --}}
      <div class="card p-5 space-y-4">
        <h3 class="text-sm font-semibold text-slate-900 -mb-1">Kontak Darurat</h3>

        <div>
          <label class="label">Nama Kontak Darurat <span class="text-red-500">*</span></label>
          <input wire:model="emergency_contact_name" class="input"
            placeholder="Mis. Nama orang tua / pasangan">
          @error('emergency_contact_name')
            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
          @enderror
        </div>

        <div>
          <label class="label">No. Telepon Kontak Darurat <span class="text-red-500">*</span></label>
          <input wire:model="emergency_contact_number" class="input" inputmode="tel"
            placeholder="Masukkan nomor telepon">
          @error('emergency_contact_number')
            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
          @enderror
        </div>
      </div>

      {{-- Bank --}}
      <div class="card p-5 space-y-4">
        <h3 class="text-sm font-semibold text-slate-900 -mb-1">Rekening Bank</h3>

        <div>
          <label class="label">Bank</label>
          <input wire:model="bank_name" class="input" placeholder="Masukkan nama bank">
        </div>

        <div>
          <label class="label">No. Rekening</label>
          <input wire:model="bank_account_number" class="input" inputmode="numeric" placeholder="Masukkan nomor rekening">
        </div>

        <div>
          <label class="label">Atas Nama</label>
          <input wire:model="bank_account_holder" class="input" placeholder="Masukkan nama pemilik rekening">
        </div>
      </div>

      <button type="submit" class="btn-primary w-full" wire:loading.attr="disabled" wire:target="saveProfile">
        <span wire:loading.remove wire:target="saveProfile">Simpan Profil</span>
        <span wire:loading wire:target="saveProfile">Menyimpan…</span>
      </button>
    </form>

    {{-- Link ke halaman wajah --}}
    @php $faceEnrolled = ! empty($employee?->face_descriptor); @endphp
    <a wire:navigate href="{{ route('mobile.profile.face') }}"
      class="card p-4 flex items-center justify-between gap-3 active:scale-[0.99] transition">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl {{ $faceEnrolled ? 'bg-emerald-100 text-emerald-600' : 'bg-rose-50 text-rose-500' }} flex items-center justify-center shrink-0">
          <x-icon name="scan-face" class="w-5 h-5" />
        </div>
        <div>
          <p class="text-sm font-medium text-slate-900">Data Wajah</p>
          <p class="text-xs {{ $faceEnrolled ? 'text-emerald-600' : 'text-rose-500' }}">
            {{ $faceEnrolled ? 'Sudah terdaftar' : 'Belum terdaftar - tap untuk mendaftar' }}
          </p>
        </div>
      </div>
      <x-icon name="chevron-right" class="w-4 h-4 text-slate-400 shrink-0" />
    </a>

    {{-- Password --}}
    <form wire:submit="changePassword" class="card p-5 space-y-4">
      <h3 class="text-sm font-semibold text-slate-900 -mb-1">Ubah Kata Sandi</h3>

      <x-password-input name="current_password" label="Kata Sandi Saat Ini" />
      <x-password-input name="new_password" label="Kata Sandi Baru" autocomplete="new-password" />
      <x-password-input name="new_password_confirmation" label="Konfirmasi Kata Sandi Baru" autocomplete="new-password" />

      <button type="submit" class="btn-primary w-full" wire:loading.attr="disabled" wire:target="changePassword">
        <span wire:loading.remove wire:target="changePassword">Ubah Kata Sandi</span>
        <span wire:loading wire:target="changePassword">Memproses…</span>
      </button>
    </form>
  </div>
</div>
