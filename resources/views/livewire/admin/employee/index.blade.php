<div class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-base font-semibold text-slate-900">Daftar Karyawan</h2>
            <p class="text-sm text-slate-500">Kelola data seluruh karyawan.</p>
        </div>
        <button wire:click="open" class="btn-primary">
            <x-icon name="plus" class="w-4 h-4" /> Tambah Karyawan
        </button>
    </div>

    {{-- Filter bar --}}
    <div class="card p-4 flex flex-col md:flex-row gap-3 items-stretch md:items-center">
        <div class="flex-1 relative">
            <x-icon name="search" class="absolute left-3 top-2.5 w-4 h-4 text-slate-400" />
            <input wire:model.live.debounce.300ms="search" type="text"
                   placeholder="Cari nama atau NIK karyawan…"
                   class="input pl-9">
        </div>

        <select wire:model.live="position" class="input md:w-52">
            <option value="">Semua Posisi</option>
            @foreach ($positions as $p)
                <option value="{{ $p->id }}">{{ $p->name }}</option>
            @endforeach
        </select>

        <select wire:model.live="status" class="input md:w-44">
            <option value="">Semua Status</option>
            @foreach (\App\Enums\EmploymentStatus::cases() as $s)
                <option value="{{ $s->value }}">{{ $s->label() }}</option>
            @endforeach
        </select>
    </div>

    {{-- Form modal --}}
    <x-modal show="showForm" max-width="4xl" :title="$editingId ? 'Ubah Karyawan' : 'Tambah Karyawan'">
        <form wire:submit="save" class="space-y-5" wire:key="employee-form-{{ $editingId ?? 'new' }}">

            {{-- Avatar --}}
            <div class="flex items-center gap-4">
                <div class="w-20 h-20 rounded-full bg-slate-200 overflow-hidden flex items-center justify-center text-2xl font-bold text-slate-500 shrink-0">
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
                    <input type="file" wire:model="avatar" accept="image/*" class="block w-full text-sm text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
                    <p class="text-xs text-slate-500 mt-1">Format: JPG/PNG, max 2MB.</p>
                    <div wire:loading wire:target="avatar" class="text-xs text-slate-500 mt-1">Mengunggah…</div>
                    @error('avatar') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Akun & Identitas --}}
            <div class="pt-4 border-t border-slate-100">
                <h4 class="text-sm font-semibold text-slate-900 mb-2">Akun & Identitas</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="label">NIK / Nomor Karyawan <span class="text-red-500">*</span></label>
                        <input wire:model="employee_number" class="input" placeholder="EMP-0001">
                        @error('employee_number') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input wire:model="full_name" class="input">
                        @error('full_name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Nama Panggilan</label>
                        <input wire:model="nickname" class="input">
                        @error('nickname') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Email <span class="text-red-500">*</span></label>
                        <input type="email" wire:model="email" class="input">
                        @error('email') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Telepon</label>
                        <input wire:model="phone" class="input" placeholder="+62 …">
                        @error('phone') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- Data Pribadi --}}
            <div class="pt-4 border-t border-slate-100">
                <h4 class="text-sm font-semibold text-slate-900 mb-2">Data Pribadi</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div>
                        <label class="label">Jenis Kelamin</label>
                        <select wire:model="gender" class="input">
                            <option value="male">Laki-laki</option>
                            <option value="female">Perempuan</option>
                        </select>
                    </div>
                    <div>
                        <label class="label">Tanggal Lahir</label>
                        <input type="date" wire:model="date_of_birth" class="input">
                    </div>
                    <div>
                        <label class="label">Tempat Lahir</label>
                        <input wire:model="place_of_birth" class="input">
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
            </div>

            {{-- Alamat --}}
            <div class="pt-4 border-t border-slate-100">
                <h4 class="text-sm font-semibold text-slate-900 mb-2">Alamat</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div class="sm:col-span-2 lg:col-span-3">
                        <label class="label">Alamat</label>
                        <textarea wire:model="address" rows="2" class="input"></textarea>
                    </div>
                    <div>
                        <label class="label">Kota</label>
                        <input wire:model="city" class="input">
                    </div>
                    <div>
                        <label class="label">Provinsi</label>
                        <input wire:model="province" class="input">
                    </div>
                    <div>
                        <label class="label">Kode Pos</label>
                        <input wire:model="postal_code" class="input" maxlength="10">
                    </div>
                </div>
            </div>

            {{-- Penempatan --}}
            <div class="pt-4 border-t border-slate-100">
                <h4 class="text-sm font-semibold text-slate-900 mb-2">Penempatan</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <div class="flex items-center justify-between mb-1">
                            <label class="label !mb-0">Posisi</label>
                            @if (! $showNewPosition)
                                <button type="button" wire:click="$set('showNewPosition', true)"
                                        class="text-xs text-brand-600 hover:underline font-medium">+ Tambah posisi baru</button>
                            @endif
                        </div>

                        @if ($showNewPosition)
                            <div class="flex gap-2">
                                <input wire:model="new_position_name" class="input flex-1" placeholder="Nama posisi"
                                       wire:keydown.enter.prevent="addPosition">
                                <button type="button" wire:click="addPosition" class="btn-primary shrink-0">Tambah</button>
                                <button type="button" wire:click="$set('showNewPosition', false)" class="btn-secondary shrink-0">Batal</button>
                            </div>
                            @error('new_position_name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        @else
                            <select wire:model="job_position_id" class="input">
                                <option value="">— Pilih posisi —</option>
                                @foreach ($positions as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                                @endforeach
                            </select>
                        @endif
                    </div>

                    <div>
                        <label class="label">Status Karyawan <span class="text-red-500">*</span></label>
                        <select wire:model="employment_status" class="input">
                            @foreach ($employmentStatuses as $s)
                                <option value="{{ $s->value }}">{{ $s->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="label">Tanggal Bergabung <span class="text-red-500">*</span></label>
                        <input type="date" wire:model="join_date" class="input">
                        @error('join_date') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Akhir Masa Probation</label>
                        <input type="date" wire:model="probation_end_date" class="input">
                        @error('probation_end_date') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Akhir Kontrak</label>
                        <input type="date" wire:model="contract_end_date" class="input">
                        @error('contract_end_date') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
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
                        @error('basic_salary') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Nama Bank</label>
                        <input wire:model="bank_name" class="input" placeholder="BCA">
                    </div>
                    <div>
                        <label class="label">No. Rekening</label>
                        <input wire:model="bank_account_number" class="input">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="label">Atas Nama</label>
                        <input wire:model="bank_account_holder" class="input">
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap gap-2 justify-end pt-4 border-t border-slate-100">
                <button type="button" wire:click="$set('showForm', false)" class="btn-secondary">Batal</button>
                <button type="submit" class="btn-primary" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="save">Simpan</span>
                    <span wire:loading wire:target="save">Menyimpan…</span>
                </button>
            </div>

            @if (! $editingId)
                <p class="text-xs text-slate-500 text-right">
                    Password awal otomatis: <code class="bg-slate-100 px-1 rounded">password</code>
                </p>
            @endif
        </form>
    </x-modal>

    {{-- Table --}}
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr class="text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">
                        <th class="px-5 py-3">Karyawan</th>
                        <th class="px-5 py-3">NIK</th>
                        <th class="px-5 py-3">Posisi</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse ($employees as $emp)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-slate-200 flex items-center justify-center font-semibold text-slate-600">
                                        {{ substr($emp->full_name, 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-slate-900">{{ $emp->full_name }}</p>
                                        <p class="text-xs text-slate-500">{{ $emp->position?->name ?? '—' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3 text-slate-600">{{ $emp->employee_number }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $emp->position?->name ?? '—' }}</td>
                            <td class="px-5 py-3">
                                <span class="badge bg-{{ $emp->employment_status?->color() }}-100 text-{{ $emp->employment_status?->color() }}-700">
                                    {{ $emp->employment_status?->label() }}
                                </span>
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <a wire:navigate href="{{ route('admin.employees.show', $emp) }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium bg-blue-50 text-blue-600 hover:bg-blue-100 transition">
                                        <x-icon name="eye" class="w-3.5 h-3.5" /> Detail
                                    </a>
                                    <button wire:click="open({{ $emp->id }})" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium bg-amber-50 text-amber-600 hover:bg-amber-100 transition">
                                        <x-icon name="pencil" class="w-3.5 h-3.5" /> Edit
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-slate-500">Tidak ada data karyawan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-5 py-3 border-t border-slate-200">{{ $employees->links() }}</div>
    </div>
</div>
