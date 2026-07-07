import { onBeforeUnmount } from 'vue'

// Realtime désactivé — php artisan serve ne supporte pas les connexions persistantes.
// Les données se mettent à jour via les appels API normaux de chaque page.

export function useRealtimeStream(handlers = {}) {
    return {
        connect: () => {},
        disconnect: () => {},
    }
}
