import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';
import path from 'path';
import { compression } from 'vite-plugin-compression2';

export default defineConfig(({ mode }) => {
    // Load environment variables
    const env = loadEnv(mode, process.cwd(), '');
    
    return {
        // CDN base URL configuration
        base: env.ASSET_URL || '/',
        
        plugins: [
            laravel({
                input: ['resources/css/app.css', 'resources/js/app.js'],
                refresh: true,
            }),
            tailwindcss(),
            react(),
            // Gzip compression for production
            compression({
                algorithm: 'gzip',
                exclude: [/\.(br)$/, /\.(gz)$/],
            }),
            // Brotli compression for production
            compression({
                algorithm: 'brotliCompress',
                exclude: [/\.(br)$/, /\.(gz)$/],
            }),
        ],
        resolve: {
            alias: {
                '@': path.resolve(__dirname, './resources/js'),
            },
        },
        server: {
            watch: {
                ignored: ['**/storage/framework/views/**'],
            },
        },
        build: {
            // Production optimizations
            minify: 'terser',
            terserOptions: {
                compress: {
                    drop_console: true, // Remove console.log in production
                    drop_debugger: true,
                    pure_funcs: ['console.log', 'console.info', 'console.debug'],
                },
            },
            // Code splitting for better caching
            rollupOptions: {
                output: {
                    // Asset versioning with content hash for cache busting
                    entryFileNames: 'assets/[name]-[hash].js',
                    chunkFileNames: 'assets/[name]-[hash].js',
                    assetFileNames: 'assets/[name]-[hash].[ext]',
                    manualChunks: {
                        // Separate vendor chunks for better caching
                        vendor: ['axios'],
                    },
                },
            },
            // Asset optimization
            assetsInlineLimit: 4096, // Inline assets smaller than 4kb
            cssCodeSplit: true, // Split CSS for better caching
            sourcemap: false, // Disable sourcemaps in production
            // Chunk size warnings
            chunkSizeWarningLimit: 500,
            // Generate manifest for asset versioning
            manifest: true,
        },
        // CSS optimization
        css: {
            devSourcemap: false,
        },
    };
});
