import {defineConfig} from "vite";
import laravel from "laravel-vite-plugin";
import react from '@vitejs/plugin-react';
import {visualizer} from 'rollup-plugin-visualizer'

export default defineConfig(() => {
    return {
        base: '/Public/backend/build',
        build: {
            sourcemap: true,
            rollupOptions: {
                plugins: [visualizer()],
                output: {
                    manualChunks: {
                        react: ['react', 'react-dom'],
                        axios: ['axios'],
                    }
                }
            }
        },
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
            modules: true,
            preprocessorOptions: {
                scss: {
                    api: 'modern-compiler',
                }
            }
        },
        server: {
            port: 5183
        }
    }
})