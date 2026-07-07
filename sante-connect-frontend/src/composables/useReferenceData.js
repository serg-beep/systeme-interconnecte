import { ref } from 'vue'
import api from '../api/client.js'

const entreprises = ref([])
const roles = ref([])
const permissions = ref([])

const initialized = {
    entreprises: false,
    roles: false,
    permissions: false,
}

const pending = {
    entreprises: null,
    roles: null,
    permissions: null,
}

async function loadCached(key, target, url, { force = false } = {}) {
    if (pending[key]) return pending[key]
    if (initialized[key] && !force) return target.value

    pending[key] = api.get(url)
        .then(({ data }) => {
            target.value = data?.data || data || []
            initialized[key] = true
            return target.value
        })
        .finally(() => {
            pending[key] = null
        })

    return pending[key]
}

export function useReferenceData() {
    return {
        entreprises,
        roles,
        permissions,

        // ⚡ CHARGEMENT À LA DEMANDE UNIQUEMENT
        loadEntreprises: (options) =>
            loadCached('entreprises', entreprises, '/entreprises', options),

        loadRoles: (options) =>
            loadCached('roles', roles, '/roles', options),

        loadPermissions: (options) =>
            loadCached('permissions', permissions, '/permissions', options),

        // ❌ NE PAS CHARGER AUTOMATIQUEMENT
        loadAdminReferences: async (options) => {
            const promises = []

            if (!initialized.roles)
                promises.push(loadCached('roles', roles, '/roles', options))

            if (!initialized.permissions)
                promises.push(loadCached('permissions', permissions, '/permissions', options))

            return Promise.all(promises)
        },
    }
}
