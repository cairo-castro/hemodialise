import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import legacy from '@vitejs/plugin-legacy';
import tailwindcss from '@tailwindcss/vite';
import path from 'path';

export default defineConfig(({ mode }) => {
    const isMobile = mode === 'mobile';
    const isDesktop = mode === 'desktop';

    // Base configuration
    const config = {
        plugins: [
            vue({
                template: {
                    transformAssetUrls: {
                        base: null,
                        includeAbsolute: false,
                    },
                },
            }),
            tailwindcss(),
        ],
        resolve: {
            alias: {
                '@': path.resolve(__dirname, 'resources/js'),
                '@mobile': path.resolve(__dirname, 'resources/js/mobile'),
                '@desktop': path.resolve(__dirname, 'resources/js/desktop'),
                '@shared': path.resolve(__dirname, 'resources/js/shared'),
            },
        },
    };

    // Mobile-specific configuration
    if (isMobile) {
        config.plugins.push(
            laravel({
                input: [
                    'resources/js/mobile/main.ts',
                    'resources/css/app.css',
    
                ],
                refresh: true,
                buildDirectory: 'mobile-assets',
            }),
            legacy({
                targets: ['defaults', 'not IE 11']
            })
        );

        config.build = {
            outDir: 'public/mobile-assets',
            emptyOutDir: true,
            rollupOptions: {
                output: {
                    manualChunks: {
                        'ionic': ['@ionic/vue', '@ionic/vue-router'],
                        'vendor': ['vue', 'vue-router']
                    }
                }
            }
        };

        config.server = {
            proxy: {
                '/api': {
                    target: 'http://localhost:8000',
                    changeOrigin: true,
                    secure: false,
                }
            }
        };
    }

    // Desktop-specific configuration
    else if (isDesktop) {
        config.plugins.push(
            laravel({
                input: [
                    'resources/css/app.css',
                    'resources/js/app.js',
                    // NOTE: login.js removed - loaded only on /login page
                ],
                refresh: true,
                buildDirectory: 'desktop',
            })
        );
    }

    // Default configuration (development and Filament)
    else {
        config.plugins.push(
            laravel({
                input: [
                    'resources/css/app.css',
                    'resources/js/app.js',
                    'resources/js/auth/login.js', // Needed for /login page
                ],
                refresh: true,
            })
        );
    }

    return config;
});