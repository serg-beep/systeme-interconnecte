export const API_BASE = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'

let authToken = localStorage.getItem('token') || null

function normalizeHeaders(headers = {}) {
    return Object.fromEntries(
        Object.entries(headers).filter(([, value]) => value !== undefined && value !== null)
    )
}

function buildUrl(url, params) {
    const endpoint = /^https?:\/\//.test(url)
        ? new URL(url)
        : new URL(`${API_BASE.replace(/\/$/, '')}/${url.replace(/^\//, '')}`)

    Object.entries(params || {}).forEach(([key, value]) => {
        if (value !== undefined && value !== null && value !== '') {
            endpoint.searchParams.set(key, value)
        }
    })

    return endpoint.toString()
}

async function parseResponse(response) {
    const contentType = response.headers.get('content-type') || ''

    if (response.status === 204) {
        return null
    }

    if (contentType.includes('application/json')) {
        return response.json()
    }

    return response.text()
}

function createApiError(response, data) {
    const error = new Error(data?.message || response.statusText || 'Erreur reseau')
    error.response = { status: response.status, data }
    return error
}

export function setAuthToken(token) {
    authToken = token || null

    if (authToken) {
        api.defaults.headers.common.Authorization = `Bearer ${authToken}`
    } else {
        delete api.defaults.headers.common.Authorization
    }
}

async function request(method, url, data = null, config = {}) {
    const isFormData = data instanceof FormData
    const headers = normalizeHeaders({
        Accept: 'application/json',
        ...(!isFormData ? { 'Content-Type': 'application/json' } : {}),
        ...api.defaults.headers.common,
        ...(config.headers || {}),
    })

    if (isFormData) {
        delete headers['Content-Type']
    }

    const options = {
        method,
        headers,
        credentials: 'include',
        signal: config.signal,
    }

    if (data && method !== 'GET') {
        options.body = isFormData ? data : JSON.stringify(data)
    }

    try {
        const response = await fetch(buildUrl(url, config.params), options)
        const responseData = await parseResponse(response)

        if (!response.ok) {
            if (response.status === 401) {
                setAuthToken(null)
                localStorage.removeItem('token')
                localStorage.removeItem('user')
                window.location.href = '/login'
            }

            throw createApiError(response, responseData)
        }

        config.onUploadProgress?.({ loaded: 1, total: 1 })

        return {
            data: responseData,
            status: response.status,
            headers: response.headers,
        }
    } catch (error) {
        if (!error.response) {
            error.response = { status: 0, data: { message: 'Erreur reseau' } }
        }

        throw error
    }
}

const api = {
    defaults: {
        headers: {
            common: {},
        },
    },
    get: (url, config) => request('GET', url, null, config),
    post: (url, data, config) => request('POST', url, data, config),
    put: (url, data, config) => request('PUT', url, data, config),
    delete: (url, config) => request('DELETE', url, null, config),
}

setAuthToken(authToken)

export default api
