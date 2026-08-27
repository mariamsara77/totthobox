import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/css/app.css",
                "resources/js/app.js",       // পাবলিক
                "resources/js/admin.js",     // অ্যাডমিন
                "resources/js/echo.js",      // শুধু চ্যাট পেজে
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    build: {
        cssCodeSplit: true,
        cssMinify: true,
        sourcemap: false,
    },
});