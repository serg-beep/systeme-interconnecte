import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api, { setAuthToken } from '../api/client.js'

function readJsonStorage(key, fallback = null) {
    try {
        return JSON.parse(localStorage.getItem(key) || JSON.stringify(fallback))
    } catch {
        localStorage.removeItem(key)
        return fallback
    }
}

export const useAuthStore = defineStore('auth', () => {
    const user  = ref(readJsonStorage('user'))
    const token = ref(localStorage.getItem('token') || null)

    const isAuthenticated = computed(() => !!token.value)
    const entreprise      = computed(() => user.value?.entreprise)
    const roles           = computed(() => user.value?.roles || [])
    const roleNames       = computed(() => new Set(roles.value.map(role => role.nom)))
    const permissionNames = computed(() => {
        const names = new Set((user.value?.permissions || []).map(permission => permission.nom))

        roles.value.forEach(role => {
            const permissions = role.permissions || []

            permissions.forEach(permission => {
                names.add(permission.nom)
            })
        })

        return names
    })

    function persistSession(nextToken, nextUser) {
        token.value = nextToken
        user.value = nextUser
        setAuthToken(nextToken)

        if (nextToken) {
            localStorage.setItem('token', nextToken)
        } else {
            localStorage.removeItem('token')
        }

        if (nextUser) {
            localStorage.setItem('user', JSON.stringify(nextUser))
        } else {
            localStorage.removeItem('user')
        }
    }

    function hasRole(role) {
        return roleNames.value.has(role)
    }

    function hasAnyRole(...names) {
        return names.some(hasRole)
    }

    function hasPermission(permission) {
        return permissionNames.value.has(permission)
    }

    async function login(credentials) {
        const { data } = await api.post('/auth/login', credentials)
        persistSession(data.token, data.user)
        return data
    }

    async function logout() {
        try {
            await api.post('/auth/logout')
        } finally {
            persistSession(null, null)
        }
    }

    async function fetchMe() {
        const { data } = await api.get('/auth/me')
        user.value = data
        localStorage.setItem('user', JSON.stringify(data))
        return data
    }

    return {
        user, token, isAuthenticated,
        entreprise, roles, hasRole, hasAnyRole, hasPermission,
        login, logout, fetchMe
    }
})
