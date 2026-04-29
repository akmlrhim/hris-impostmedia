// Alpine factory: <div x-data="currencyInput($wire, 'basic_salary')">
//
// User mengetik angka, otomatis tampil dengan pemisah ribuan ID ("8.000.000").
// Property Livewire selalu menyimpan nilai numerik mentah (8000000).

window.currencyInput = function (wire, property) {
    return {
        formatted: '',
        formatNumber(value) {
            const num = Number(String(value ?? '').replace(/\D/g, '')) || 0;
            return num > 0 ? num.toLocaleString('id-ID') : '';
        },
        init() {
            const initial = Number(wire.get(property)) || 0;
            this.formatted = initial > 0 ? initial.toLocaleString('id-ID') : '';
        },
        onInput(event) {
            const raw = event.target.value.replace(/\D/g, '');
            const num = raw === '' ? 0 : parseInt(raw, 10);
            this.formatted = num > 0 ? num.toLocaleString('id-ID') : '';
            // false = jangan commit ke server, biar tidak round-trip tiap ketik
            wire.set(property, num, false);
        },
    };
};
