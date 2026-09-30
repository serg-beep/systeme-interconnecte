import { onBeforeUnmount } from 'vue'
import api from '../api/client.js'

// Pas de WebSocket/SSE en place (php artisan serve ne tient pas les connexions
// persistantes, aucun driver de broadcast configuré) : on simule le temps réel
// par un sondage régulier du même endpoint que consultait l'ancien flux SSE.
const POLL_INTERVAL_MS = 20000

export function useRealtimeStream(handlers = {}) {
    let timer = null
    let stopped = false

    async function tick() {
        try {
            const { data } = await api.get('/stream/realtime')
            if (!stopped && data?.notifications && handlers.notifications) {
                handlers.notifications(data.notifications)
            }
        } catch {
            // Silencieux : on retentera au prochain sondage.
        }
    }

    function connect() {
        if (timer) return
        stopped = false
        tick()
        timer = setInterval(tick, POLL_INTERVAL_MS)
    }

    function disconnect() {
        stopped = true
        if (timer) {
            clearInterval(timer)
            timer = null
        }
    }

    onBeforeUnmount(disconnect)

    return { connect, disconnect }
}
