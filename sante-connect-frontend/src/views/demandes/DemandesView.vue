<template>
  <div>
    <div class="page-header">
      <h2 class="titre">Demandes</h2>
      <button class="btn-primary" @click="ouvrirModal()">Nouvelle demande</button>
    </div>

    <div class="onglets">
      <button :class="{ active: onglet === 'recues' }"   @click="changerOnglet('recues')">
        Reçues <span v-if="nbEnAttente > 0" class="badge-nb">{{ nbEnAttente }}</span>
      </button>
      <button :class="{ active: onglet === 'envoyees' }" @click="changerOnglet('envoyees')">Envoyées</button>
    </div>

    <SkeletonList v-if="chargement" :count="5" />
    <div v-else-if="demandesFiltrees.length === 0" class="vide">
      {{ onglet === 'recues' ? 'Aucune demande reçue' : 'Aucune demande envoyée' }}
    </div>
    <div v-else class="liste">
      <div v-for="d in demandesFiltrees" :key="d.id" :id="`demande-${d.id}`" class="demande-card" :class="{ 'mise-en-avant': d.id === highlightId }">
        <div class="card-top">
          <span class="badge-type">{{ d.type }}</span>
          <span class="badge-statut" :class="d.statut">{{ d.statut.replace('_',' ') }}</span>
          <span class="card-date">{{ formatDate(d.created_at) }}</span>
          <!-- Bouton modifier si c'est ma demande envoyée et en attente -->
          <div v-if="onglet === 'envoyees' && d.statut === 'en_attente'" class="card-actions">
            <button class="btn-modifier" @click="ouvrirModal(d)">Modifier</button>
            <button class="btn-supprimer" @click="annuler(d.id)">Annuler</button>
          </div>
        </div>
        <div class="card-titre">{{ d.titre }}</div>
        <div class="card-desc">{{ d.description }}</div>
        <div class="card-footer">
          <div class="card-ents">
            <span>{{ d.entreprise_source?.nom }}</span>
            <span class="fleche">→</span>
            <span>{{ d.entreprise_cible?.nom }}</span>
          </div>
          <!-- Boutons accepter/refuser si c'est une demande reçue en attente -->
          <div v-if="onglet === 'recues' && d.statut === 'en_attente'" class="card-actions">
            <button class="btn-accepter" @click="repondre(d.id, 'acceptee')">Accepter</button>
            <button class="btn-refuser"  @click="repondre(d.id, 'refusee')">Refuser</button>
          </div>
        </div>
      </div>
    </div>

    <UiPagination :meta="meta" @change="changerPage" />

    <!-- Modal créer/modifier demande -->
    <div v-if="showModal" class="modal-overlay" @click.self="fermerModal">
      <div class="modal">
        <h3 class="modal-titre">{{ demandeEdit ? 'Modifier la demande' : 'Nouvelle demande' }}</h3>

        <div class="champ" v-if="!demandeEdit">
          <label>Entreprise destinataire *</label>
          <select v-model="form.entreprise_cible_id">
            <option value="">Sélectionner une entreprise</option>
            <option v-for="e in autresEntreprises" :key="e.id" :value="e.id">
              {{ e.nom }} ({{ e.type }})
            </option>
          </select>
        </div>
        <div class="champ">
          <label>Titre *</label>
          <input v-model="form.titre" type="text" placeholder="Ex: Demande médicaments" />
        </div>
        <div class="champ">
          <label>Description</label>
          <textarea v-model="form.description" rows="3" placeholder="Détaillez votre demande..."></textarea>
        </div>
        <div class="champ">
          <label>Type</label>
          <select v-model="form.type">
            <option value="stock">Stock</option>
            <option value="service">Service</option>
            <option value="rapport">Rapport</option>
            <option value="partenariat">Partenariat</option>
            <option value="donnees">Données</option>
          </select>
        </div>

        <div v-if="erreur" class="erreur-box">{{ erreur }}</div>
        <div class="modal-actions">
          <button class="btn-annuler" @click="fermerModal">Annuler</button>
          <button class="btn-primary" @click="sauvegarder" :disabled="envoi">
            {{ envoi ? 'Envoi...' : demandeEdit ? 'Modifier' : 'Envoyer' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '../../stores/auth.js'
import api from '../../api/client.js'
import UiPagination from '../../components/ui/UiPagination.vue'
import SkeletonList from '../../components/ui/SkeletonList.vue'
import { useReferenceData } from '../../composables/useReferenceData.js'

const auth    = useAuthStore()
const route   = useRoute()
const { entreprises, loadEntreprises } = useReferenceData()
const highlightId = ref(null)
const chargement = ref(true)
const demandes = ref([])
const page = ref(1)
const meta = ref(null)
const showModal = ref(false)
const envoi   = ref(false)
const erreur  = ref('')
const onglet  = ref('recues')
const demandeEdit = ref(null)

const form = ref({ entreprise_cible_id: '', titre: '', description: '', type: 'stock' })

const dateFormatter = new Intl.DateTimeFormat('fr-FR')
function formatDate(d) { return dateFormatter.format(new Date(d)) }

const demandesFiltrees = computed(() => {
    const monId = auth.entreprise?.id
    if (onglet.value === 'recues')   return demandes.value.filter(d => d.entreprise_cible_id === monId)
    if (onglet.value === 'envoyees') return demandes.value.filter(d => d.entreprise_source_id === monId)
    return demandes.value
})

const autresEntreprises = computed(() =>
    entreprises.value.filter(e => e.id !== auth.entreprise?.id)
)

const nbEnAttente = computed(() => {
    const monId = auth.entreprise?.id
    return demandes.value.filter(d => d.entreprise_cible_id === monId && d.statut === 'en_attente').length
})

function ouvrirModal(d = null) {
    demandeEdit.value = d
    form.value = d
        ? { titre: d.titre, description: d.description, type: d.type, entreprise_cible_id: d.entreprise_cible_id }
        : { entreprise_cible_id: '', titre: '', description: '', type: 'stock' }
    erreur.value = ''
    showModal.value = true
}

function changerOnglet(nouvelOnglet) {
    if (onglet.value === nouvelOnglet) return
    onglet.value = nouvelOnglet
    page.value = 1
    charger()
}

function fermerModal() { showModal.value = false; demandeEdit.value = null }

let abortCtrl = null

async function charger() {
    if (abortCtrl) abortCtrl.abort()
    abortCtrl = new AbortController()

    chargement.value = demandes.value.length === 0
    try {
        const [resDem] = await Promise.all([
            api.get('/demandes', {
                params: { paginate: 1, page: page.value, per_page: 10, sens: onglet.value },
                signal: abortCtrl.signal,
            }),
            loadEntreprises(),
        ])
        demandes.value = resDem.data.data || resDem.data
        meta.value = resDem.data.data ? resDem.data : null
    } catch (e) {
        if (e.name === 'AbortError') return
        window.dispatchEvent(new CustomEvent('app-error', { detail: e?.response?.data?.message || 'Une erreur est survenue' }))
    } finally { chargement.value = false }
}

async function sauvegarder() {
    if (!form.value.titre || (!demandeEdit.value && !form.value.entreprise_cible_id)) {
        erreur.value = 'Champs obligatoires manquants'
        return
    }
    envoi.value = true
    try {
        if (demandeEdit.value) {
            const payload = {
                titre: form.value.titre,
                description: form.value.description,
                type: form.value.type,
            }
            const { data } = await api.put(`/demandes/${demandeEdit.value.id}`, payload)
            const index = demandes.value.findIndex(d => d.id === demandeEdit.value.id)
            if (index >= 0) demandes.value[index] = { ...demandes.value[index], ...(data || payload) }
        } else {
            const { data } = await api.post('/demandes', form.value)
            if (data) demandes.value.unshift(data)
        }
        fermerModal()
    } catch (e) {
        erreur.value = e.response?.data?.message || 'Erreur'
    } finally { envoi.value = false }
}

async function changerPage(nextPage) {
    page.value = nextPage
    await charger()
}

async function repondre(id, statut) {
    const demande = demandes.value.find(d => d.id === id)
    const previous = demande?.statut

    if (demande) {
        demande.statut = statut
    }

    try {
        await api.post(`/demandes/${id}/repondre`, { statut })
    } catch (e) {
        if (demande) {
            demande.statut = previous
        }

        window.dispatchEvent(
            new CustomEvent('app-error', {
                detail: e?.response?.data?.message || 'Une erreur est survenue'
            })
        )
    }
}

async function annuler(id) {
    if (!confirm('Annuler cette demande ?')) return
    const demande = demandes.value.find(d => d.id === id)
    const previous = demande?.statut
    if (demande) demande.statut = 'annulee'

    try { await api.post(`/demandes/${id}/annuler`) }
    catch (e) {
        if (demande) demande.statut = previous
        window.dispatchEvent(new CustomEvent('app-error', { detail: e?.response?.data?.message || 'Une erreur est survenue' }))
    }
}

async function ouvrirDepuisNotification() {
    const id = Number(route.query.id)
    if (!id) return

    try {
        const { data } = await api.get(`/demandes/${id}`)
        onglet.value = data.entreprise_source_id === auth.entreprise?.id ? 'envoyees' : 'recues'
    } catch { /* on garde l'onglet courant si la demande est introuvable */ }

    await charger()
    highlightId.value = id
    await nextTick()
    document.getElementById(`demande-${id}`)?.scrollIntoView({ behavior: 'smooth', block: 'center' })
    setTimeout(() => { if (highlightId.value === id) highlightId.value = null }, 4000)
}

onMounted(() => {
    if (route.query.id) ouvrirDepuisNotification()
    else charger()
})
onUnmounted(() => { if (abortCtrl) abortCtrl.abort() })
</script>

<style scoped>
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.titre { font-size: 18px; font-weight: 600; color: var(--color-text-primary); margin: 0; }
.btn-primary { background: #1D9E75; color: white; border: none; border-radius: 8px; padding: 9px 18px; font-size: 13px; font-weight: 500; cursor: pointer; }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
.onglets { display: flex; gap: 4px; background: #f3f4f6; border-radius: 10px; padding: 4px; margin-bottom: 16px; width: fit-content; }
.onglets button { padding: 6px 16px; border: none; border-radius: 8px; font-size: 13px; cursor: pointer; background: none; color: #6b7280; display: flex; align-items: center; gap: 6px; }
.onglets button.active { background: white; color: var(--color-text-primary); font-weight: 500; }
.badge-nb { background: #E24B4A; color: white; font-size: 10px; font-weight: 600; padding: 1px 6px; border-radius: 10px; }
.liste { display: flex; flex-direction: column; gap: 12px; }
.vide { text-align: center; padding: 40px; color: #9ca3af; font-size: 13px; background: white; border-radius: 12px; border: 1px solid #e5e7eb; }
.demande-card { background: white; border-radius: 12px; border: 1px solid #e5e7eb; padding: 16px; transition: box-shadow 0.3s, border-color 0.3s; }
.demande-card.mise-en-avant { border-color: #1D9E75; box-shadow: 0 0 0 3px rgba(29,158,117,0.2); animation: pulse-highlight 1.6s ease-in-out 2; }
@keyframes pulse-highlight { 0%, 100% { background: white; } 50% { background: #f0fdf9; } }
.card-top { display: flex; align-items: center; gap: 8px; margin-bottom: 10px; flex-wrap: wrap; }
.card-date { font-size: 11px; color: #9ca3af; }
.card-actions { margin-left: auto; display: flex; gap: 6px; }
.btn-modifier  { font-size: 11px; padding: 4px 10px; background: #E6F1FB; color: #0C447C; border: none; border-radius: 6px; cursor: pointer; }
.btn-supprimer { font-size: 11px; padding: 4px 10px; background: #FCEBEB; color: #791F1F; border: none; border-radius: 6px; cursor: pointer; }
.btn-accepter  { font-size: 12px; background: #E1F5EE; color: #085041; border: none; border-radius: 6px; padding: 5px 12px; cursor: pointer; }
.btn-refuser   { font-size: 12px; background: #FCEBEB; color: #791F1F; border: none; border-radius: 6px; padding: 5px 12px; cursor: pointer; }
.badge-type { font-size: 10px; font-weight: 600; padding: 3px 8px; border-radius: 10px; background: #EEEDFE; color: #3C3489; text-transform: capitalize; }
.badge-statut { font-size: 10px; font-weight: 600; padding: 3px 8px; border-radius: 10px; text-transform: capitalize; }
.badge-statut.en_attente { background: #FAEEDA; color: #633806; }
.badge-statut.acceptee   { background: #E1F5EE; color: #085041; }
.badge-statut.refusee    { background: #FCEBEB; color: #791F1F; }
.badge-statut.annulee    { background: #f3f4f6; color: #6b7280; }
.card-titre { font-size: 14px; font-weight: 600; color: var(--color-text-primary); margin-bottom: 6px; }
.card-desc  { font-size: 13px; color: #374151; margin-bottom: 12px; }
.card-footer { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px; }
.card-ents { font-size: 12px; color: #9ca3af; display: flex; gap: 6px; align-items: center; }
.fleche { color: #1D9E75; }
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.4); display: flex; align-items: center; justify-content: center; z-index: 100; padding: 16px; }
.modal { background: white; border-radius: 16px; padding: 28px; width: 100%; max-width: 480px; }
.modal-titre { font-size: 16px; font-weight: 600; color: var(--color-text-primary); margin: 0 0 20px; }
.champ { display: flex; flex-direction: column; gap: 6px; margin-bottom: 14px; }
.champ label { font-size: 13px; font-weight: 500; color: #374151; }
.champ input, .champ textarea, .champ select { padding: 9px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 13px; outline: none; font-family: inherit; }
.champ input:focus, .champ textarea:focus, .champ select:focus { border-color: #1D9E75; }
.erreur-box { background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 10px; font-size: 13px; color: #dc2626; margin-bottom: 14px; }
.modal-actions { display: flex; gap: 10px; justify-content: flex-end; }
.btn-annuler { padding: 9px 18px; border: 1px solid #e5e7eb; border-radius: 8px; background: white; font-size: 13px; cursor: pointer; color: #6b7280; }
</style>
