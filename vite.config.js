import path from 'path';
import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import { VitePWA } from 'vite-plugin-pwa';

export default defineConfig({
  base: '/',
  resolve: {
    alias: {
      '@base': path.resolve(__dirname, './'),
      '@lang': path.resolve(__dirname, './lang'),
      '@r': path.resolve(__dirname, './resources'),
      '@': path.resolve(__dirname, './resources/js'),
      'ziggy-js': path.resolve('vendor/tightenco/ziggy'),
    },
  },
  build: {
    outDir: './public',
    emptyOutDir: false,
    sourcemap: process.env.NODE_ENV == 'development',
  },
  plugins: [
    laravel({
      input: 'resources/js/app.js',
      ssr: 'resources/js/ssr.js',
      refresh: true,
    }),
    vue({
      template: {
        transformAssetUrls: {
          base: null,
          includeAbsolute: false,
        },
      },
    }),
    VitePWA({
      strategies: 'generateSW', // 'generateSW' | 'injectManifest'
      injectRegister: 'auto',
      registerType: 'prompt',
      workbox: {
        globPatterns: ['**/*/*.{js,css,html,ico,png,svg}'],
      },
      manifest: {
        start_url: '.',
        short_name: 'SIM',
        name: 'Invoice Manager',
        display: 'fullscreen',
        background_color: '#111827',
        description: 'Simple Invoice Manager by Tecdiary',
        icons: [
          {
            src: '/pwa-64x64.png',
            sizes: '64x64',
            type: 'image/png',
          },
          {
            src: '/pwa-192x192.png',
            sizes: '192x192',
            type: 'image/png',
          },
          {
            src: '/pwa-512x512.png',
            sizes: '512x512',
            type: 'image/png',
            purpose: 'any',
          },
          {
            src: '/maskable-icon-512x512.png',
            sizes: '512x512',
            type: 'image/png',
            purpose: 'maskable',
          },
        ],
      },
    }),
  ],
});
