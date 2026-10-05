<div class="space-y-6">
  <x-page-header title="Daftar Karyawan" description="Kelola data seluruh karyawan.">
    <x-slot:action>
      <button type="button" @click="$wire.set('showForm', true, false); $wire.open()" class="btn-primary">Tambah
        Karyawan</button>
    </x-slot:action>
  </x-page-header>

  {{-- Filter bar --}}
  <div class="card p-4 flex flex-col sm:flex-row gap-3 items-stretch sm:items-center">
    <div class="flex-1 relative">
      <x-icon name="search" class="absolute left-3 top-2.5 w-4 h-4 text-slate-400" />
      <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama, nomor karyawan, atau NIK…"
        class="input pl-9">
    </div>

    <select wire:model.live="work_type_filter" class="input md:w-44">
      <option value="">Semua Tipe Kerja</option>
      @foreach (\App\Enums\WorkType::cases() as $w)
        <option value="{{ $w->value }}">{{ $w->label() }}</option>
      @endforeach
    </select>

    <select wire:model.live="filterStatus" class="input md:w-40">
      <option value="">Semua Status</option>
      <option value="1">Aktif</option>
      <option value="0">Nonaktif</option>
    </select>
  </div>

  {{-- Form modal --}}
  @include('livewire.admin.employee.partials.form-modal')

  @include('livewire.admin.employee.partials.table', [
      'employees' => $employees,
      'emptyMessage' => 'Tidak ada karyawan ditemukan.',
  ])
</div>
