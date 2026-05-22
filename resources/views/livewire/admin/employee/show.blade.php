<div class="space-y-5">
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
                <p class="text-sm text-slate-500 mt-0.5">{{ $employee->user?->email }}</p>
                <div class="flex flex-wrap gap-2 mt-2">
                    <span class="badge bg-{{ $employee->employment_status?->color() }}-100 text-{{ $employee->employment_status?->color() }}-700">
                        {{ $employee->employment_status?->label() }}
                    </span>
                    @if ($employee->join_date)
                        <span class="text-xs text-slate-500">
                            Bergabung {{ $employee->join_date->translatedFormat('d M Y') }}
                            ({{ $employee->join_date->diffForHumans() }})
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
            <p class="text-xs uppercase text-slate-500">Gaji Pokok</p>
            <p class="text-lg font-bold text-slate-900 mt-1">{{ rupiah($employee->basic_salary) }}</p>
        </div>
        <div class="card p-4">
            <p class="text-xs uppercase text-slate-500">Bergabung</p>
            <p class="text-lg font-bold text-slate-900 mt-1">{{ $employee->join_date?->translatedFormat('M Y') ?? '-' }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        {{-- Identity --}}
        <div class="card p-5">
            <h3 class="text-sm font-semibold text-slate-900 mb-3">Identitas</h3>
            <dl class="divide-y divide-slate-100 text-sm">
                @php
                    $identity = [
                        'NIK Karyawan' => $employee->employee_number,
                        'Nama Panggilan' => $employee->nickname,
                        'Jenis Kelamin' => $employee->gender === 'male' ? 'Laki-laki' : ($employee->gender === 'female' ? 'Perempuan' : '-'),
                        'Tanggal Lahir' => $employee->date_of_birth?->translatedFormat('d M Y'),
                        'Tempat Lahir' => $employee->place_of_birth,
                        'Agama' => $employee->religion,
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
                        'Email' => $employee->user?->email,
                        'Telepon' => $employee->phone,
                        'Alamat' => $employee->address,
                        'Kota' => $employee->city,
                        'Provinsi' => $employee->province,
                        'Kode Pos' => $employee->postal_code,
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
            <h3 class="text-sm font-semibold text-slate-900 mb-3">Penggajian</h3>
            <dl class="divide-y divide-slate-100 text-sm">
                @php
                    $salary = [
                        'Gaji Pokok' => rupiah($employee->basic_salary),
                        'Bank' => $employee->bank_name,
                        'No. Rekening' => $employee->bank_account_number,
                        'Atas Nama' => $employee->bank_account_holder,
                    ];
                @endphp
                @foreach ($salary as $k => $v)
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
