<template>
  <div>
    <div class="page-header">
      <h2 class="titre">Partenaires</h2>
      <button class="btn-primary" @click="showModal = true">Demander partenariat</button>
    </div>

    <div class="grille">
      <div v-if="chargement" class="vide">Chargement...</div>
      <div v-else-if="partenaires.length === 0" class="vide">Aucun partenariat</div>
      <template v-else>
        <div class="cards">
          <div v-for="p in partenaires" :key="p.id" :id="`partenaire-${p.id}`" class="part-card" :class="{ 'mise-en-avant': p.id === highlightId }">
            <div class="part-avatar">{{ autreEntreprise(p).nom?.[0] }}</div>
            <div class="part-nom">{{ autreEntreprise(p).nom }}</div>
            <div class="part-type">{{ autreEntreprise(p).type }}</div>
            <span class="badge-statut" :class="p.statut">{{ p.statut.replace('_', ' ') }}</span>
            <div v-if="peutRepondre(p)" class="part-actions">
              <button class="btn-accepter" @click="repondre(p.id, 'accepte')">Accepter</button>
              <button class="btn-refuser"  @click="repondre(p.id, 'refuse')">Refuser</button>
            </div>
            <div v-if="p.statut === 'accepte'" class="part-date">
              Partenaire depuis {{ formatDate(p.date_partenariat) }}
            </div>
          </div>
        </div>
        <UiPagination :meta="meta" @change="changerPage" />
      </template>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="modal-overlay" @click.self="showModal = false">
      <div class="modal">
        <h3 class="modal-titre">Demander un partenariat</h3>
        <div class="champ">
          <label>Choisir une entreprise</label>
          <select v-model="form.entreprise_partenaire_id">
            <option value="">Sélectionner...</option>
            <option v-for="e in autresEntreprises" :key="e.id" :value="e.id">
              {{ e.nom }} ({{ e.type }})
            </option>
          </select>
        </div>
        <div v-if="erreur" class="erreur-box">{{ erreur }}</div>
        <div class="modal-actions">
          <button class="btn-annuler" @click="showModal = false">Annuler</button>
          <button class="btn-primary" @click="creer" :disabled="envoi">
            {{ envoi ? 'Envoi...' : 'Envoyer' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, onMounted, nextTick } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '../../stores/auth.js'
import api from '../../api/client.js'
import { useReferenceData } from '../../composables/useReferenceData.js'
import UiPagination from '../../components/ui/UiPagination.vue'

const auth    = useAuthStore()
const route   = useRoute()
const highlightId = ref(null)
const { entreprises, loadEntreprises } = useReferenceData()
const chargement = ref(true)
const partenaires = ref([])
const meta    = ref(null)
const page    = ref(1)
const showModal = ref(false)
const envoi   = ref(false)
const erreur  = ref('')
const form    = ref({ entreprise_partenaire_id: '' })

const autresEntreprises = computed(() =>
    entreprises.value.filter(e => e.id !== auth.entreprise?.id)
)

function formatDate(d) { if (!d) return ''; return new Date(d).toLocaleDateString('fr-FR') }

function autreEntreprise(p) {
    const monId = auth.entreprise?.id
    return p.entreprise_id === monId ? p.entreprise_partenaire : p.entreprise
}

function peutRepondre(p) {
    return p.entreprise_partenaire_id === auth.entreprise?.id && p.statut === 'en_attente'
}

async function chargerPartenaires() {
    const { data } = await api.get('/partenaires', { params: { paginate: 1, page: page.value, per_page: 12 } })
    partenaires.value = data.data ?? data
    meta.value = data.data ? data : null
}

async function charger() {
    chargement.value = partenaires.value.length === 0
    try {
        await Promise.all([chargerPartenaires(), loadEntreprises()])
    } catch (e) { window.dispatchEvent(new CustomEvent('app-error', { detail: e?.response?.data?.message || 'Une erreur est survenue' })) }
    finally { chargement.value = false }
}

function changerPage(p) {
    page.value = p
    chargerPartenaires()
}

async function creer() {
    if (!form.value.entreprise_partenaire_id) { erreur.value = 'Choisir une entreprise'; return }
    envoi.value = true
    try {
        await api.post('/partenaires', form.value)
        page.value = 1
        await chargerPartenaires()
        showModal.value = false
        form.value = { entreprise_partenaire_id: '' }
    } catch (e) {
        erreur.value = e.response?.data?.message || 'Erreur'
    } finally { envoi.value = false }
}

async function repondre(id, statut) {
    const partenariat = partenaires.value.find(p => p.id === id)
    const previous = partenariat?.statut
    const previousDate = partenariat?.date_partenariat

    if (partenariat) {
        partenariat.statut = statut
        if (statut === 'accepte') {
            partenariat.date_partenariat = partenariat.date_partenariat || new Date().toISOString()
        }
    }

    try { await api.post(`/partenaires/${id}/repondre`, { statut }) }
    catch (e) {
        if (partenariat) {
            partenariat.statut = previous
            partenariat.date_partenariat = previousDate
        }
        window.dispatchEvent(new CustomEvent('app-error', { detail: e?.response?.data?.message || 'Une erreur est survenue' }))
    }
}

async function ouvrirDepuisNotification() {
    const id = Number(route.query.id)
    if (!id) return

    await charger()
    highlightId.value = id
    await nextTick()
    document.getElementById(`partenaire-${id}`)?.scrollIntoView({ behavior: 'smooth', block: 'center' })
    setTimeout(() => { if (highlightId.value === id) highlightId.value = null }, 4000)
}

onMounted(() => {
    if (route.query.id) ouvrirDepuisNotification()
    else charger()
})
</script>

<style scoped>
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.titre { font-size: 18px; font-weight: 600; color: #1a1a2e; margin: 0; }
.btn-primary { background: #1D9E75; color: white; border: none; border-radius: 8px; padding: 9px 18px; font-size: 13px; font-weight: 500; cursor: pointer; }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
.vide { text-align: center; padding: 40px; color: #9ca3af; background: white; border-radius: 12px; border: 1px solid #e5e7eb; font-size: 13px; }
.cards { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 16px; }
.part-card { background: white; border-radius: 12px; border: 1px solid #e5e7eb; padding: 20px; display: flex; flex-direction: column; align-items: center; gap: 8px; text-align: center; transition: box-shadow 0.3s, border-color 0.3s; }
.part-card.mise-en-avant { border-color: #1D9E75; box-shadow: 0 0 0 3px rgba(29,158,117,0.2); animation: pulse-highlight 1.6s ease-in-out 2; }
@keyframes pulse-highlight { 0%, 100% { background: white; } 50% { background: #f0fdf9; } }
.part-avatar { width: 52px; height: 52px; border-radius: 50%; background: #1D9E75; color: white; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: 600; }
.part-nom  { font-size: 14px; font-weight: 600; color: #1a1a2e; }
.part-type { font-size: 12px; color: #9ca3af; text-transform: capitalize; }
.badge-statut { font-size: 10px; font-weight: 600; padding: 3px 10px; border-radius: 10px; text-transform: capitalize; }
.badge-statut.en_attente { background: #FAEEDA; color: #633806; }
.badge-statut.accepte    { background: #E1F5EE; color: #085041; }
.badge-statut.refuse     { background: #FCEBEB; color: #791F1F; }
.part-actions { display: flex; gap: 8px; }
.btn-accepter { font-size: 12px; background: #E1F5EE; color: #085041; border: none; border-radius: 6px; padding: 5px 12px; cursor: pointer; }
.btn-refuser  { font-size: 12px; background: #FCEBEB; color: #791F1F; border: none; border-radius: 6px; padding: 5px 12px; cursor: pointer; }
.part-date { font-size: 11px; color: #9ca3af; }
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.4); display: flex; align-items: center; justify-content: center; z-index: 100; }
.modal { background: white; border-radius: 16px; padding: 28px; width: 100%; max-width: 420px; }
.modal-titre { font-size: 16px; font-weight: 600; color: #1a1a2e; margin: 0 0 20px; }
.champ { display: flex; flex-direction: column; gap: 6px; margin-bottom: 14px; }
.champ label { font-size: 13px; font-weight: 500; color: #374151; }
.champ select { padding: 9px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 13px; color: #1a1a2e; outline: none; }
.erreur-box { background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 10px; font-size: 13px; color: #dc2626; margin-bottom: 14px; }
.modal-actions { display: flex; gap: 10px; justify-content: flex-end; }
.btn-annuler { padding: 9px 18px; border: 1px solid #e5e7eb; border-radius: 8px; background: white; font-size: 13px; cursor: pointer; color: #6b7280; }
</style>
