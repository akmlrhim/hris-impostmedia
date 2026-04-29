<div>
  <div class="flex items-center gap-3 mb-4">
    <a href="{{ route('admin.employees') }}" class="text-slate-600 hover:text-slate-900">
      <x-icon name="arrow-left" class="w-5 h-5" />
    </a>
    <h2 class="text-base font-semibold text-slate-900">
      {{ $employeeId ? 'Edit Karyawan' : 'Tambah Karyawan' }}
    </h2>
  </div>

  <form wire:submit="save" class="space-y-6 max-w-4xl">
    <div class="card p-5 sm:p-6">
      <h3 class="text-sm font-semibold text-slate-900 mb-4">Identitas</h3>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="label">NIK / Nomor Karyawan</label>
          <input wire:model="employee_number" class="input">
          @error('employee_number')
            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
          @enderror
        </div>
        <div>
          <label class="label">Nama Lengkap</label>
          <input wire:model="full_name" class="input">
          @error('full_name')
            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
          @enderror
        </div>
        <div>
          <label class="label">Email</label>
          <input type="email" wire:model="email" class="input">
          @error('email')
            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
          @enderror
        </div>
        <div>
          <label class="label">Telepon</label>
          <input wire:model="phone" class="input">
        </div>
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
      </div>
    </div>

    <div class="card p-5 sm:p-6">
      <h3 class="text-sm font-semibold text-slate-900 mb-4">Penempatan</h3>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="sm:col-span-2">
          <div class="flex items-center justify-between mb-1">
            <label class="label !mb-0">Posisi</label>
            @if (!$showNewPosition)
              <button type="button" wire:click="$set('showNewPosition', true)"
                class="text-xs text-brand-600 hover:underline font-medium">
                + Tambah posisi baru
              </button>
            @endif
          </div>

          @if ($showNewPosition)
            <div class="flex gap-2">
              <input wire:model="new_position_name" class="input flex-1" placeholder="Nama posisi "
                wire:keydown.enter.prevent="addPosition">
              <button type="button" wire:click="addPosition" class="btn-primary shrink-0">Tambah</button>
              <button type="button" wire:click="$set('showNewPosition', false)"
                class="btn-secondary shrink-0">Batal</button>
            </div>
            @error('new_position_name')
              <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
            @enderror
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
          <label class="label">Status Karyawan</label>
          <select wire:model="employment_status" class="input">
            @foreach ($employmentStatuses as $s)
              <option value="{{ $s->value }}">{{ $s->label() }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="label">Tanggal Bergabung</label>
          <input type="date" wire:model="join_date" class="input">
          @error('join_date')
            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
          @enderror
        </div>
        <label class="flex items-center gap-2 mt-1 sm:col-span-2 text-sm">
          <input type="checkbox" wire:model="is_active" class="rounded border-slate-300">
          Karyawan aktif
        </label>
      </div>
    </div>

    <div class="card p-5 sm:p-6">
      <h3 class="text-sm font-semibold text-slate-900 mb-4">Gaji & Bank</h3>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="label">Gaji Pokok</label>
          <x-currency-input wire-model="basic_salary" />
          @error('basic_salary') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
          <label class="label">Bank</label>
          <input wire:model="bank_name" class="input" placeholder="BCA">
        </div>
        <div class="sm:col-span-2">
          <label class="label">No. Rekening</label>
          <input wire:model="bank_account_number" class="input">
        </div>
      </div>
    </div>

    <div class="flex flex-wrap gap-2 justify-end">
      <a href="{{ route('admin.employees') }}" class="btn-secondary">Batal</a>
      <button type="submit" class="btn-primary" wire:loading.attr="disabled">
        <span wire:loading.remove wire:target="save">Simpan</span>
        <span wire:loading wire:target="save">Menyimpan…</span>
      </button>
    </div>

    @if (!$employeeId)
      <p class="text-xs text-slate-500 text-right">
        Password awal otomatis: <code class="bg-slate-100 px-1 rounded">password</code>
      </p>
    @endif
  </form>
</div>
