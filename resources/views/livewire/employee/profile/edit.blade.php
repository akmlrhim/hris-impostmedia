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

            {{-- Avatar --}}
            <div class="card p-5">
                <div class="flex items-center gap-4">
                    <div class="w-20 h-20 rounded-full bg-slate-200 overflow-hidden flex items-center justify-center text-2xl font-bold text-slate-500 shrink-0">
                        @if ($avatar)
                            <img src="{{ $avatar->temporaryUrl() }}" class="w-full h-full object-cover">
                        @elseif ($employee?->avatar_path)
                            <img src="{{ route('files.avatar', $employee) }}" class="w-full h-full object-cover">
                        @else
                            {{ strtoupper(substr($full_name ?: $name ?: '?', 0, 1)) }}
                        @endif
                    </div>
                    <div class="flex-1">
                        <label class="label">Foto Profil</label>
                        <input type="file" wire:model="avatar" accept="image/*"
                               class="block w-full text-xs text-slate-600 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-slate-100 file:text-slate-700">
                        <p class="text-[11px] text-slate-500 mt-1">JPG/PNG, max 2MB.</p>
                        <div wire:loading wire:target="avatar" class="text-[11px] text-slate-500 mt-1">Mengunggah…</div>
                        @error('avatar') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- Akun --}}
            <div class="card p-5 space-y-4">
                <h3 class="text-sm font-semibold text-slate-900 -mb-1">Akun</h3>

                <div>
                    <label class="label">Nama Tampilan</label>
                    <input wire:model="name" class="input">
                    @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="label">Email</label>
                    <input type="email" wire:model="email" class="input" inputmode="email">
                    @error('email') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="label">Nama Lengkap</label>
                    <input wire:model="full_name" class="input">
                    @error('full_name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="label">Nama Panggilan</label>
                    <input wire:model="nickname" class="input">
                </div>

                <div>
                    <label class="label">Telepon</label>
                    <input wire:model="phone" class="input" inputmode="tel">
                </div>
            </div>

            {{-- Data pribadi --}}
            <div class="card p-5 space-y-4">
                <h3 class="text-sm font-semibold text-slate-900 -mb-1">Data Pribadi</h3>

                <div>
                    <label class="label">Jenis Kelamin</label>
                    <select wire:model="gender" class="input">
                        <option value="">— Pilih —</option>
                        <option value="male">Laki-laki</option>
                        <option value="female">Perempuan</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="label">Tanggal Lahir</label>
                        <input type="date" wire:model="date_of_birth" class="input">
                        @error('date_of_birth') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Tempat Lahir</label>
                        <input wire:model="place_of_birth" class="input">
                    </div>
                </div>

                <div>
                    <label class="label">Agama</label>
                    <select wire:model="religion" class="input">
                        <option value="">— Pilih —</option>
                        <option value="Islam">Islam</option>
                        <option value="Kristen">Kristen</option>
                        <option value="Katolik">Katolik</option>
                        <option value="Hindu">Hindu</option>
                        <option value="Budha">Budha</option>
                        <option value="Konghucu">Konghucu</option>
                    </select>
                </div>
            </div>

            {{-- Bank --}}
            <div class="card p-5 space-y-4">
                <h3 class="text-sm font-semibold text-slate-900 -mb-1">Rekening Bank</h3>

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
                <div class="relative" x-data="{ show: false }">
                    <input :type="show ? 'text' : 'password'" wire:model="current_password" class="input pr-10"
                        autocomplete="current-password">
                    <button type="button" @click="show = !show" tabindex="-1"
                        class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 hover:text-slate-600"
                        :aria-label="show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'">
                        <x-icon name="eye" class="w-5 h-5" x-show="!show" />
                        <x-icon name="eye-off" class="w-5 h-5" x-show="show" x-cloak />
                    </button>
                </div>
                @error('current_password') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="label">Kata Sandi Baru</label>
                <div class="relative" x-data="{ show: false }">
                    <input :type="show ? 'text' : 'password'" wire:model="new_password" class="input pr-10"
                        autocomplete="new-password">
                    <button type="button" @click="show = !show" tabindex="-1"
                        class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 hover:text-slate-600"
                        :aria-label="show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'">
                        <x-icon name="eye" class="w-5 h-5" x-show="!show" />
                        <x-icon name="eye-off" class="w-5 h-5" x-show="show" x-cloak />
                    </button>
                </div>
                @error('new_password') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="label">Konfirmasi Kata Sandi Baru</label>
                <div class="relative" x-data="{ show: false }">
                    <input :type="show ? 'text' : 'password'" wire:model="new_password_confirmation"
                        class="input pr-10" autocomplete="new-password">
                    <button type="button" @click="show = !show" tabindex="-1"
                        class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 hover:text-slate-600"
                        :aria-label="show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'">
                        <x-icon name="eye" class="w-5 h-5" x-show="!show" />
                        <x-icon name="eye-off" class="w-5 h-5" x-show="show" x-cloak />
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-primary w-full" wire:loading.attr="disabled" wire:target="changePassword">
                <span wire:loading.remove wire:target="changePassword">Ubah Kata Sandi</span>
                <span wire:loading wire:target="changePassword">Memproses…</span>
            </button>
        </form>
    </div>
</div>
