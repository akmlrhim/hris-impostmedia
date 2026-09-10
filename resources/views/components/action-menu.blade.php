@props(['align' => 'right'])

<div
  x-data="{
    open: false,
    align: @js($align),
    menuStyle: '',
    toggle() {
      this.open = !this.open;
      if (this.open) this.position();
    },
    position() {
      const r = this.$refs.trigger.getBoundingClientRect();
      const w = 176;
      const pad = 4;
      let left = this.align === 'right' ? r.right - w : r.left;
      left = Math.min(Math.max(left, pad), window.innerWidth - w - pad);
      const flip = r.bottom + 200 > window.innerHeight;
      const top = flip ? r.top - pad : r.bottom + pad;
      this.menuStyle = (flip ? 'transform: translateY(-100%); ' : '') + 'position: fixed; left:' + left + 'px; top:' + top + 'px;';
    },
  }"
  @click.outside="open = false"
  class="relative inline-block text-left">
  <button type="button" x-ref="trigger" @click="toggle()"
    class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
    <x-icon name="more-vertical" class="w-4 h-4" />
  </button>
  <div x-cloak x-show="open"
    x-transition:enter="transition ease-out duration-100"
    x-transition:enter-start="opacity-0 scale-95"
    x-transition:enter-end="opacity-100 scale-100"
    x-transition:leave="transition ease-in duration-75"
    x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-95"
    :style="menuStyle"
    class="w-44 bg-white rounded-xl border border-slate-200 py-1 shadow-lg z-50">
    {{ $slot }}
  </div>
</div>