@props(['show' => 'showForm', 'submit' => 'Simpan', 'variant' => 'primary'])

<div class="flex gap-2 justify-end pt-2 border-t border-slate-100">
    <button type="button" @click="$wire.set('{{ $show }}', false, true)" class="btn-secondary">Batal</button>
    <button type="submit" class="btn-{{ $variant }}">{{ $submit }}</button>
</div>
