<div>
  {{-- Header --}}
  <x-mobile-header title="Pengumuman" :back="route('mobile.home')">
    @if ($announcement->is_pinned)
      <x-slot:action>
        <span class="hero-action">📌 Disematkan</span>
      </x-slot:action>
    @endif
  </x-mobile-header>

  <div class="px-4 -mt-10 pb-32">
    <div class="card-float p-5 space-y-4">

      {{-- Meta --}}
      <div>
        <h2 class="text-lg font-bold text-navy-800 leading-snug">{{ $announcement->title }}</h2>
        <div class="flex items-center gap-2 mt-2 text-xs text-navy-400 flex-wrap">
          <x-icon name="user" class="w-3.5 h-3.5 shrink-0" />
          <span>{{ $announcement->author?->name ?? 'Admin' }}</span>
          <span>·</span>
          <x-icon name="clock" class="w-3.5 h-3.5 shrink-0" />
          <span>{{ $announcement->published_at->translatedFormat('d F Y, H:i') }}</span>
          @if ($announcement->expires_at)
            <span>·</span>
            <span class="text-amber-500">Berlaku hingga
              {{ $announcement->expires_at->translatedFormat('d F Y') }}</span>
          @endif
        </div>
      </div>

      <div class="border-t border-slate-100"></div>

      {{-- Content dari WYSIWYG editor --}}
      <div class="ql-snow">
        <div class="ql-editor ql-readonly">{!! $announcement->content !!}</div>
      </div>

    </div>
  </div>
</div>
