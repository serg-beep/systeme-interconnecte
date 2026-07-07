<template>
  <div>
    <div class="page-header">
      <h2 class="titre">Alertes</h2>
      <button class="btn-primary" @click="ouvrirModal()">Nouvelle alerte</button>
    </div>

    <!-- Filtres -->
    <div class="filtres">
      <button v-for="f in filtres" :key="f.val"
        class="filtre-btn" :class="{ active: filtre === f.val }"
        @click="changerFiltre(f.val)">
        {{ f.label }}
      </button>
    </div>

    <div v-if="messageErreur" class="erreur-box">{{ messageErreur }}</div>
    <SkeletonList v-if="chargement" :count="5" />
    <div v-else-if="alertesFiltrees.length === 0" class="vide">
      Aucune alerte — toutes les alertes ont été traitées ✓
    </div>
    <div v-else class="liste">
      <div v-for="a in alertesFiltrees" :key="a.id" class="alerte-card">
        <div class="alerte-top">
          <span class="badge-type" :class="a.type">{{ a.type.replace('_',' ') }}</span>
          <span class="badge-priorite" :class="a.priorite">{{ a.priorite }}</span>
          <span class="alerte-date">{{ formatDate(a.created_at) }}</span>
          <!-- Boutons modifier si c'est mon alerte -->
          <div v-if="estMaAlerte(a)" class="alerte-actions">
            <button class="btn-modifier" @click="ouvrirModal(a)">Modifier</button>
            <button class="btn-supprimer" @click="supprimer(a.id)">Supprimer</button>
          </div>
        </div>
        <div class="alerte-titre">{{ a.titre }}</div>
        <div class="alerte-message">{{ a.message }}</div>
        <div class="alerte-footer">
          <span class="alerte-entreprise">{{ a.entreprise?.nom }}</span>
          <button v-if="!estMaAlerte(a) && !aAccuse(a)"
            class="btn-accuser" @click="accuser(a.id)">
            Accuser réception
          </button>
          <span v-if="aAccuse(a)" class="badge-accuse">✓ Reçu</span>
        </div>
      </div>
    </div>
    <UiPagination :meta="meta" @change="changerPage" />

    <!-- Modal création / modification -->
    <div v-if="showModal" class="modal-overlay" @click.self="fermerModal">
      <div class="modal">
        <h3 class="modal-titre">{{ alerteEdit ? 'Modifier l\'alerte' : 'Nouvelle alerte' }}</h3>

        <div class="champ">
          <label>Titre *</label>
          <input v-model="form.titre" type="text" placeholder="Ex: Rupture de stock insuline" />
        </div>
        <div class="champ">
          <label>Message *</label>
          <textarea v-model="form.message" rows="3" placeholder="Décrivez l'alerte..."></textarea>
        </div>
        <div class="champ-grille">
          <div class="champ">
            <label>Type</label>
            <select v-model="form.type">
              <option value="rupture_stock">Rupture de stock</option>
              <option value="urgence">Urgence</option>
              <option value="information">Information</option>
              <option value="rappel">Rappel</option>
            </select>
          </div>
          <div class="champ">
            <label>Priorité</label>
            <select v-model="form.priorite">
              <option value="faible">Faible</option>
              <option value="normale">Normale</option>
              <option value="haute">Haute</option>
              <option value="critique">Critique</option>
            </select>
          </div>
        </div>

        <div v-if="erreur" class="erreur-box">{{ erreur }}</div>

        <div class="modal-actions">
          <button class="btn-annuler" @click="fermerModal">Annuler</button>
          <button class="btn-primary" @click="sauvegarder" :disabled="envoi">
            {{ envoi ? 'Envoi...' : alerteEdit ? 'Modifier' : 'Envoyer à tous' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useAuthStore } from '../../stores/auth.js'
import api from '../../api/client.js'
import UiPagination from '../../components/ui/UiPagination.vue'
import SkeletonList from '../../components/ui/SkeletonList.vue'
import { apiErrorMessage } from '../../composables/useApiError.js'

const auth      = useAuthStore()
const chargement = ref(true)
const alertes   = ref([])
const showModal = ref(false)
const envoi     = ref(false)
const erreur    = ref('')
const messageErreur = ref('')
const filtre    = ref('toutes')
const alerteEdit = ref(null)
const page = ref(1)
const meta = ref(null)

const form = ref({ titre: '', message: '', type: 'information', priorite: 'normale' })

const filtres = [
    { val: 'toutes',        label: 'Toutes' },
    { val: 'critique',      label: 'Critiques' },
    { val: 'urgence',       label: 'Urgences' },
    { val: 'rupture_stock', label: 'Ruptures' },
    { val: 'information',   label: 'Informations' },
]

const alertesFiltrees = computed(() => {
    let liste = alertes.value
    if (filtre.value !== 'toutes' && !meta.value) {
        liste = liste.filter(a => a.priorite === filtre.value || a.type === filtre.value)
    }
    return liste
})

function estMaAlerte(a) {
    return a.entreprise_id === auth.entreprise?.id
}

function aAccuse(a) {
    return a.entreprises_destinaires?.some(
        e => e.id === auth.entreprise?.id && e.pivot?.accuse_reception
    )
}

const dateFormatter = new Intl.DateTimeFormat('fr-FR', { day: '2-digit', month: 'short', year: 'numeric' })
function formatDate(d) {
    return dateFormatter.format(new Date(d))
}

function ouvrirModal(alerte = null) {
    alerteEdit.value = alerte
    if (alerte) {
        form.value = { titre: alerte.titre, message: alerte.message, type: alerte.type, priorite: alerte.priorite }
    } else {
        form.value = { titre: '', message: '', type: 'information', priorite: 'normale' }
    }
    erreur.value  = ''
    showModal.value = true
}

function fermerModal() {
    showModal.value = false
    alerteEdit.value = null
}

let abortCtrl = null

async function charger() {
    // Annuler la requête précédente si elle est encore en vol
    if (abortCtrl) abortCtrl.abort()
    abortCtrl = new AbortController()

    chargement.value = alertes.value.length === 0
    messageErreur.value = ''

    const params = { paginate: 1, page: page.value, per_page: 10 }
    if (filtre.value === 'critique') params.priorite = 'critique'
    if (['urgence', 'rupture_stock', 'information'].includes(filtre.value)) params.type = filtre.value

    try {
        const { data } = await api.get('/alertes', { params, signal: abortCtrl.signal })
        alertes.value = data.data || data
        meta.value = data.data ? data : null
    } catch (e) {
        if (e.name === 'AbortError') return
        messageErreur.value = apiErrorMessage(e, 'Impossible de charger les alertes')
    } finally {
        chargement.value = false
    }
}

function changerFiltre(valeur) {
    filtre.value = valeur
    page.value = 1
    charger()
}

function changerPage(nextPage) {
    page.value = nextPage
    charger()
}

onUnmounted(() => { if (abortCtrl) abortCtrl.abort() })

async function accuser(id) {
    const alerte = alertes.value.find(a => a.id === id)
    const destinataire = alerte?.entreprises_destinaires?.find(e => e.id === auth.entreprise?.id)

    if (destinataire) {
        destinataire.pivot = { ...(destinataire.pivot || {}), accuse_reception: true }
    }

    try {
        await api.post(`/alertes/${id}/accuser`)
    } catch (e) {
        if (destinataire) {
            destinataire.pivot = { ...(destinataire.pivot || {}), accuse_reception: false }
        }
        messageErreur.value = apiErrorMessage(e, 'Impossible d accuser reception')
    }
}

async function sauvegarder() {
    erreur.value = ''
    if (!form.value.titre || !form.value.message) {
        erreur.value = 'Titre et message obligatoires'
        return
    }
    envoi.value = true
    try {
        if (alerteEdit.value) {
            const { data } = await api.put(`/alertes/${alerteEdit.value.id}`, form.value)
            const index = alertes.value.findIndex(a => a.id === alerteEdit.value.id)
            if (index >= 0) {
                alertes.value[index] = { ...alertes.value[index], ...(data || form.value) }
            }
        } else {
            const { data } = await api.post('/alertes', form.value)
            if (data) alertes.value.unshift(data)
        }
        fermerModal()
    } catch (e) {
        erreur.value = apiErrorMessage(e, 'Erreur lors de l enregistrement')
    } finally { envoi.value = false }
}

async function supprimer(id) {
    if (!confirm('Supprimer cette alerte ?')) return
    const index = alertes.value.findIndex(a => a.id === id)
    const deleted = index >= 0 ? alertes.value[index] : null
    if (index >= 0) alertes.value.splice(index, 1)

    try {
        await api.delete(`/alertes/${id}`)
    } catch (e) {
        if (deleted) alertes.value.splice(index, 0, deleted)
        messageErreur.value = apiErrorMessage(e, 'Suppression impossible')
    }
}

onMounted(charger)
</script>

<style scoped>
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.titre { font-size: 18px; font-weight: 600; color: var(--color-text-primary); margin: 0; }
.btn-primary { background: #1D9E75; color: white; border: none; border-radius: 8px; padding: 9px 18px; font-size: 13px; font-weight: 500; cursor: pointer; }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
.filtres { display: flex; gap: 8px; margin-bottom: 16px; flex-wrap: wrap; }
.filtre-btn { padding: 6px 14px; border: 1px solid #e5e7eb; border-radius: 20px; background: white; font-size: 12px; cursor: pointer; color: #6b7280; transition: all 0.15s; }
.filtre-btn.active { background: #1D9E75; color: white; border-color: #1D9E75; }
.liste { display: flex; flex-direction: column; gap: 12px; }
.vide { text-align: center; padding: 40px; color: #9ca3af; font-size: 13px; background: white; border-radius: 12px; border: 1px solid #e5e7eb; }
.alerte-card { background: white; border-radius: 12px; border: 1px solid #e5e7eb; padding: 16px; }
.alerte-top { display: flex; align-items: center; gap: 8px; margin-bottom: 10px; flex-wrap: wrap; }
.alerte-date { font-size: 11px; color: #9ca3af; }
.alerte-actions { margin-left: auto; display: flex; gap: 6px; }
.btn-modifier { font-size: 11px; padding: 4px 10px; background: #E6F1FB; color: #0C447C; border: none; border-radius: 6px; cursor: pointer; }
.btn-supprimer { font-size: 11px; padding: 4px 10px; background: #FCEBEB; color: #791F1F; border: none; border-radius: 6px; cursor: pointer; }
.badge-type { font-size: 10px; font-weight: 600; padding: 3px 8px; border-radius: 10px; background: #E6F1FB; color: #0C447C; text-transform: capitalize; }
.badge-priorite { font-size: 10px; font-weight: 600; padding: 3px 8px; border-radius: 10px; text-transform: capitalize; }
.badge-priorite.critique { background: #FCEBEB; color: #791F1F; }
.badge-priorite.haute    { background: #FAEEDA; color: #633806; }
.badge-priorite.normale  { background: #E1F5EE; color: #085041; }
.badge-priorite.faible   { background: #f3f4f6; color: #6b7280; }
.alerte-titre   { font-size: 14px; font-weight: 600; color: var(--color-text-primary); margin-bottom: 6px; }
.alerte-message { font-size: 13px; color: #374151; line-height: 1.5; margin-bottom: 12px; }
.alerte-footer  { display: flex; align-items: center; justify-content: space-between; }
.alerte-entreprise { font-size: 12px; color: #9ca3af; }
.btn-accuser { font-size: 12px; background: #E1F5EE; color: #085041; border: none; border-radius: 6px; padding: 5px 12px; cursor: pointer; }
.badge-accuse { font-size: 11px; color: #085041; background: #E1F5EE; padding: 3px 8px; border-radius: 8px; }
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.4); display: flex; align-items: center; justify-content: center; z-index: 100; padding: 16px; }
.modal { background: white; border-radius: 16px; padding: 28px; width: 100%; max-width: 480px; }
.modal-titre { font-size: 16px; font-weight: 600; color: var(--color-text-primary); margin: 0 0 20px; }
.champ { display: flex; flex-direction: column; gap: 6px; margin-bottom: 14px; }
.champ label { font-size: 13px; font-weight: 500; color: #374151; }
.champ input, .champ textarea, .champ select { padding: 9px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 13px; outline: none; font-family: inherit; }
.champ input:focus, .champ textarea:focus, .champ select:focus { border-color: #1D9E75; }
.champ-grille { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.erreur-box { background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 10px; font-size: 13px; color: #dc2626; margin-bottom: 14px; }
.modal-actions { display: flex; gap: 10px; justify-content: flex-end; }
.btn-annuler { padding: 9px 18px; border: 1px solid #e5e7eb; border-radius: 8px; background: white; font-size: 13px; cursor: pointer; color: #6b7280; }
</style>
