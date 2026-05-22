<div>
  {{-- Header --}}
  <div class="px-5 pt-6 pb-4 bg-white border-b border-slate-200 flex items-center gap-3 sticky top-0 z-10">
    <a wire:navigate href="{{ route('mobile.home') }}" class="p-2 -ml-2 rounded-lg hover:bg-slate-100">
      <x-icon name="arrow-left" class="w-5 h-5" />
    </a>
    <h1 class="text-base font-bold text-slate-900 flex-1 truncate">Pengumuman</h1>
    @if ($announcement->is_pinned)
      <span class="badge bg-amber-100 text-amber-700 text-[10px]">📌 Disematkan</span>
    @endif
  </div>

  <div class="px-5 pt-5 pb-32 space-y-4">

    {{-- Meta --}}
    <div>
      <h2 class="text-lg font-bold text-slate-900 leading-snug">{{ $announcement->title }}</h2>
      <div class="flex items-center gap-2 mt-2 text-xs text-slate-400 flex-wrap">
        <x-icon name="user" class="w-3.5 h-3.5 shrink-0" />
        <span>{{ $announcement->author?->name ?? 'Admin' }}</span>
        <span>·</span>
        <x-icon name="clock" class="w-3.5 h-3.5 shrink-0" />
        <span>{{ $announcement->published_at->translatedFormat('d F Y, H:i') }}</span>
        @if ($announcement->expires_at)
          <span>·</span>
          <span class="text-amber-500">Berlaku hingga {{ $announcement->expires_at->translatedFormat('d F Y') }}</span>
        @endif
      </div>
    </div>

    <div class="border-t border-slate-100"></div>

    {{-- Content dari WYSIWYG editor --}}
    <div class="prose-announcement">
      {!! $announcement->content !!}
    </div>

  </div>
</div>
