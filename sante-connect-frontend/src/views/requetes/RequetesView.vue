<template>
  <div>
    <div class="page-header">
      <h2 class="titre">Requêtes</h2>
      <button class="btn-primary" @click="ouvrirModal()">Nouvelle requête</button>
    </div>

    <SkeletonList v-if="chargement" :count="5" />
    <div v-else-if="requetes.length === 0" class="vide">
      Aucune requête ouverte — tout est résolu ✓
    </div>
    <div v-else class="liste">
      <div v-for="r in requetes" :key="r.id" :id="`requete-${r.id}`" class="requete-card" :class="{ 'mise-en-avant': r.id === highlightId }">
        <div class="card-top">
          <span class="badge-type">{{ r.type }}</span>
          <span class="badge-statut" :class="r.statut">{{ r.statut }}</span>
          <span class="card-date">{{ formatDate(r.created_at) }}</span>
          <div v-if="estMaRequete(r)" class="card-actions">
            <button class="btn-modifier" @click="ouvrirModal(r)">Modifier</button>
            <button class="btn-fermer-btn" @click="fermer(r.id)"
              v-if="r.statut === 'ouverte'">Fermer</button>
            <button class="btn-supprimer" @click="supprimer(r.id)">Supprimer</button>
          </div>
        </div>
        <div class="card-titre">{{ r.titre }}</div>
        <div class="card-desc">{{ r.description }}</div>
        <div class="card-footer">
          <span class="card-ent">{{ r.entreprise?.nom }}</span>
          <button v-if="!estMaRequete(r)" class="btn-repondre" @click="repondreA(r)">
            Répondre
          </button>
        </div>

        <!-- Réponses existantes -->
        <div v-if="r.reponses?.length" class="reponses-section">
          <div class="reponses-titre">{{ r.reponses.length }} réponse(s)</div>
          <div v-for="rep in r.reponses" :key="rep.id" class="reponse-item">
            <div class="rep-avatar">{{ rep.entreprise?.nom?.[0] }}</div>
            <div class="rep-content">
              <div class="rep-ent">{{ rep.entreprise?.nom }}</div>
              <div class="rep-texte">{{ rep.contenu }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <UiPagination :meta="meta" @change="changerPage" />

    <!-- Modal créer/modifier -->
    <div v-if="showModal" class="modal-overlay" @click.self="fermerModal">
      <div class="modal">
        <h3 class="modal-titre">{{ requeteEdit ? 'Modifier la requête' : 'Nouvelle requête' }}</h3>
        <div class="champ">
          <label>Titre *</label>
          <input v-model="form.titre" type="text" placeholder="Ex: Besoin réactifs hématologie" />
        </div>
        <div class="champ">
          <label>Description</label>
          <textarea v-model="form.description" rows="3" placeholder="Décrivez votre besoin..."></textarea>
        </div>
        <div class="champ">
          <label>Type</label>
          <select v-model="form.type">
            <option value="information">Information</option>
            <option value="ressource">Ressource</option>
            <option value="collaboration">Collaboration</option>
            <option value="autre">Autre</option>
          </select>
        </div>
        <div v-if="erreur" class="erreur-box">{{ erreur }}</div>
        <div class="modal-actions">
          <button class="btn-annuler" @click="fermerModal">Annuler</button>
          <button class="btn-primary" @click="sauvegarder" :disabled="envoi">
            {{ envoi ? 'Envoi...' : requeteEdit ? 'Modifier' : 'Publier' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Modal répondre -->
    <div v-if="requeteSelectionnee" class="modal-overlay" @click.self="requeteSelectionnee = null">
      <div class="modal">
        <h3 class="modal-titre">Répondre à : {{ requeteSelectionnee.titre }}</h3>
        <div class="champ">
          <label>Votre réponse *</label>
          <textarea v-model="reponse" rows="4" placeholder="Votre réponse..."></textarea>
        </div>
        <div class="modal-actions">
          <button class="btn-annuler" @click="requeteSelectionnee = null">Annuler</button>
          <button class="btn-primary" @click="envoyerReponse" :disabled="envoi">
            {{ envoi ? 'Envoi...' : 'Envoyer' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, nextTick } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '../../stores/auth.js'
import api from '../../api/client.js'
import UiPagination from '../../components/ui/UiPagination.vue'
import SkeletonList from '../../components/ui/SkeletonList.vue'

const auth    = useAuthStore()
const route   = useRoute()
const highlightId = ref(null)
const chargement = ref(true)
const requetes = ref([])
const page = ref(1)
const meta = ref(null)
const showModal = ref(false)
const envoi   = ref(false)
const erreur  = ref('')
const requeteEdit = ref(null)
const requeteSelectionnee = ref(null)
const reponse = ref('')

const form = ref({ titre: '', description: '', type: 'information' })

function estMaRequete(r) { return r.entreprise_id === auth.entreprise?.id }

const dateFormatter = new Intl.DateTimeFormat('fr-FR')
function formatDate(d) { return dateFormatter.format(new Date(d)) }
function repondreA(r) { requeteSelectionnee.value = r; reponse.value = '' }

function ouvrirModal(r = null) {
    requeteEdit.value = r
    form.value = r
        ? { titre: r.titre, description: r.description, type: r.type }
        : { titre: '', description: '', type: 'information' }
    erreur.value = ''
    showModal.value = true
}

function fermerModal() { showModal.value = false; requeteEdit.value = null }

let abortCtrl = null

async function charger() {
    if (abortCtrl) abortCtrl.abort()
    abortCtrl = new AbortController()

    chargement.value = requetes.value.length === 0
    try {
        const { data } = await api.get('/requetes', {
            params: { paginate: 1, page: page.value, per_page: 10 },
            signal: abortCtrl.signal,
        })
        requetes.value = data.data || data
        meta.value = data.data ? data : null
    } catch (e) {
        if (e.name === 'AbortError') return
        window.dispatchEvent(new CustomEvent('app-error', { detail: e?.response?.data?.message || 'Une erreur est survenue' }))
    } finally { chargement.value = false }
}

function changerPage(nextPage) {
    page.value = nextPage
    charger()
}

async function sauvegarder() {
    if (!form.value.titre) { erreur.value = 'Titre obligatoire'; return }
    envoi.value = true
    try {
        if (requeteEdit.value) {
            const { data } = await api.put(`/requetes/${requeteEdit.value.id}`, form.value)
            const index = requetes.value.findIndex(r => r.id === requeteEdit.value.id)
            if (index >= 0) requetes.value[index] = { ...requetes.value[index], ...(data || form.value) }
        } else {
            const { data } = await api.post('/requetes', form.value)
            if (data) requetes.value.unshift(data)
        }
        fermerModal()
    } catch (e) {
        erreur.value = e.response?.data?.message || 'Erreur'
    } finally { envoi.value = false }
}

async function fermer(id) {
    if (!confirm('Fermer cette requête comme résolue ?')) return
    const index = requetes.value.findIndex(r => r.id === id)
    const closed = index >= 0 ? requetes.value[index] : null
    if (index >= 0) requetes.value.splice(index, 1)

    try { await api.post(`/requetes/${id}/fermer`) }
    catch (e) {
        if (closed) requetes.value.splice(index, 0, closed)
        window.dispatchEvent(new CustomEvent('app-error', { detail: e?.response?.data?.message || 'Une erreur est survenue' }))
    }
}

async function supprimer(id) {
    if (!confirm('Supprimer cette requête ?')) return
    const index = requetes.value.findIndex(r => r.id === id)
    const deleted = index >= 0 ? requetes.value[index] : null
    if (index >= 0) requetes.value.splice(index, 1)

    try { await api.delete(`/requetes/${id}`) }
    catch (e) {
        if (deleted) requetes.value.splice(index, 0, deleted)
        window.dispatchEvent(new CustomEvent('app-error', { detail: e?.response?.data?.message || 'Une erreur est survenue' }))
    }
}

async function envoyerReponse() {
    if (!reponse.value) return
    envoi.value = true
    const answeredId = requeteSelectionnee.value.id
    const index = requetes.value.findIndex(r => r.id === answeredId)
    const answered = index >= 0 ? requetes.value[index] : null

    try {
        await api.post('/reponses', {
            requete_id: answeredId,
            contenu: reponse.value
        })
        requeteSelectionnee.value = null
        reponse.value = ''
        if (index >= 0) requetes.value.splice(index, 1)
    } catch (e) {
        if (answered && !requetes.value.some(r => r.id === answered.id)) {
            requetes.value.splice(index, 0, answered)
        }
        window.dispatchEvent(new CustomEvent('app-error', { detail: e?.response?.data?.message || 'Une erreur est survenue' }))
    }
    finally { envoi.value = false }
}

async function ouvrirDepuisNotification() {
    const id = Number(route.query.id)
    if (!id) return

    await charger()
    highlightId.value = id
    await nextTick()
    document.getElementById(`requete-${id}`)?.scrollIntoView({ behavior: 'smooth', block: 'center' })
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
.liste { display: flex; flex-direction: column; gap: 12px; }
.vide { text-align: center; padding: 40px; color: #9ca3af; font-size: 13px; background: white; border-radius: 12px; border: 1px solid #e5e7eb; }
.requete-card { background: white; border-radius: 12px; border: 1px solid #e5e7eb; padding: 16px; transition: box-shadow 0.3s, border-color 0.3s; }
.requete-card.mise-en-avant { border-color: #1D9E75; box-shadow: 0 0 0 3px rgba(29,158,117,0.2); animation: pulse-highlight 1.6s ease-in-out 2; }
@keyframes pulse-highlight { 0%, 100% { background: white; } 50% { background: #f0fdf9; } }
.card-top { display: flex; align-items: center; gap: 8px; margin-bottom: 10px; flex-wrap: wrap; }
.card-date { font-size: 11px; color: #9ca3af; }
.card-actions { margin-left: auto; display: flex; gap: 6px; }
.btn-modifier { font-size: 11px; padding: 4px 10px; background: #E6F1FB; color: #0C447C; border: none; border-radius: 6px; cursor: pointer; }
.btn-fermer-btn { font-size: 11px; padding: 4px 10px; background: #FAEEDA; color: #633806; border: none; border-radius: 6px; cursor: pointer; }
.btn-supprimer { font-size: 11px; padding: 4px 10px; background: #FCEBEB; color: #791F1F; border: none; border-radius: 6px; cursor: pointer; }
.badge-type { font-size: 10px; font-weight: 600; padding: 3px 8px; border-radius: 10px; background: #EEEDFE; color: #3C3489; text-transform: capitalize; }
.badge-statut { font-size: 10px; font-weight: 600; padding: 3px 8px; border-radius: 10px; text-transform: capitalize; }
.badge-statut.ouverte { background: #E1F5EE; color: #085041; }
.badge-statut.fermee  { background: #f3f4f6; color: #6b7280; }
.card-titre { font-size: 14px; font-weight: 600; color: var(--color-text-primary); margin-bottom: 6px; }
.card-desc  { font-size: 13px; color: #374151; margin-bottom: 12px; }
.card-footer { display: flex; align-items: center; justify-content: space-between; }
.card-ent { font-size: 12px; color: #9ca3af; }
.btn-repondre { font-size: 12px; background: #E6F1FB; color: #0C447C; border: none; border-radius: 6px; padding: 5px 12px; cursor: pointer; }
.reponses-section { margin-top: 14px; padding-top: 12px; border-top: 1px solid #f3f4f6; }
.reponses-titre { font-size: 12px; color: #9ca3af; margin-bottom: 8px; }
.reponse-item { display: flex; gap: 8px; margin-bottom: 8px; }
.rep-avatar { width: 28px; height: 28px; border-radius: 50%; background: #1D9E75; color: white; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 600; flex-shrink: 0; }
.rep-ent { font-size: 11px; font-weight: 600; color: var(--color-text-primary); }
.rep-texte { font-size: 12px; color: #374151; }
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
