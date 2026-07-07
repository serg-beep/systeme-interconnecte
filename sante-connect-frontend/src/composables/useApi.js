import { ref } from 'vue'
import api from '@/api/client'

export function useApi() {
    const loading = ref(false)
    const error   = ref(null)

    async function request(method, url, data = null) {
        loading.value = true
        error.value   = null
        try {
            const response = await api[method](url, data)
            return response.data
        } catch (e) {
            error.value = e.response?.data?.message || 'Erreur réseau'
            throw e
        } finally {
            loading.value = false
        }
    }

    return {
        loading, error,
        get:    (url)       => request('get', url),
        post:   (url, data) => request('post', url, data),
        put:    (url, data) => request('put', url, data),
        delete: (url)       => request('delete', url),
    }
}
