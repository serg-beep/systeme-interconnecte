<template>
  <div>
    <div class="page-header">
      <h2 class="titre">Notifications</h2>
      <button v-if="notifications.length > 0" class="btn-tout-lire" @click="toutMarquerLues">
        Tout marquer comme lu
      </button>
    </div>

    <!-- Filtres -->
    <div class="filtres">
      <button v-for="f in filtres" :key="f.val"
        class="filtre-btn" :class="{ active: filtre === f.val }"
        @click="filtre = f.val">
        {{ f.label }}
        <span v-if="f.val === 'non_lues' && nbNonLues > 0" class="badge-nb">{{ nbNonLues }}</span>
      </button>
    </div>

    <div v-if="chargement" class="vide">Chargement...</div>
    <div v-else-if="notifications.length === 0" class="vide">
      <div class="vide-icone">🔔</div>
      <div>Aucune notification</div>
    </div>
    <template v-else>
    <div class="liste">
      <div v-for="n in notifications" :key="n.id"
        class="notif-card" :class="{ 'non-lue': !n.lu }"
        @click="ouvrirNotif(n)">
        <div class="notif-icone" :class="n.type">
          <svg v-if="n.type === 'alerte'" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
          <svg v-else-if="n.type === 'demande'" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/></svg>
          <svg v-else-if="n.type === 'message'" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
          <svg v-else-if="n.type === 'partenariat'" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
          <svg v-else width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        </div>
        <div class="notif-content">
          <div class="notif-titre">{{ n.titre }}</div>
          <div class="notif-message">{{ n.message }}</div>
          <div class="notif-date">{{ formatDate(n.created_at) }}</div>
        </div>
        <div class="notif-droite">
          <span v-if="!n.lu" class="point-non-lu"></span>
          <span class="badge-type" :class="n.type">{{ n.type }}</span>
        </div>
      </div>
    </div>
    <UiPagination :meta="meta" @change="changerPage" />
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../api/client.js'
import { useNotifications } from '../../composables/useNotifications.js'
import UiPagination from '../../components/ui/UiPagination.vue'

const router      = useRouter()
const filtre      = ref('toutes')
const { nbNonLues } = useNotifications()

const notifications = ref([])
const meta = ref(null)
const page = ref(1)
const chargement = ref(true)

const filtres = [
    { val: 'toutes',   label: 'Toutes' },
    { val: 'non_lues', label: 'Non lues' },
    { val: 'alerte',   label: 'Alertes' },
    { val: 'demande',  label: 'Demandes' },
    { val: 'message',  label: 'Messages' },
    { val: 'partenariat', label: 'Partenariats' },
]

function formatDate(d) {
    const date = new Date(d)
    const maintenant = new Date()
    const diff = maintenant - date
    const minutes = Math.floor(diff / 60000)
    const heures  = Math.floor(diff / 3600000)
    const jours   = Math.floor(diff / 86400000)
    if (minutes < 1)  return 'À l\'instant'
    if (minutes < 60) return `Il y a ${minutes} min`
    if (heures < 24)  return `Il y a ${heures}h`
    if (jours < 7)    return `Il y a ${jours}j`
    return date.toLocaleDateString('fr-FR', { day: '2-digit', month: 'short' })
}

function filtreParams() {
    if (filtre.value === 'non_lues') return { lu: 0 }
    if (filtre.value === 'toutes')   return {}
    return { type: filtre.value }
}

async function charger() {
    chargement.value = notifications.value.length === 0
    try {
        const { data } = await api.get('/notifications', {
            params: { paginate: 1, page: page.value, per_page: 15, ...filtreParams() },
        })
        notifications.value = data.data ?? data
        meta.value = data.data ? data : null
    } catch (e) {
        window.dispatchEvent(new CustomEvent('app-error', { detail: e?.response?.data?.message || 'Une erreur est survenue' }))
    } finally {
        chargement.value = false
    }
}

function changerPage(p) {
    page.value = p
    charger()
}

watch(filtre, () => {
    page.value = 1
    charger()
})

async function ouvrirNotif(n) {
    if (!n.lien && n.lu) return
    const wasUnread = !n.lu
    n.lu = true
    if (wasUnread) nbNonLues.value = Math.max(0, nbNonLues.value - 1)

    try {
        await api.post(`/notifications/${n.id}/lue`)
    } catch (e) {
        if (wasUnread) { n.lu = false; nbNonLues.value += 1 }
    }
    if (n.lien) router.push(n.lien)
}

async function toutMarquerLues() {
    const previous = notifications.value.map(n => n.lu)
    notifications.value.forEach(n => { n.lu = true })
    const previousCount = nbNonLues.value
    nbNonLues.value = 0

    try {
        await api.post('/notifications/toutes-lues')
    } catch (e) {
        notifications.value.forEach((n, i) => { n.lu = previous[i] })
        nbNonLues.value = previousCount
        window.dispatchEvent(new CustomEvent('app-error', { detail: e?.response?.data?.message || 'Une erreur est survenue' }))
    }
}

onMounted(charger)
</script>

<style scoped>
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.titre { font-size: 18px; font-weight: 600; color: var(--color-text-primary); margin: 0; }
.btn-tout-lire { padding: 7px 14px; border: 1px solid #e5e7eb; border-radius: 8px; background: white; font-size: 12px; cursor: pointer; color: #6b7280; }
.btn-tout-lire:hover { background: #f3f4f6; }
.filtres { display: flex; gap: 8px; margin-bottom: 16px; flex-wrap: wrap; }
.filtre-btn { padding: 6px 14px; border: 1px solid #e5e7eb; border-radius: 20px; background: white; font-size: 12px; cursor: pointer; color: #6b7280; display: flex; align-items: center; gap: 5px; transition: all 0.15s; }
.filtre-btn.active { background: #1D9E75; color: white; border-color: #1D9E75; }
.badge-nb { background: #E24B4A; color: white; font-size: 10px; font-weight: 600; padding: 1px 5px; border-radius: 8px; }
.vide { text-align: center; padding: 60px; color: #9ca3af; font-size: 13px; background: white; border-radius: 12px; border: 1px solid #e5e7eb; }
.vide-icone { font-size: 32px; margin-bottom: 12px; }
.liste { display: flex; flex-direction: column; gap: 6px; }
.notif-card { display: flex; align-items: flex-start; gap: 12px; padding: 14px 16px; background: white; border: 1px solid #e5e7eb; border-radius: 12px; cursor: pointer; transition: all 0.15s; }
.notif-card:hover { border-color: #1D9E75; background: #f9fafb; }
.notif-card.non-lue { border-left: 3px solid #1D9E75; background: #f0fdf4; }
.notif-icone { width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.notif-icone.alerte     { background: #FCEBEB; color: #791F1F; }
.notif-icone.demande    { background: #E1F5EE; color: #085041; }
.notif-icone.message    { background: #E6F1FB; color: #0C447C; }
.notif-icone.partenariat { background: #FAEEDA; color: #633806; }
.notif-icone.reponse    { background: #EEEDFE; color: #3C3489; }
.notif-icone.systeme    { background: #f3f4f6; color: #6b7280; }
.notif-content { flex: 1; min-width: 0; }
.notif-titre   { font-size: 13px; font-weight: 500; color: var(--color-text-primary); margin-bottom: 3px; }
.notif-message { font-size: 12px; color: #6b7280; margin-bottom: 5px; line-height: 1.4; }
.notif-date    { font-size: 11px; color: #9ca3af; }
.notif-droite  { display: flex; flex-direction: column; align-items: flex-end; gap: 6px; flex-shrink: 0; }
.point-non-lu  { width: 8px; height: 8px; border-radius: 50%; background: #1D9E75; }
.badge-type    { font-size: 10px; font-weight: 600; padding: 2px 7px; border-radius: 8px; text-transform: capitalize; }
.badge-type.alerte     { background: #FCEBEB; color: #791F1F; }
.badge-type.demande    { background: #E1F5EE; color: #085041; }
.badge-type.message    { background: #E6F1FB; color: #0C447C; }
.badge-type.partenariat { background: #FAEEDA; color: #633806; }
.badge-type.reponse    { background: #EEEDFE; color: #3C3489; }
.badge-type.systeme    { background: #f3f4f6; color: #6b7280; }
</style>
