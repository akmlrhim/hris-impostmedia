<div class="space-y-5" x-data="{ showSalary: false }">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <a wire:navigate href="{{ route('admin.employees') }}" class="text-slate-600 hover:text-slate-900">
                <x-icon name="arrow-left" class="w-5 h-5" />
            </a>
            <div>
                <h2 class="text-base font-semibold text-slate-900">Detail Karyawan</h2>
                <p class="text-sm text-slate-500">{{ $employee->employee_number }}</p>
            </div>
        </div>
        <div class="flex flex-wrap gap-2">
            <button wire:click="toggleActive"
                    wire:confirm="{{ $employee->is_active ? 'Nonaktifkan karyawan ini?' : 'Aktifkan karyawan ini?' }}"
                    class="btn-secondary">
                {{ $employee->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
            </button>
            <a wire:navigate href="{{ route('admin.employees', ['edit' => $employee->getRouteKey()]) }}" class="btn-primary">
                Edit
            </a>
        </div>
    </div>

    {{-- Header card --}}
    <div class="card p-5 sm:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center gap-4">
            <div class="w-20 h-20 rounded-full bg-slate-900 text-white flex items-center justify-center text-3xl font-bold shrink-0 overflow-hidden">
                @if ($employee->avatar_path)
                    <img src="{{ route('files.avatar', $employee) }}" class="w-full h-full object-cover">
                @else
                    {{ strtoupper(substr($employee->full_name, 0, 1)) }}
                @endif
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <h3 class="text-xl font-bold text-slate-900">{{ $employee->full_name }}</h3>
                    @if (! $employee->is_active)
                        <span class="badge bg-red-100 text-red-700">Nonaktif</span>
                    @else
                        <span class="badge bg-emerald-100 text-emerald-700">Aktif</span>
                    @endif
                </div>
                <p class="text-sm text-slate-500 mt-0.5">{{ $employee->user?->email ?? $employee->email }}</p>
                @if ($employee->position)
                    <p class="text-sm font-medium text-slate-700 mt-0.5">{{ $employee->position }}</p>
                @endif
                <div class="flex flex-wrap gap-2 mt-2">
                    @if ($employee->work_type)
                        <span class="badge bg-{{ $employee->work_type->color() }}-100 text-{{ $employee->work_type->color() }}-700">
                            {{ $employee->work_type->label() }}
                        </span>
                    @endif
                    @if ($employee->contract_start_date)
                        <span class="text-xs text-slate-500">
                            Mulai kontrak {{ $employee->contract_start_date->translatedFormat('d M Y') }}
                            ({{ $employee->contract_start_date->diffForHumans() }})
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Quick stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div class="card p-4">
            <p class="text-xs uppercase text-slate-500">Hadir Bulan Ini</p>
            <p class="text-2xl font-bold text-emerald-600 mt-1">{{ $attendanceStats['present'] }}</p>
        </div>
        <div class="card p-4">
            <p class="text-xs uppercase text-slate-500">Terlambat Bulan Ini</p>
            <p class="text-2xl font-bold text-amber-600 mt-1">{{ $attendanceStats['late'] }}</p>
        </div>
        <div class="card p-4">
            <div class="flex items-center justify-between">
                <p class="text-xs uppercase text-slate-500">Gaji Pokok</p>
                <button type="button" @click="showSalary = !showSalary"
                    class="text-slate-400 hover:text-slate-600 transition">
                    <x-icon name="eye" class="w-3.5 h-3.5" x-show="!showSalary" />
                    <x-icon name="eye-off" class="w-3.5 h-3.5" x-show="showSalary" x-cloak />
                </button>
            </div>
            <p class="text-lg font-bold text-slate-900 mt-1">
                <span x-show="showSalary" x-cloak>{{ rupiah($employee->basic_salary) }}</span>
                <span x-show="!showSalary" class="tracking-widest text-slate-400 text-base">••••••</span>
            </p>
        </div>
        <div class="card p-4">
            <p class="text-xs uppercase text-slate-500">Mulai Kontrak</p>
            <p class="text-lg font-bold text-slate-900 mt-1">{{ $employee->contract_start_date?->translatedFormat('M Y') ?? '-' }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        {{-- Identity --}}
        <div class="card p-5">
            <h3 class="text-sm font-semibold text-slate-900 mb-3">Identitas</h3>
            <dl class="divide-y divide-slate-100 text-sm">
                @php
                    $identity = [
                        'Nomor Karyawan' => $employee->employee_number,
                        'Jabatan / Posisi' => $employee->position,
                        'NIK' => $employee->nik,
                        'Nama Panggilan' => $employee->nickname,
                        'Jenis Kelamin' => $employee->gender === 'male' ? 'Laki-laki' : ($employee->gender === 'female' ? 'Perempuan' : '-'),
                        'Tanggal Lahir' => $employee->date_of_birth?->translatedFormat('d M Y'),
                        'Tempat Lahir' => $employee->place_of_birth,
                        'Pendidikan Terakhir' => $employee->last_education,
                        'Jurusan/Sekolah' => $employee->major_school_university,
                    ];
                @endphp
                @foreach ($identity as $k => $v)
                    <div class="flex justify-between gap-3 py-2">
                        <dt class="text-slate-500">{{ $k }}</dt>
                        <dd class="text-slate-900 font-medium text-right">{{ $v ?: '-' }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>

        {{-- Contact --}}
        <div class="card p-5">
            <h3 class="text-sm font-semibold text-slate-900 mb-3">Kontak & Alamat</h3>
            <dl class="divide-y divide-slate-100 text-sm">
                @php
                    $contact = [
                        'Email' => $employee->user?->email ?? $employee->email,
                        'Telepon' => $employee->phone,
                        'Alamat' => $employee->address,
                        'Kontak Darurat' => $employee->emergency_contact_name,
                        'No. Kontak Darurat' => $employee->emergency_contact_number,
                    ];
                @endphp
                @foreach ($contact as $k => $v)
                    <div class="flex justify-between gap-3 py-2">
                        <dt class="text-slate-500 shrink-0">{{ $k }}</dt>
                        <dd class="text-slate-900 font-medium text-right">{{ $v ?: '-' }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>

        {{-- Bank & Salary --}}
        <div class="card p-5">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-slate-900">Penggajian</h3>
                <button type="button" @click="showSalary = !showSalary"
                    class="flex items-center gap-1 text-xs text-slate-500 hover:text-slate-700 transition">
                    <x-icon name="eye" class="w-3.5 h-3.5" x-show="!showSalary" />
                    <x-icon name="eye-off" class="w-3.5 h-3.5" x-show="showSalary" x-cloak />
                    <span x-text="showSalary ? 'Sembunyikan' : 'Tampilkan gaji'"></span>
                </button>
            </div>
            <dl class="divide-y divide-slate-100 text-sm">
                <div class="flex justify-between gap-3 py-2">
                    <dt class="text-slate-500">Gaji Pokok</dt>
                    <dd class="text-slate-900 font-medium text-right">
                        <span x-show="showSalary" x-cloak>{{ rupiah($employee->basic_salary) }}</span>
                        <span x-show="!showSalary" class="tracking-widest text-slate-400">••••••</span>
                    </dd>
                </div>
                @php
                    $bankFields = [
                        'Bank' => $employee->bank_name,
                        'No. Rekening' => $employee->bank_account_number,
                        'Atas Nama' => $employee->bank_account_holder,
                    ];
                @endphp
                @foreach ($bankFields as $k => $v)
                    <div class="flex justify-between gap-3 py-2">
                        <dt class="text-slate-500">{{ $k }}</dt>
                        <dd class="text-slate-900 font-medium text-right">{{ $v ?: '-' }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>

        {{-- Recent Payslips --}}
        <div class="card p-5">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-slate-900">Slip Gaji Terbaru</h3>
            </div>
            @forelse ($recentPayrolls as $p)
                <a wire:navigate href="{{ route('admin.payroll.payslip', $p) }}"
                   class="flex items-center justify-between py-2 border-b border-slate-100 last:border-0 text-sm hover:bg-slate-50 -mx-2 px-2 rounded transition">
                    <div>
                        <p class="font-medium text-slate-900">{{ $p->period->code }}</p>
                        <p class="text-xs text-slate-500 capitalize">{{ $p->status }}</p>
                    </div>
                    <span class="font-semibold text-emerald-600">{{ rupiah($p->net_salary) }}</span>
                </a>
            @empty
                <p class="text-sm text-slate-500">Belum ada slip gaji.</p>
            @endforelse
        </div>
    </div>

</div>
