import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

const workspace = window.InventoryWorkspace;
const realtime = workspace?.realtime;

if (workspace?.userId && realtime?.enabled && realtime.key && realtime.host) {
    window.Pusher = Pusher;

    try {
        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: realtime.key,
            wsHost: realtime.host,
            wsPort: Number(realtime.port || 8080),
            wssPort: Number(realtime.port || 443),
            forceTLS: Boolean(realtime.forceTls),
            enabledTransports: realtime.forceTls ? ['ws', 'wss'] : ['ws'],
            authEndpoint: '/broadcasting/auth',
            auth: { headers: { 'X-CSRF-TOKEN': workspace.csrfToken } },
        });

        window.Echo.private(`App.Models.User.${workspace.userId}`)
            .listen('.inventory.notification', (event) => {
                window.dispatchEvent(new CustomEvent('inventory:notification', {
                    detail: event.notification,
                }));
            });

        window.InventoryWorkspace.realtimeConnected = true;
    } catch (error) {
        window.InventoryWorkspace.realtimeConnected = false;
        console.warn('No fue posible conectar las notificaciones en tiempo real.', error);
    }
}
