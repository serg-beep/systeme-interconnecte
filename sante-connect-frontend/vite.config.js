import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import path from 'path'

export default defineConfig({
    server: {
        host: '0.0.0.0',
        port: 5173,
    },
    plugins: [vue()],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, './src'),
        },
    },
    build: {
        rollupOptions: {
            output: {
                manualChunks(id) {
                    if (id.includes('node_modules') && /[\\/](vue|vue-router|pinia)[\\/]/.test(id)) {
                        return 'vendor-vue'
                    }
                },
            },
        },
        chunkSizeWarningLimit: 600,
    },
})
