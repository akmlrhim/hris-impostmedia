import Quill from "quill";
import "quill/dist/quill.snow.css";

window.Quill = Quill;

// Alpine factory: <div x-data="quillEditor($wire, 'content')"></div>
window.quillEditor = function (wire, property) {
    return {
        editor: null,
        init() {
            const initial = wire.get(property) || "";

            this.editor = new Quill(this.$refs.editor, {
                theme: "snow",
                placeholder: "Tulis konten pengumuman…",
                modules: {
                    toolbar: [
                        [{ header: [1, 2, 3, false] }],
                        ["bold", "italic", "underline", "strike"],
                        [{ color: [] }, { background: [] }],
                        [{ list: "ordered" }, { list: "bullet" }],
                        ["blockquote", "link"],
                        ["clean"],
                    ],
                },
            });

            if (initial) {
                this.editor.clipboard.dangerouslyPasteHTML(initial);
                this.editor.setSelection(this.editor.getLength(), 0);
            }

            this.editor.on("text-change", () => {
                const html = this.editor.root.innerHTML;
                const clean = html === "<p><br></p>" ? "" : html;
                wire.set(property, clean, false);
            });

            // Sync editor when Livewire updates the property from the server
            // (e.g. when open() is called for editing or resetting the form)
            wire.$watch(property, (value) => {
                const currentHtml = this.editor.root.innerHTML;
                const normalized =
                    currentHtml === "<p><br></p>" ? "" : currentHtml;
                if ((value || "") === normalized) {
                    return;
                }
                this.editor.clipboard.dangerouslyPasteHTML(value || "");
                const length = this.editor.getLength();
                this.editor.setSelection(length, 0);
            });
        },
    };
};
