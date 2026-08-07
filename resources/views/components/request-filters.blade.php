@props([
    'statuses' => [],
    'property' => 'statusFilter',
    'total' => null,
])

{{-- Filter row for the request pages: an optional type dropdown in the
     `leading` slot, then one pill per status.

     The pills paint their active state straight from `$wire`, so the tap lands
     instantly while the filtered list is still being fetched. --}}
<div class="card-float p-3">
  <div class="flex items-center gap-2 overflow-x-auto no-scrollbar">
    <x-icon name="filter" class="w-4 h-4 text-navy-400 shrink-0" />

    @isset($leading)
      {{ $leading }}
    @endisset

    <button type="button" @click="$wire.{{ $property }} = ''"
      :class="$wire.{{ $property }} === '' ? 'chip-active' : ''" class="chip">Semua</button>

    @foreach ($statuses as $status)
      <button type="button" @click="$wire.{{ $property }} = '{{ $status->value }}'"
        :class="$wire.{{ $property }} === '{{ $status->value }}' ? 'chip-active' : ''"
        class="chip">{{ $status->label() }}</button>
    @endforeach
  </div>

  @if ($total !== null)
    <p class="text-[11px] text-navy-400 mt-2 px-0.5">{{ $total }} pengajuan</p>
  @endif
</div>
