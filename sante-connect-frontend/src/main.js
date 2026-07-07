import { createApp } from 'vue'
import { createPinia } from 'pinia'
import router, { prefetchAppRoutes } from './router'
import App from './App.vue'
import './style.css'
import { useAuthStore } from './stores/auth.js'
import { useNotifications } from './composables/useNotifications.js'
import { useReferenceData } from './composables/useReferenceData.js'

const app = createApp(App)
const pinia = createPinia()
app.use(pinia)
app.use(router)

const auth = useAuthStore(pinia)
if (auth.token && (!auth.user || !auth.user.roles?.length)) {
    auth.fetchMe().catch(() => auth.logout())
}

app.mount('#app')

// Précharger toutes les routes dès le démarrage (connecté ou non)
const prefetchAll = () => {
    prefetchAppRoutes()

    if (auth.token) {
        const { loadNotifications } = useNotifications()
        const { loadEntreprises, loadRoles, loadPermissions } = useReferenceData()

        loadNotifications().catch(() => {})
        loadEntreprises().catch(() => {})
        loadRoles().catch(() => {})
        loadPermissions().catch(() => {})
    }
}

if ('requestIdleCallback' in window) {
    window.requestIdleCallback(prefetchAll, { timeout: 1000 })
} else {
    window.setTimeout(prefetchAll, 300)
}
