import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    // server: {
    //     host: '0.0.0.0', // Allow access from any device on the network
    //     hmr: {
    //         host: '192.168.200.27', // Replace with your PC's LAN IP address
    //     },
    // },
});
