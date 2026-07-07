import { ref } from 'vue'
import api from '../api/client.js'

const notifications = ref([])
const nbNonLues = ref(0)
const toast = ref(null)
const chargement = ref(false)

let toastTimer = null
let initialized = false
let pendingLoad = null

function normalizeList(data) {
    return data?.data || data || []
}

function notify(notification) {
    const payload = typeof notification === 'string'
        ? { titre: 'Notification', message: notification }
        : notification

    toast.value = {
        titre: payload?.titre || 'Nouvelle notification',
        message: payload?.message || '',
        lien: payload?.lien || null,
    }

    clearTimeout(toastTimer)
    toastTimer = setTimeout(() => {
        toast.value = null
    }, 5000)
}

function setUnreadCount(count) {
    nbNonLues.value = Number(count || 0)
}

async function loadNotifications({ force = false } = {}) {
    if (pendingLoad) {
        return pendingLoad
    }

    if (initialized && !force) {
        return notifications.value
    }

    chargement.value = true
    pendingLoad = Promise.all([
        api.get('/notifications'),
        api.get('/notifications/non-lues').catch(() => null),
    ])
        .then(([liste, nonLues]) => {
            const items = normalizeList(liste.data)
            notifications.value = items
            setUnreadCount(nonLues?.data?.length ?? items.filter(item => !item.lu).length)
            initialized = true
            return items
        })
        .finally(() => {
            chargement.value = false
            pendingLoad = null
        })

    return pendingLoad
}

function replaceNotifications(items, unreadCount = null) {
    notifications.value = items || []
    setUnreadCount(unreadCount ?? notifications.value.filter(item => !item.lu).length)
    initialized = true
}

async function marquerNotificationLue(id) {
    const notification = notifications.value.find(item => item.id === id)
    const wasUnread = notification && !notification.lu

    if (notification) {
        notification.lu = true
    }

    if (wasUnread) {
        setUnreadCount(Math.max(0, nbNonLues.value - 1))
    }

    try {
        await api.post(`/notifications/${id}/lue`)
    } catch (error) {
        if (notification && wasUnread) {
            notification.lu = false
            setUnreadCount(nbNonLues.value + 1)
        }

        throw error
    }
}

async function marquerToutesNotificationsLues() {
    const unreadIds = notifications.value
        .filter(item => !item.lu)
        .map(item => item.id)

    notifications.value.forEach(item => {
        item.lu = true
    })
    setUnreadCount(0)

    try {
        await api.post('/notifications/toutes-lues')
    } catch (error) {
        notifications.value.forEach(item => {
            if (unreadIds.includes(item.id)) {
                item.lu = false
            }
        })
        setUnreadCount(unreadIds.length)
        throw error
    }
}

async function supprimerNotification(id) {
    const index = notifications.value.findIndex(item => item.id === id)
    const deleted = index >= 0 ? notifications.value[index] : null

    if (index >= 0) {
        notifications.value.splice(index, 1)
    }

    if (deleted && !deleted.lu) {
        setUnreadCount(Math.max(0, nbNonLues.value - 1))
    }

    try {
        await api.delete(`/notifications/${id}`)
    } catch (error) {
        if (deleted) {
            notifications.value.splice(index, 0, deleted)

            if (!deleted.lu) {
                setUnreadCount(nbNonLues.value + 1)
            }
        }

        throw error
    }
}

export function useNotifications() {
    return {
        notifications,
        nbNonLues,
        toast,
        chargement,
        notify,
        setUnreadCount,
        loadNotifications,
        replaceNotifications,
        marquerNotificationLue,
        marquerToutesNotificationsLues,
        supprimerNotification,
    }
}
