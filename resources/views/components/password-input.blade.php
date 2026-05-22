@props(['name', 'label' => null, 'autocomplete' => 'current-password'])

<div>
  @if ($label)
    <label class="label">{{ $label }}</label>
  @endif
  <div class="relative" x-data="{ show: false }">
    <input :type="show ? 'text' : 'password'" wire:model="{{ $name }}" class="input pr-10"
      placeholder="Masukkan password" autocomplete="{{ $autocomplete }}">
    <button type="button" @click="show = !show" tabindex="-1"
      class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 hover:text-slate-600"
      :aria-label="show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'">
      <x-icon name="eye" class="w-5 h-5" x-show="!show" />
      <x-icon name="eye-off" class="w-5 h-5" x-show="show" x-cloak />
    </button>
  </div>
  @error($name)
    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
  @enderror
</div>
