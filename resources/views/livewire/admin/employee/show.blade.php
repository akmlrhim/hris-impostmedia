<div class="space-y-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.employees') }}" class="text-slate-600 hover:text-slate-900">
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
            <a href="{{ route('admin.employees.edit', $employee->id) }}" class="btn-primary">
                <x-icon name="cog" class="w-4 h-4" /> Edit
            </a>
        </div>
    </div>

    {{-- Header card --}}
    <div class="card p-5 sm:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center gap-4">
            <div class="w-20 h-20 rounded-full bg-slate-900 text-white flex items-center justify-center text-3xl font-bold shrink-0">
                {{ strtoupper(substr($employee->full_name, 0, 1)) }}
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
                <p class="text-sm text-slate-600">
                    {{ $employee->position?->name ?? 'Tanpa posisi' }}
                    @if ($employee->position?->level)
                        · {{ $employee->position->level->name }}
                    @endif
                </p>
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
            <p class="text-xs uppercase text-slate-500">PTKP</p>
            <p class="text-lg font-bold text-slate-900 mt-1">{{ $employee->ptkp_status ?? '—' }}</p>
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
                        'Jenis Kelamin' => $employee->gender === 'male' ? 'Laki-laki' : ($employee->gender === 'female' ? 'Perempuan' : '—'),
                        'Tanggal Lahir' => $employee->date_of_birth?->translatedFormat('d M Y'),
                        'Tempat Lahir' => $employee->place_of_birth,
                        'Agama' => $employee->religion,
                        'Status Pernikahan' => $employee->marital_status,
                        'Golongan Darah' => $employee->blood_type,
                        'NIK KTP' => $employee->nik_ktp,
                        'NPWP' => $employee->npwp,
                    ];
                @endphp
                @foreach ($identity as $k => $v)
                    <div class="flex justify-between gap-3 py-2">
                        <dt class="text-slate-500">{{ $k }}</dt>
                        <dd class="text-slate-900 font-medium text-right">{{ $v ?: '—' }}</dd>
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
                        'Kontak Darurat' => $employee->emergency_contact_name
                            ? "{$employee->emergency_contact_name} ({$employee->emergency_contact_relation})"
                            : null,
                        'Telp. Darurat' => $employee->emergency_contact_phone,
                    ];
                @endphp
                @foreach ($contact as $k => $v)
                    <div class="flex justify-between gap-3 py-2">
                        <dt class="text-slate-500 shrink-0">{{ $k }}</dt>
                        <dd class="text-slate-900 font-medium text-right">{{ $v ?: '—' }}</dd>
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
                        'PTKP' => $employee->ptkp_status,
                        'Bank' => $employee->bank_name,
                        'No. Rekening' => $employee->bank_account_number,
                        'Atas Nama' => $employee->bank_account_holder,
                        'BPJS Kesehatan' => $employee->bpjs_kesehatan,
                        'BPJS Ketenagakerjaan' => $employee->bpjs_ketenagakerjaan,
                    ];
                @endphp
                @foreach ($salary as $k => $v)
                    <div class="flex justify-between gap-3 py-2">
                        <dt class="text-slate-500">{{ $k }}</dt>
                        <dd class="text-slate-900 font-medium text-right">{{ $v ?: '—' }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>

        {{-- Leave Balances --}}
        <div class="card p-5">
            <h3 class="text-sm font-semibold text-slate-900 mb-3">Saldo Cuti {{ now()->year }}</h3>
            @if ($leaveBalances->isEmpty())
                <p class="text-sm text-slate-500">Belum ada saldo cuti.</p>
            @else
                <div class="space-y-2">
                    @foreach ($leaveBalances as $b)
                        @php $remaining = (float) $b->quota_days + (float) $b->carried_over_days - (float) $b->used_days; @endphp
                        <div class="flex items-center justify-between text-sm">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full" style="background:{{ $b->leaveType->color }}"></span>
                                <span class="text-slate-700">{{ $b->leaveType->name }}</span>
                            </div>
                            <span class="font-medium text-slate-900">
                                {{ rtrim(rtrim(number_format($remaining, 1), '0'), '.') }}
                                / {{ rtrim(rtrim(number_format((float) $b->quota_days, 1), '0'), '.') }} hari
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Recent Leaves --}}
        <div class="card p-5">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-slate-900">Pengajuan Cuti Terbaru</h3>
            </div>
            @forelse ($recentLeaves as $r)
                <div class="flex items-center justify-between py-2 border-b border-slate-100 last:border-0 text-sm">
                    <div>
                        <p class="font-medium text-slate-900">{{ $r->leaveType->name }}</p>
                        <p class="text-xs text-slate-500">
                            {{ $r->start_date->format('d M') }} – {{ $r->end_date->format('d M Y') }}
                            ({{ $r->total_days }} hari)
                        </p>
                    </div>
                    <span class="badge bg-{{ $r->status?->color() }}-100 text-{{ $r->status?->color() }}-700">
                        {{ $r->status?->label() }}
                    </span>
                </div>
            @empty
                <p class="text-sm text-slate-500">Belum ada pengajuan.</p>
            @endforelse
        </div>

        {{-- Recent Payslips --}}
        <div class="card p-5">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-slate-900">Slip Gaji Terbaru</h3>
            </div>
            @forelse ($recentPayrolls as $p)
                <a href="{{ route('admin.payroll.payslip', $p->id) }}"
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
