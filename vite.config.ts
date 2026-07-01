import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import type { Plugin } from 'vite';
import { defineConfig } from 'vite';

// Rename .mjs chunks/assets to .js so servers without an explicit .mjs MIME
// type mapping (e.g. the pdf.js worker) serve them with the correct type.
function mjsToJs(): Plugin {
    return {
        name: 'mjs-to-js',
        generateBundle(_options, bundle) {
            for (const key of Object.keys(bundle)) {
                if (key.endsWith('.mjs')) {
                    const entry = bundle[key] as { fileName: string };
                    const newKey = key.replace(/\.mjs$/, '.js');
                    entry.fileName = newKey;
                    bundle[newKey] = bundle[key];
                    delete bundle[key];
                }
            }
        },
    };
}

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/app.ts'],
            ssr: 'resources/js/ssr.ts',
            refresh: true,
        }),
        tailwindcss(),
        wayfinder({
            formVariants: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        mjsToJs(),
    ],
    server: {
        host: '0.0.0.0',
        port: 5173,
        hmr: { host: '192.168.1.5' },
    },
});

