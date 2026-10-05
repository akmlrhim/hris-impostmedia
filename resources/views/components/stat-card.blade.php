@props([
    'label',
    'value',
    'icon',
    'color' => 'bg-slate-100 text-slate-600',
    'delta' => null,
    'deltaLabel' => null,
    'goodWhenDown' => false,
])

<div class="bg-white p-5">
  <div class="flex items-start justify-between gap-3">
    <p class="text-sm font-medium text-slate-700">{{ $label }}</p>
    <div class="w-9 h-9 rounded-full {{ $color }} flex items-center justify-center shrink-0">
      <x-icon :name="$icon" class="w-4 h-4" />
    </div>
  </div>
  <p class="text-3xl font-bold text-black mt-2 tracking-tight">{{ $value }}</p>
  @if ($delta !== null)
    @php
      $direction = $delta >= 0 ? 'up' : 'down';
      $isGood = $goodWhenDown ? $delta <= 0 : $delta >= 0;
      $tone = $isGood ? 'text-emerald-600' : 'text-rose-600';
    @endphp
    <p class="mt-2 flex items-center gap-1.5 text-xs">
      <span class="inline-flex items-center gap-0.5 font-semibold {{ $tone }}">
        <x-icon :name="$direction === 'up' ? 'trending-up' : 'trending-down'" class="w-3.5 h-3.5" />
        {{ abs($delta) }}%
      </span>
      @if ($deltaLabel)
        <span class="text-slate-500 uppercase text-[10px] font-bold tracking-wide">{{ $deltaLabel }}</span>
      @endif
    </p>
  @endif
</div>