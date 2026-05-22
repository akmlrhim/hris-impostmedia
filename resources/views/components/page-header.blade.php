@props(['title', 'description' => null])

<div class="flex flex-wrap items-center justify-between gap-3">
    <div>
        <h2 class="text-base font-semibold text-slate-900">{{ $title }}</h2>
        @if ($description)
            <p class="text-sm text-slate-500">{{ $description }}</p>
        @endif
    </div>
    @isset($action)
        {{ $action }}
    @endisset
</div>
