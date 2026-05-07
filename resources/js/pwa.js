// ── PWA: Service Worker ──────────────────────────────────────────────────────
if ("serviceWorker" in navigator) {
    window.addEventListener("load", () => {
        navigator.serviceWorker
            .register("/sw.js", { scope: "/" })
            .catch((err) => console.warn("SW registration failed:", err));
    });
}

// ── PWA: Install Prompt ──────────────────────────────────────────────────────
let _deferredPrompt = null;

window.addEventListener("beforeinstallprompt", (e) => {
    e.preventDefault();
    _deferredPrompt = e;
    window.dispatchEvent(new CustomEvent("pwa:installable"));
});

window.addEventListener("appinstalled", () => {
    _deferredPrompt = null;
    window.dispatchEvent(new CustomEvent("pwa:installed"));
});

window.pwaCanInstall = () => Boolean(_deferredPrompt);

window.pwaInstall = async () => {
    if (!_deferredPrompt) return false;
    _deferredPrompt.prompt();
    const { outcome } = await _deferredPrompt.userChoice;
    _deferredPrompt = null;
    return outcome === "accepted";
};
