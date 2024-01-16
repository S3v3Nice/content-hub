import {defineConfig, loadEnv} from 'vite'
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'
import path from 'path'

export default defineConfig((configEnv) => {
    // Load .env file based on `mode` in the current working directory.
    // Set the third parameter to '' to load all $env regardless of the `VITE_` prefix.
    const env = loadEnv(configEnv.mode, process.cwd(), '')

    return {
        publicDir: 'public',
        resolve: {
            alias: [
                {find: '@', replacement: path.resolve(__dirname, 'resources', 'js')},
            ],
        },
        server: {
            host: '0.0.0.0',
            hmr: {
                clientPort: parseInt(env.VITE_PORT),
                host: env.APP_URL.replace(/^(https?:\/\/)/, ''),
                protocol: 'ws'
            },
            port: 5173,
            watch: {
                usePolling: true
            }
        },
        plugins: [
            laravel({
                input: ['resources/css/app.css', 'resources/js/app.js'],
                refresh: true,
            }),
            vue(),
        ],
    }
})