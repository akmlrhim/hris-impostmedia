function openConfirmModal(message) {
    return new Promise((resolve) => {
        window.dispatchEvent(
            new CustomEvent("confirm:open", {
                detail: { message, resolve },
            }),
        );
    });
}

function overrideConfirm(scope) {
    if (!scope) return;

    const apply = (el) => {
        const raw = el.getAttribute("wire:confirm");
        if (raw === null) return;
        const message = raw.replaceAll("\\n", "\n") || "Anda yakin?";

        el.__livewire_confirm = (action, instead) => {
            openConfirmModal(message).then((ok) => (ok ? action() : instead()));
        };
    };

    if (
        scope.nodeType === 1 &&
        scope.hasAttribute &&
        scope.hasAttribute("wire:confirm")
    ) {
        apply(scope);
    }
    if (scope.querySelectorAll) {
        scope.querySelectorAll("[wire\\:confirm]").forEach(apply);
    }
}

document.addEventListener("livewire:init", () => {
    queueMicrotask(() => overrideConfirm(document));

    Livewire.hook("morph.added", ({ el }) => {
        queueMicrotask(() => overrideConfirm(el));
    });
    Livewire.hook("morphed", ({ el }) => {
        queueMicrotask(() => overrideConfirm(el || document));
    });
    Livewire.hook("commit", ({ succeed }) => {
        succeed(() => queueMicrotask(() => overrideConfirm(document)));
    });
});

document.addEventListener("livewire:navigated", () => {
    queueMicrotask(() => overrideConfirm(document));
});
