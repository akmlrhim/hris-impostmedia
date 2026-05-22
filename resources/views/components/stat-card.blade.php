@props(['label', 'value', 'icon', 'color' => 'bg-slate-500'])

<div class="card p-4">
  <div class="flex items-start justify-between">
    <div>
      <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">{{ $label }}</p>
      <p class="text-2xl font-bold text-black mt-1">{{ $value }}</p>
    </div>
    <div class="w-9 h-9 rounded-lg {{ $color }} flex items-center justify-center text-white">
      <x-icon :name="$icon" class="w-5 h-5" />
    </div>
  </div>
</div>
