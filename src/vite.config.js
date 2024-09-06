import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';

export default defineConfig({
    server: {
        host: '0.0.0.0',
        port: 5173,
        watch: {
          usePolling: true,
        },
        hmr: {
          host: 'localhost',
        },
      },
    plugins: [
        laravel({
            input:  [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
        react(),
    ],
    build: {
        outDir: 'public/build', // Output directory for built files
        manifest: true, // Ensure manifest is generated
    },
});
