@props([
    'wireModel',
    'placeholder' => '0',
    'disabled' => false,
])

<div wire:ignore x-data="currencyInput($wire, '{{ $wireModel }}')" class="relative">
    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-sm pointer-events-none">Rp</span>
    <input type="text"
           x-ref="input"
           x-model="formatted"
           @input="onInput($event)"
           inputmode="numeric"
           autocomplete="off"
           placeholder="{{ $placeholder }}"
           @if ($disabled) disabled @endif
           {{ $attributes->merge(['class' => 'input pl-10']) }}>
</div>
