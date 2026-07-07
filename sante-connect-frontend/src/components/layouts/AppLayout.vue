<template>
  <div class="app-layout">
    <aside class="sidebar">
      <div class="sidebar-logo">
        <div class="logo-icon">
          <img v-if="!brandLogoError" :src="brandLogoSrc" alt="" @error="brandLogoError = true" />
          <svg v-else width="24" height="24" viewBox="0 0 24 24" fill="none">
            <path d="M12 4v16M4 12h16" stroke="white" stroke-width="2.5" stroke-linecap="round" />
          </svg>
        </div>
        <span class="logo-text">SanteConnect BF</span>
      </div>

      <div class="sidebar-entreprise">
        <div class="ent-avatar" :class="{ 'has-logo': auth.entreprise?.logo }">
          <img v-if="auth.entreprise?.logo" :src="logoUrl(auth.entreprise.logo)" alt="" />
          <span v-else>{{ initiales }}</span>
        </div>
        <div>
          <div class="ent-nom">{{ auth.entreprise?.nom }}</div>
          <div class="ent-type">{{ auth.entreprise?.type }}</div>
        </div>
      </div>

      <nav class="nav">
  <RouterLink to="/app/dashboard"     class="nav-item" active-class="active">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
    Tableau de bord
  </RouterLink>

  <RouterLink to="/app/alertes"       class="nav-item" active-class="active">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
    Alertes
  </RouterLink>

  <RouterLink to="/app/requetes"      class="nav-item" active-class="active">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
    Requêtes
  </RouterLink>

  <RouterLink to="/app/demandes"      class="nav-item" active-class="active">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
    Demandes
  </RouterLink>

  <RouterLink to="/app/conversations" class="nav-item" active-class="active">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
    Conversations
  </RouterLink>

  <RouterLink to="/app/partenaires"   class="nav-item" active-class="active">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
    Partenaires
  </RouterLink>

  <RouterLink to="/app/documents"     class="nav-item" active-class="active">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="13 2 13 9 20 9"/></svg>
    Documents
  </RouterLink>

  <RouterLink to="/app/annuaire"      class="nav-item" active-class="active">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
    Annuaire
  </RouterLink>

  <!-- Séparateur -->
  <div class="nav-separateur"></div>

  <RouterLink to="/app/notifications"  class="nav-item" active-class="active">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
    Notifications
    <span v-if="nbNonLues > 0" class="nav-badge">{{ nbNonLues }}</span>
  </RouterLink>

  <RouterLink to="/app/profil"         class="nav-item" active-class="active">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
    Mon profil
  </RouterLink>

  <!-- Admin seulement -->
  <RouterLink v-if="auth.hasRole('admin') || auth.hasRole('super_admin')"
    to="/app/actualites" class="nav-item nav-admin" active-class="active">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M19 20H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h10l6 6v8a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/></svg>
    Actualités Santé
  </RouterLink>

  <RouterLink v-if="auth.hasRole('admin') || auth.hasRole('super_admin')"
    to="/app/admin/users" class="nav-item nav-admin" active-class="active">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
    Admin — Utilisateurs
  </RouterLink>
</nav>

      <button class="btn-logout" @click="deconnexion">Deconnexion</button>
    </aside>

    <div class="contenu">
      <header class="header">
        <h1 class="titre-page">{{ titrePage }}</h1>

        <div class="header-actions">
          <div class="notifications">
            <button class="notif-btn" @click="showNotifications = !showNotifications">
              <span class="notif-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none">
                  <path d="M12 4a4 4 0 0 0-4 4v2.8c0 .7-.2 1.4-.6 2L6 15h12l-1.4-2.2c-.4-.6-.6-1.3-.6-2V8a4 4 0 0 0-4-4Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round" />
                  <path d="M10 18a2 2 0 0 0 4 0" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" />
                </svg>
              </span>
              <span v-if="nbNonLues > 0" class="notif-badge">{{ nbNonLues }}</span>
            </button>

            <div v-if="showNotifications" class="notif-panel">
              <div class="notif-panel-top">
                <strong>Notifications</strong>
                <button class="notif-link" @click="toutMarquerLues">Tout lire</button>
              </div>

              <div v-if="notifications.length === 0" class="notif-empty">Aucune notification</div>

              <div v-else class="notif-list">
                <div
                  v-for="notification in notifications"
                  :key="notification.id"
                  class="notif-item"
                  :class="{ unread: !notification.lu }"
                >
                  <div class="notif-title">{{ notification.titre }}</div>
                  <div class="notif-message">{{ notification.message }}</div>
                  <div class="notif-actions">
                    <button v-if="!notification.lu" class="notif-link" @click="marquerLue(notification.id)">Lire</button>
                    <button class="notif-link danger" @click="supprimerNotification(notification.id)">Supprimer</button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="user-info">
            <div class="user-avatar">{{ userInitiales }}</div>
            <span class="user-nom">{{ auth.user?.prenom || 'Utilisateur' }} {{ auth.user?.nom || '' }}</span>
          </div>
        </div>
      </header>

      <main class="main">
        <RouterView />
      </main>

      <button
        v-if="toast"
        class="toast-notification"
        type="button"
        @click="ouvrirToast"
      >
        <span class="toast-title">{{ toast.titre }}</span>
        <span class="toast-message">{{ toast.message }}</span>
      </button>
      <button
        v-if="errorToast"
        class="toast-notification error"
        type="button"
        @click="errorToast = ''"
      >
        <span class="toast-title">Action impossible</span>
        <span class="toast-message">{{ errorToast }}</span>
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { RouterLink, RouterView, useRoute, useRouter } from 'vue-router'
import { useRealtimeStream } from '../../composables/useRealtimeStream.js'
import { useAuthStore } from '../../stores/auth.js'
import { useNotifications } from '../../composables/useNotifications.js'
const {
    notifications,
    nbNonLues,
    toast,
    notify,
    loadNotifications,
    replaceNotifications,
    marquerNotificationLue,
    marquerToutesNotificationsLues,
    supprimerNotification: supprimerNotificationPartagee,
} = useNotifications()

const auth = useAuthStore()
const router = useRouter()
const route = useRoute()

const showNotifications = ref(false)
const errorToast = ref('')
const previousUnreadCount = ref(0)
const notificationsReady = ref(false)
const brandLogoError = ref(false)
const brandLogoSrc = '/logo-sante-connect.png'

const titres = {
    dashboard: 'Tableau de bord',
    alertes: 'Alertes',
    requetes: 'Requetes',
    demandes: 'Demandes',
    conversations: 'Conversations',
    partenaires: 'Partenaires',
    documents: 'Documents',
    annuaire: 'Annuaire',
    'actualites-admin': 'Actualités Santé',
    'admin-users': 'Utilisateurs',
}

const titrePage = computed(() => titres[route.name] || 'SanteConnect')

const initiales = computed(() => {
    const nom = auth.entreprise?.nom || ''
    return nom.split(' ').map(word => word[0]).join('').substring(0, 2).toUpperCase()
})

const userInitiales = computed(() => {
    const user = auth.user
    if (!user) return '?'
    return `${user.prenom?.[0] || ''}${user.nom?.[0] || ''}`.toUpperCase()
})

function logoUrl(logo) {
    if (!logo) return ''
    if (/^https?:\/\//.test(logo)) return logo

    const apiBase = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'
    return `${apiBase.replace(/\/api\/?$/, '')}${logo.startsWith('/') ? '' : '/'}${logo}`
}

async function chargerNotifications() {
    try {
        await loadNotifications()
        previousUnreadCount.value = nbNonLues.value
        notificationsReady.value = true
    } catch (e) {
        window.dispatchEvent(new CustomEvent('app-error', { detail: e?.response?.data?.message || 'Une erreur est survenue' }))
    }
}

function afficherErreur(event) {
    errorToast.value = event.detail || 'Une erreur est survenue'
    window.setTimeout(() => {
        errorToast.value = ''
    }, 5000)
}

async function marquerLue(id) {
    await marquerNotificationLue(id)
}

async function toutMarquerLues() {
    await marquerToutesNotificationsLues()
}

async function supprimerNotification(id) {
    await supprimerNotificationPartagee(id)
}

function ouvrirToast() {
    if (toast.value?.lien) {
        router.push(toast.value.lien)
    }
    toast.value = null
}

async function deconnexion() {
    await auth.logout()
    router.push('/login')
}

const { connect } = useRealtimeStream({
    notifications: payload => {
        const items = payload.items || []
        const count = payload.unread_count || 0

        replaceNotifications(items, count)

        if (notificationsReady.value && count > previousUnreadCount.value) {
            const nouvelle = items.find(item => !item.lu) || items[0]
            notify(nouvelle)
        }

        previousUnreadCount.value = count
        notificationsReady.value = true
    },
})

onMounted(() => {
    window.addEventListener('app-error', afficherErreur)
    chargerNotifications()
    connect()
})

onBeforeUnmount(() => {
    window.removeEventListener('app-error', afficherErreur)
})
</script>

<style scoped>
.app-layout { display: flex; min-height: 100vh; height: 100vh; overflow: hidden; background: radial-gradient(circle at top left, #e8f7f4 0, var(--color-bg) 310px); }
.sidebar { width: 244px; background: linear-gradient(180deg, var(--color-sidebar) 0%, var(--color-sidebar-2) 100%); display: flex; flex-direction: column; padding: 0; flex-shrink: 0; box-shadow: 12px 0 30px rgba(15, 23, 42, 0.12); min-height: 100%; min-height: 0; }
.sidebar-logo { display: flex; align-items: center; gap: 10px; padding: 20px 18px; border-bottom: 1px solid rgba(255,255,255,0.08); }
.logo-icon { width: 34px; height: 34px; background: linear-gradient(135deg, var(--color-primary), var(--color-accent)); border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 10px 22px rgba(15, 118, 110, 0.28); overflow: hidden; }
.logo-icon img { width: 100%; height: 100%; object-fit: contain; padding: 4px; box-sizing: border-box; background: white; }
.logo-text { font-size: 13px; font-weight: 600; color: white; }
.sidebar-entreprise { display: flex; align-items: center; gap: 10px; padding: 14px 16px; border-bottom: 1px solid rgba(255,255,255,0.08); background: rgba(255, 255, 255, 0.035); }
.ent-avatar { width: 34px; height: 34px; background: rgba(15, 118, 110, 0.95); border-radius: 10px; color: white; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 600; flex-shrink: 0; overflow: hidden; }
.ent-avatar.has-logo { background: white; border: 1px solid rgba(255,255,255,0.18); }
.ent-avatar img { width: 100%; height: 100%; object-fit: contain; padding: 4px; box-sizing: border-box; }
.ent-nom { font-size: 12px; font-weight: 500; color: white; }
.ent-type { font-size: 11px; color: rgba(255,255,255,0.58); text-transform: capitalize; }
.nav { flex: 1; padding: 8px; display: flex; flex-direction: column; gap: 2px; overflow-y: auto; min-height: 0; scrollbar-width: none; -ms-overflow-style: none; }
.nav::-webkit-scrollbar { display: none; }
.nav-item { display: flex; align-items: center; gap: 9px; padding: 10px 12px; border-radius: 10px; color: rgba(255,255,255,0.68); text-decoration: none; font-size: 13px; transition: all 0.18s ease; }
.nav-item:hover { background: rgba(255,255,255,0.08); color: white; transform: translateX(2px); }
.nav-item.active { background: rgba(15, 118, 110, 0.98); color: white; box-shadow: inset 3px 0 0 rgba(255,255,255,0.38), 0 10px 22px rgba(15, 118, 110, 0.18); }
.btn-logout { margin: 12px 8px; padding: 10px 12px; background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; color: rgba(255,255,255,0.62); cursor: pointer; font-size: 13px; text-align: left; transition: all 0.15s; }
.btn-logout:hover { background: rgba(220,38,38,0.14); color: #fecaca; border-color: rgba(248, 113, 113, 0.28); }
.contenu { flex: 1; display: flex; flex-direction: column; min-width: 0; min-height: 0; }
.header { background: rgba(255,255,255,0.88); backdrop-filter: blur(12px); border-bottom: 1px solid var(--color-border); padding: 0 28px; height: 64px; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 20; }
.titre-page { font-size: 16px; font-weight: 700; color: var(--color-text-primary); margin: 0; }
.header-actions { display: flex; align-items: center; gap: 16px; }
.notifications { position: relative; }
.notif-btn { position: relative; border: 1px solid var(--color-border); background: var(--color-surface); border-radius: 12px; width: 42px; height: 42px; cursor: pointer; box-shadow: var(--shadow-sm); }
.notif-icon { width: 18px; height: 18px; display: inline-flex; color: var(--color-text-primary); }
.notif-icon svg { width: 18px; height: 18px; }
.notif-badge { position: absolute; top: -5px; right: -5px; min-width: 20px; height: 20px; border-radius: 999px; background: var(--color-danger); color: white; font-size: 11px; font-weight: 700; display: flex; align-items: center; justify-content: center; padding: 0 5px; }
.notif-panel { position: absolute; right: 0; top: 50px; width: 340px; background: var(--color-surface); border: 1px solid var(--color-border); border-radius: 14px; box-shadow: var(--shadow); z-index: 30; overflow: hidden; }
.notif-panel-top { display: flex; align-items: center; justify-content: space-between; padding: 14px 16px; border-bottom: 1px solid var(--color-border); }
.notif-empty { padding: 18px 16px; font-size: 13px; color: var(--color-text-muted); }
.notif-list { max-height: 360px; overflow-y: auto; }
.notif-item { padding: 12px 16px; border-bottom: 1px solid var(--color-surface-muted); }
.notif-item.unread { background: #f0fdfa; }
.notif-title { font-size: 13px; font-weight: 600; color: var(--color-text-primary); margin-bottom: 4px; }
.notif-message { font-size: 12px; color: var(--color-text-secondary); line-height: 1.4; }
.notif-actions { display: flex; gap: 12px; margin-top: 8px; }
.notif-link { background: none; border: none; padding: 0; color: var(--color-accent); font-size: 12px; cursor: pointer; }
.notif-link.danger { color: var(--color-danger); }
.user-info { display: flex; align-items: center; gap: 10px; }
.user-avatar { width: 34px; height: 34px; border-radius: 50%; background: linear-gradient(135deg, var(--color-primary), var(--color-accent)); color: white; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 600; }
.user-nom { font-size: 13px; color: var(--color-text-secondary); }
.main { flex: 1; padding: 28px; overflow-y: auto; min-height: 0; }
.nav-separateur { height: 1px; background: rgba(255,255,255,0.08); margin: 8px 0; }
.nav-badge { margin-left: auto; background: var(--color-danger); color: white; font-size: 10px; font-weight: 600; padding: 1px 6px; border-radius: 10px; }
.nav-admin { border: 1px solid rgba(255,255,255,0.1); margin-top: 4px; }
.nav-admin:hover { background: rgba(220,38,38,0.14) !important; color: #fecaca !important; border-color: rgba(248, 113, 113, 0.28); }
.toast-notification { position: fixed; right: 24px; bottom: 24px; width: min(360px, calc(100vw - 32px)); background: var(--color-surface); border: 2px solid var(--color-primary); border-radius: 18px; box-shadow: var(--shadow); padding: 14px 16px; z-index: 80; text-align: left; cursor: pointer; display: flex; flex-direction: column; gap: 4px; }
.toast-title { color: var(--color-text-primary); font-size: 13px; font-weight: 700; }
.toast-message { color: var(--color-text-secondary); font-size: 12px; line-height: 1.4; }
.toast-notification.error { border-color: var(--color-danger); }

@media (max-width: 1100px) {
  .app-layout { flex-direction: column; }
  .sidebar {
    width: 100%;
    min-height: auto;
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
  }
  .sidebar-logo,
  .sidebar-entreprise {
    padding: 14px 18px;
  }
  .nav {
    flex-direction: row;
    overflow-x: auto;
    gap: 6px;
    padding: 8px 14px 12px;
    scrollbar-width: thin;
  }
  .nav-item {
    flex: 0 0 auto;
    white-space: nowrap;
    padding: 9px 11px;
  }
  .nav-item:hover { transform: none; }
  .nav-separateur { display: none; }
  .btn-logout {
    margin: 0 14px 14px;
    width: max-content;
  }
  .header {
    height: auto;
    min-height: 60px;
    padding: 12px 20px;
    gap: 14px;
  }
  .main { padding: 20px; }
}

@media (max-width: 700px) {
  .sidebar-logo { justify-content: center; }
  .sidebar-entreprise { display: none; }
  .header {
    align-items: flex-start;
    flex-direction: column;
  }
  .header-actions {
    width: 100%;
    justify-content: space-between;
  }
  .user-nom {
    max-width: 180px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }
  .notif-panel {
    position: fixed;
    left: 12px;
    right: 12px;
    top: 124px;
    width: auto;
  }
  .main { padding: 16px; }
}
</style>
