import {defineConfig} from "vite";
import laravel from "laravel-vite-plugin";
import react from '@vitejs/plugin-react';

export default () => defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/frontend/app.tsx'],
            refresh: [
                'app/**/*.html',
            ],
            publicDirectory: 'www/Public',
            buildDirectory: 'build',
            hotFile: 'www/Public/hot',
            ssr: 'resources/js/frontend/ssr.tsx',
            ssrOutputDirectory: 'lara/bootstrap/ssr'
        }),
        react(),
    ],
    css: {
        modules: true
    },
    base: '/Public/build',
    server: {
        port: 5173,
    }
})