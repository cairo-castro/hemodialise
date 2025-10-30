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
            sourcemap: false, // Disable source maps to avoid warnings
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
                },
                '/login': {
                    target: 'http://localhost:8000',
                    changeOrigin: true,
                    secure: false,
                },
                '/logout': {
                    target: 'http://localhost:8000',
                    changeOrigin: true,
                    secure: false,
                }
            }
        };

        // Optimizations for development mode
        config.optimizeDeps = {
            include: ['vue', 'vue-router', '@ionic/vue', '@ionic/vue-router'],
            exclude: []
        };
    }

    // Desktop-specific configuration
    else if (isDesktop) {
        config.plugins.push(
            laravel({
                input: [
                    'resources/js/desktop.js',
                ],
                refresh: true,
                buildDirectory: 'desktop',
            })
        );

        config.build = {
            outDir: 'public/desktop',
            emptyOutDir: true,
            sourcemap: false,
            rollupOptions: {
                output: {
                    manualChunks: {
                        'heroicons': ['@heroicons/vue'],
                        'headlessui': ['@headlessui/vue'],
                        'vendor': ['vue', 'vue-router']
                    }
                }
            }
        };

        config.optimizeDeps = {
            include: ['vue', 'vue-router', '@heroicons/vue/24/outline', '@headlessui/vue'],
            exclude: []
        };
    }

    // Default configuration (development and Filament)
    else {
        config.plugins.push(
            laravel({
                input: [
                    'resources/css/app.css',
                    'resources/js/app.js',
                    // NOTE: login.js intentionally excluded to prevent Vue.js conflict with Filament Livewire
                    // If /login page is needed, it should load login.js via separate build or inline
                ],
                refresh: true,
            })
        );
    }

    return config;
});