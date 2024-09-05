import {defineConfig} from "vite";
import laravel from "laravel-vite-plugin";
import react from '@vitejs/plugin-react';

export default defineConfig(() => {
    return {
        base: '/Public/backend/build',
        plugins: [
            laravel({
                input: ['resources/js/backend/app.tsx'],
                refresh: [
                    'app/**/*.html',
                ],
                publicDirectory: 'www/Public',
                hotFile: 'www/Public/backend-hot',
                buildDirectory: 'backend/build',
            }),
            react(),
        ],
        css: {
            modules: true
        },
        server: {
            port: 5183
        }
    }
})