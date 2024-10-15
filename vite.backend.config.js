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
                        antd: ['antd', '@ant-design/icons'],
                        axios: ['axios'],
                        'pro-components': ['@ant-design/pro-components'],
                        'pro-layout': ['@ant-design/pro-layout'],
                        'pro-table': ['@ant-design/pro-table'],
                        'pro-form': ['@ant-design/pro-form'],
                        'pro-field': ['@ant-design/pro-field'],
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