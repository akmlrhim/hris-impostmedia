@php
    // Map session flash → alert. Aliases: 'error' → 'danger'.
    $flash = null;
    foreach (['success', 'danger', 'warning', 'info', 'dark'] as $type) {
        if (session()->has($type)) {
            $flash = ['type' => $type, 'message' => (string) session($type)];
            break;
        }
    }
    if (! $flash && session()->has('error')) {
        $flash = ['type' => 'danger', 'message' => (string) session('error')];
    }
@endphp

<div x-data="{
        visible: @js($flash !== null),
        type: @js($flash['type'] ?? 'info'),
        message: @js($flash['message'] ?? ''),
        timer: null,
        classes: {
            success: 'text-emerald-800 bg-emerald-50 border border-emerald-200',
            danger:  'text-red-800 bg-red-50 border border-red-200',
            warning: 'text-amber-800 bg-amber-50 border border-amber-200',
            info:    'text-slate-800 bg-slate-100 border border-slate-200',
            dark:    'text-white bg-slate-800 border border-slate-900',
        },
        titles: {
            success: 'Berhasil!',
            danger:  'Gagal!',
            warning: 'Perhatian!',
            info:    'Info',
            dark:    'Catatan',
        },
        show(rawType, message) {
            const aliases = { error: 'danger' };
            const type = aliases[rawType] ?? rawType;
            this.type = ['success','danger','warning','info','dark'].includes(type) ? type : 'info';
            this.message = message ?? '';
            this.visible = true;
            clearTimeout(this.timer);
            this.timer = setTimeout(() => { this.visible = false; }, 4000);
        },
        init() {
            if (this.visible) {
                this.timer = setTimeout(() => { this.visible = false; }, 4000);
            }
            Livewire.on('notify', (payload) => {
                const data = Array.isArray(payload) ? payload[0] : payload;
                if (! data) return;
                this.show(data.type ?? 'success', data.message ?? '');
            });
        },
    }"
    x-show="visible"
    x-transition.opacity
    :class="classes[type]"
    role="alert"
    class="p-4 mb-4 text-sm rounded-lg flex items-start gap-3"
    style="display: none;">
    <span class="flex-1">
        <span class="font-medium" x-text="titles[type]"></span>
        <span x-text="message"></span>
    </span>
    <button type="button" @click="visible = false; clearTimeout(timer)"
            class="text-current opacity-60 hover:opacity-100 leading-none text-lg">&times;</button>
</div>
