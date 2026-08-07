import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        laravel({
            input: ["resources/css/app.css", "resources/js/app.js"],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        // Tanpa ini Vite bind ke `::1` dan public/hot berisi `http://[::1]:5173`,
        // yang bikin aset dev (CSS/JS) gagal dimuat dari browser.
        host: "localhost",
        cors: true,
        watch: {
            ignored: ["**/storage/framework/views/**"],
        },
    },
    build: {
        rolldownOptions: {
            checks: {
                pluginTimings: false,
            },
        },
    },
});
