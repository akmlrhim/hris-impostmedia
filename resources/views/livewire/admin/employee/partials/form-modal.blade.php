<x-modal show="showForm" max-width="4xl" :title="$editingId ? 'Ubah Karyawan' : 'Tambah Karyawan'">
  <form wire:submit="save" class="space-y-5" wire:key="employee-form-{{ $editingId ?? 'new' }}">

    {{-- Avatar --}}
    <div class="flex items-center gap-4">
      <div
        class="w-20 h-20 rounded-full bg-slate-200 overflow-hidden flex items-center justify-center text-2xl font-bold text-slate-500 shrink-0">
        @if ($avatar)
          <img src="{{ $avatar->temporaryUrl() }}" class="w-full h-full object-cover">
        @elseif ($editingId && $existing_avatar_path)
          <img src="{{ route('files.avatar', $editingId) }}" class="w-full h-full object-cover">
        @else
          {{ strtoupper(substr($full_name ?: '?', 0, 1)) }}
        @endif
      </div>
      <div class="flex-1">
        <label class="label">Foto Profil</label>
        <input type="file" wire:model="avatar" accept="image/*"
          class="block w-full text-sm text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
        <p class="text-xs text-slate-500 mt-1">Format: JPG/PNG, max 2MB.</p>
        <div wire:loading wire:target="avatar" class="text-xs text-slate-500 mt-1">Mengunggah…</div>
        @error('avatar')
          <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
        @enderror
      </div>
    </div>

    {{-- Akun & Identitas --}}
    <div class="pt-4 border-t border-slate-100">
      <h4 class="text-sm font-semibold text-slate-900 mb-2">Akun & Identitas</h4>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="label">Nomor Karyawan <span class="text-red-500">*</span></label>
          <input wire:model="employee_number" class="input" placeholder="Mis. EMP-0001">
          @error('employee_number')
            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
          @enderror
        </div>
        <div>
          <label class="label">NIK <span class="text-red-500">*</span></label>
          <input wire:model="nik" class="input" placeholder="Masukkan NIK (KTP)" maxlength="20" inputmode="numeric">
          @error('nik')
            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
          @enderror
        </div>
        <div>
          <label class="label">Nama Lengkap <span class="text-red-500">*</span></label>
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
          <label class="label">Email <span class="text-red-500">*</span></label>
          <input type="email" wire:model="email" class="input" placeholder="Masukkan alamat email">
          @error('email')
            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
          @enderror
        </div>
        <div>
          <label class="label">Telepon</label>
          <input wire:model="phone" class="input" placeholder="Masukkan nomor telepon" inputmode="tel">
        </div>
      </div>
    </div>

    {{-- Data Pribadi --}}
    <div class="pt-4 border-t border-slate-100">
      <h4 class="text-sm font-semibold text-slate-900 mb-2">Data Pribadi</h4>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <div>
          <label class="label">Jenis Kelamin <span class="text-red-500">*</span></label>
          <select wire:model="gender" class="input">
            <option value="male">Laki-laki</option>
            <option value="female">Perempuan</option>
          </select>
        </div>
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
        <div class="sm:col-span-2 lg:col-span-3">
          <label class="label">Alamat</label>
          <textarea wire:model="address" rows="2" class="input" placeholder="Masukkan alamat lengkap"></textarea>
        </div>
      </div>
    </div>

    {{-- Pendidikan --}}
    <div class="pt-4 border-t border-slate-100">
      <h4 class="text-sm font-semibold text-slate-900 mb-2">Pendidikan</h4>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
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
    </div>

    {{-- Penempatan --}}
    <div class="pt-4 border-t border-slate-100">
      <h4 class="text-sm font-semibold text-slate-900 mb-2">Penempatan</h4>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="label">Tipe Kerja <span class="text-red-500">*</span></label>
          <select wire:model="work_type" class="input">
            @foreach ($workTypes as $w)
              <option value="{{ $w->value }}">{{ $w->label() }}</option>
            @endforeach
          </select>
          @error('work_type')
            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
          @enderror
        </div>
        <div></div>
        <div>
          <label class="label">Mulai Kontrak</label>
          <input type="date" onclick="this.showPicker()" wire:model="contract_start_date" class="input">
          @error('contract_start_date')
            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
          @enderror
        </div>
        <div>
          <label class="label">Akhir Kontrak</label>
          <input type="date" onclick="this.showPicker()" wire:model="contract_end_date" class="input">
          @error('contract_end_date')
            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
          @enderror
        </div>
        <label class="flex items-center gap-2 mt-1 sm:col-span-2 text-sm">
          <input type="checkbox" wire:model="is_active" class="rounded border-slate-300">
          Karyawan aktif
        </label>
      </div>
    </div>

    {{-- Penggajian & Bank --}}
    <div class="pt-4 border-t border-slate-100">
      <h4 class="text-sm font-semibold text-slate-900 mb-2">Penggajian & Bank</h4>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="sm:col-span-2">
          <label class="label">Gaji Pokok <span class="text-red-500">*</span></label>
          <x-currency-input wire-model="basic_salary" />
          @error('basic_salary')
            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
          @enderror
        </div>
        <div>
          <label class="label">Nama Bank</label>
          <input wire:model="bank_name" class="input" placeholder="Masukkan nama bank">
        </div>
        <div>
          <label class="label">No. Rekening</label>
          <input wire:model="bank_account_number" class="input" placeholder="Masukkan nomor rekening"
            inputmode="numeric">
        </div>
        <div class="sm:col-span-2">
          <label class="label">Atas Nama</label>
          <input wire:model="bank_account_holder" class="input" placeholder="Masukkan nama pemilik rekening">
        </div>
      </div>
    </div>

    {{-- Kontak Darurat --}}
    <div class="pt-4 border-t border-slate-100">
      <h4 class="text-sm font-semibold text-slate-900 mb-2">Kontak Darurat</h4>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="label">Nama Kontak Darurat <span class="text-red-500">*</span></label>
          <input wire:model="emergency_contact_name" class="input" placeholder="Mis. Nama orang tua / pasangan">
          @error('emergency_contact_name')
            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
          @enderror
        </div>
        <div>
          <label class="label">No. Telepon Kontak Darurat <span class="text-red-500">*</span></label>
          <input wire:model="emergency_contact_number" class="input" placeholder="Masukkan nomor telepon"
            inputmode="tel">
          @error('emergency_contact_number')
            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
          @enderror
        </div>
      </div>
    </div>

    <div class="flex flex-wrap gap-2 justify-end pt-4 border-t border-slate-100">
      <button type="button" @click="$wire.set('showForm', false, true)" class="btn-secondary">Batal</button>
      <button type="submit" class="btn-primary" wire:loading.attr="disabled">
        <span wire:loading.remove wire:target="save">Simpan</span>
        <span wire:loading wire:target="save">Menyimpan…</span>
      </button>
    </div>

    @if (!$editingId)
      <p class="text-xs text-slate-500 text-right">
        Password awal otomatis: <code class="bg-slate-100 px-1 rounded">password</code>
      </p>
    @endif
  </form>
</x-modal>
