<div>
    <div class="px-5 pt-6 pb-4 bg-white border-b border-slate-200 flex items-center gap-3 sticky top-0 z-10">
        <a href="{{ route('mobile.profile') }}" class="p-2 -ml-2 rounded-lg hover:bg-slate-100">
            <x-icon name="arrow-left" class="w-5 h-5" />
        </a>
        <h1 class="text-lg font-bold text-slate-900">Edit Profil</h1>
    </div>

    <div class="p-4 space-y-4">
        {{-- Personal Info --}}
        <form wire:submit="saveProfile" class="card p-5 space-y-4">
            <h3 class="text-sm font-semibold text-slate-900 -mb-1">Informasi Pribadi</h3>

            <div>
                <label class="label">Nama</label>
                <input wire:model="name" class="input">
                @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="label">Email</label>
                <input type="email" wire:model="email" class="input">
                @error('email') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="label">Nama Panggilan</label>
                <input wire:model="nickname" class="input">
            </div>

            <div>
                <label class="label">Telepon</label>
                <input wire:model="phone" class="input" inputmode="tel">
            </div>

            <h3 class="text-sm font-semibold text-slate-900 pt-2 -mb-1">Alamat</h3>

            <div>
                <label class="label">Alamat Lengkap</label>
                <textarea wire:model="address" rows="2" class="input"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="label">Kota</label>
                    <input wire:model="city" class="input">
                </div>
                <div>
                    <label class="label">Kode Pos</label>
                    <input wire:model="postal_code" class="input" inputmode="numeric">
                </div>
            </div>

            <div>
                <label class="label">Provinsi</label>
                <input wire:model="province" class="input">
            </div>

            <h3 class="text-sm font-semibold text-slate-900 pt-2 -mb-1">Kontak Darurat</h3>

            <div>
                <label class="label">Nama</label>
                <input wire:model="emergency_contact_name" class="input">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="label">Telepon</label>
                    <input wire:model="emergency_contact_phone" class="input" inputmode="tel">
                </div>
                <div>
                    <label class="label">Hubungan</label>
                    <input wire:model="emergency_contact_relation" class="input" placeholder="Orangtua / Pasangan">
                </div>
            </div>

            <h3 class="text-sm font-semibold text-slate-900 pt-2 -mb-1">Rekening Bank</h3>

            <div>
                <label class="label">Bank</label>
                <input wire:model="bank_name" class="input" placeholder="BCA / Mandiri">
            </div>

            <div>
                <label class="label">No. Rekening</label>
                <input wire:model="bank_account_number" class="input" inputmode="numeric">
            </div>

            <div>
                <label class="label">Atas Nama</label>
                <input wire:model="bank_account_holder" class="input">
            </div>

            <button type="submit" class="btn-primary w-full" wire:loading.attr="disabled" wire:target="saveProfile">
                <span wire:loading.remove wire:target="saveProfile">Simpan Profil</span>
                <span wire:loading wire:target="saveProfile">Menyimpan…</span>
            </button>
        </form>

        {{-- Password --}}
        <form wire:submit="changePassword" class="card p-5 space-y-4">
            <h3 class="text-sm font-semibold text-slate-900 -mb-1">Ubah Kata Sandi</h3>

            <div>
                <label class="label">Kata Sandi Saat Ini</label>
                <input type="password" wire:model="current_password" class="input" autocomplete="current-password">
                @error('current_password') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="label">Kata Sandi Baru</label>
                <input type="password" wire:model="new_password" class="input" autocomplete="new-password">
                @error('new_password') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="label">Konfirmasi Kata Sandi Baru</label>
                <input type="password" wire:model="new_password_confirmation" class="input" autocomplete="new-password">
            </div>

            <button type="submit" class="btn-primary w-full" wire:loading.attr="disabled" wire:target="changePassword">
                <span wire:loading.remove wire:target="changePassword">Ubah Kata Sandi</span>
                <span wire:loading wire:target="changePassword">Memproses…</span>
            </button>
        </form>
    </div>
</div>
