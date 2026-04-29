import Quill from 'quill';
import 'quill/dist/quill.snow.css';

window.Quill = Quill;

// Alpine factory: <div x-data="quillEditor($wire, 'content')"></div>
window.quillEditor = function (wire, property) {
    return {
        editor: null,
        init() {
            const initial = wire.get(property) || '';

            this.editor = new Quill(this.$refs.editor, {
                theme: 'snow',
                placeholder: 'Tulis konten pengumuman…',
                modules: {
                    toolbar: [
                        [{ header: [1, 2, 3, false] }],
                        ['bold', 'italic', 'underline', 'strike'],
                        [{ color: [] }, { background: [] }],
                        [{ list: 'ordered' }, { list: 'bullet' }],
                        ['blockquote', 'link'],
                        ['clean'],
                    ],
                },
            });

            if (initial) {
                this.editor.clipboard.dangerouslyPasteHTML(initial);
            }

            this.editor.on('text-change', () => {
                const html = this.editor.root.innerHTML;
                // Treat Quill's empty state as empty string for validation
                const clean = html === '<p><br></p>' ? '' : html;
                wire.set(property, clean, false);
            });
        },
    };
};
