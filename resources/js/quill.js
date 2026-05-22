import Quill from "quill";
import "quill/dist/quill.snow.css";

window.Quill = Quill;

window.quillEditor = function (wire, property) {
    return {
        editor: null,
        _debounce: null,
        init() {
            this.$nextTick(() => {
                const initial = wire.get(property) ?? "";

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
                }

                this.editor.on("text-change", () => {
                    const html = this.editor.root.innerHTML;
                    const isEmpty =
                        html === "<p><br></p>" || html.trim() === "";
                    const clean = isEmpty ? "" : html;

                    // Mark dirty on client so the next Livewire request includes it
                    wire[property] = clean;

                    clearTimeout(this._debounce);
                    this._debounce = setTimeout(() => {
                        wire.set(property, clean);
                    }, 300);
                });

                wire.$watch(property, (value) => {
                    const current = this.editor.root.innerHTML;
                    const normalized =
                        current === "<p><br></p>" || current.trim() === ""
                            ? ""
                            : current;
                    if ((value ?? "") === normalized) return;
                    this.editor.clipboard.dangerouslyPasteHTML(value ?? "");
                });
            });
        },
        destroy() {
            clearTimeout(this._debounce);
        },
    };
};
