import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import react from "@vitejs/plugin-react";

export default defineConfig({
    // server: {
    //     host: "0.0.0.0",
    //     port: 5173,
    //     watch: {
    //         usePolling: true,
    //     },
    //     hmr: {
    //         host: "app.localhost",
    //         protocol: "http",
    //     },
    // },

    // TODO: uncommnet this on merge
    server: {
        host: "0.0.0.0",
        port: 5173,
        https: true,
        watch: {
            usePolling: true,
        },
        hmr: {
            host: "passport.et",
            protocol: "https",
        },
    },
    plugins: [
        laravel({
            input: ["resources/css/app.css", "resources/js/app.jsx"],
            refresh: true,
        }),
        react(),
    ],
    build: {
        outDir: "public/build", // Output directory for built files
        manifest: true, // Ensure manifest is generated
        rollupOptions: {
            output: {
                assetFileNames: (assetInfo) => {
                    let extType = assetInfo.name.split(".").at(-1);
                    if (/png|jpe?g|svg|gif|tiff|bmp|ico|webp/.test(extType)) {
                        return `images/[name]-[hash][extname]`;
                    }
                    return `[ext]/[name]-[hash][extname]`;
                },
                chunkFileNames: "js/[name]-[hash].js",
                entryFileNames: "js/[name]-[hash].js",
            },
        },
    },
});
