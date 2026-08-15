<template>
  <div>
    <div class="page-header">
      <h2 class="titre">Gestion des entreprises</h2>
      <button v-if="peutGerer" class="btn-primary" @click="ouvrirModal()">Ajouter une entreprise</button>
    </div>

    <div class="stats-mini">
      <div class="stat-mini">
        <div class="stat-num">{{ stats.total }}</div>
        <div class="stat-label">Total entreprises</div>
      </div>
      <div class="stat-mini">
        <div class="stat-num success">{{ stats.actives }}</div>
        <div class="stat-label">Actives</div>
      </div>
      <div class="stat-mini">
        <div class="stat-num danger">{{ stats.inactives }}</div>
        <div class="stat-label">Inactives / suspendues</div>
      </div>
    </div>

    <div class="section">
      <div class="recherche-bar">
        <input v-model="recherche" type="text" placeholder="Rechercher une entreprise..." class="recherche-input" />
        <select v-model="typeFiltre" class="type-select">
          <option value="">Tous les types</option>
          <option v-for="t in types" :key="t.value" :value="t.value">{{ t.label }}</option>
        </select>
      </div>

      <div v-if="chargement" class="vide">Chargement...</div>
      <div v-else-if="entreprises.length === 0" class="vide">Aucune entreprise</div>
      <template v-else>
        <table class="table">
          <thead>
            <tr>
              <th>Entreprise</th>
              <th>Type</th>
              <th>Ville</th>
              <th>Email</th>
              <th>Statut</th>
              <th>Utilisateurs</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="e in entreprises" :key="e.id">
              <td data-label="Entreprise">
                <div class="ent-cell">
                  <div class="ent-av" :class="{ 'has-logo': e.logo }">
                    <img v-if="e.logo" :src="logoUrl(e.logo)" alt="" />
                    <span v-else>{{ initiales(e.nom) }}</span>
                  </div>
                  <div class="ent-nom">{{ e.nom }}</div>
                </div>
              </td>
              <td data-label="Type" class="type-cell">{{ libelleType(e.type) }}</td>
              <td data-label="Ville">{{ e.ville || '-' }}</td>
              <td data-label="Email" class="email">{{ e.email }}</td>
              <td data-label="Statut">
                <span class="badge-statut" :class="e.statut">{{ libelleStatut(e.statut) }}</span>
              </td>
              <td data-label="Utilisateurs">{{ e.users_count ?? 0 }}</td>
              <td data-label="Actions">
                <div class="actions-cell">
                  <button v-if="peutGerer" class="btn-modifier" @click="ouvrirModal(e)">Modifier</button>
                  <button v-if="peutGerer" class="btn-toggle danger" @click="supprimer(e)">Supprimer</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
        <UiPagination :meta="meta" @change="changerPage" />
      </template>
    </div>

    <div v-if="showModal" class="modal-overlay" @click.self="fermerModal">
      <div class="modal">
        <h3 class="modal-titre">{{ entrepriseEdit ? 'Modifier l entreprise' : 'Ajouter une entreprise' }}</h3>

        <div class="champ">
          <label>Nom *</label>
          <input v-model="form.nom" type="text" placeholder="Clinique du Plateau" />
        </div>

        <div class="champ-grille">
          <div class="champ">
            <label>Type *</label>
            <select v-model="form.type">
              <option v-for="t in types" :key="t.value" :value="t.value">{{ t.label }}</option>
            </select>
          </div>
          <div class="champ">
            <label>Email *</label>
            <input v-model="form.email" type="email" placeholder="contact@clinique.bf" />
          </div>
        </div>

        <div class="champ-grille">
          <div class="champ">
            <label>Telephone</label>
            <input v-model="form.telephone" type="text" placeholder="+226 XX XX XX XX" />
          </div>
          <div class="champ">
            <label>Ville</label>
            <input v-model="form.ville" type="text" placeholder="Ouagadougou" />
          </div>
        </div>

        <div class="champ">
          <label>Adresse</label>
          <input v-model="form.adresse" type="text" placeholder="Secteur 15, Avenue..." />
        </div>

        <div class="champ">
          <label>Description</label>
          <textarea v-model="form.description" rows="3" placeholder="Description de l'etablissement..." />
        </div>

        <div class="champ" v-if="entrepriseEdit">
          <label>Statut</label>
          <select v-model="form.statut">
            <option value="actif">Actif</option>
            <option value="inactif">Inactif</option>
            <option value="suspendu">Suspendu</option>
          </select>
        </div>

        <div class="champ">
          <label>Logo</label>
          <input type="file" accept="image/*" @change="onLogoChange" />
        </div>

        <div v-if="erreur" class="erreur-box">{{ erreur }}</div>

        <div class="modal-actions">
          <button class="btn-annuler" @click="fermerModal">Annuler</button>
          <button class="btn-primary" @click="sauvegarder" :disabled="envoi">
            {{ envoi ? 'Sauvegarde...' : entrepriseEdit ? 'Modifier' : 'Creer' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import api from '../../api/client.js'
import { useAuthStore } from '../../stores/auth.js'
import { useDebouncedRef } from '../../composables/useDebouncedRef.js'
import UiPagination from '../../components/ui/UiPagination.vue'

const auth = useAuthStore()
const peutGerer = computed(() => auth.hasRole('super_admin'))

const chargement = ref(true)
const entreprises = ref([])
const meta = ref(null)
const page = ref(1)
const stats = ref({ total: 0, actives: 0, inactives: 0 })
const showModal = ref(false)
const envoi = ref(false)
const erreur = ref('')
const entrepriseEdit = ref(null)
const recherche = ref('')
const typeFiltre = ref('')
const rechercheDebounced = useDebouncedRef(recherche)
const logoFichier = ref(null)

const types = [
    { value: 'hopital', label: 'Hopital' },
    { value: 'clinique', label: 'Clinique' },
    { value: 'pharmacie', label: 'Pharmacie' },
    { value: 'laboratoire', label: 'Laboratoire' },
    { value: 'autre', label: 'Autre' },
]

const form = ref(formVide())

function formVide() {
    return { nom: '', type: 'hopital', email: '', telephone: '', adresse: '', ville: '', description: '', statut: 'actif' }
}

function libelleType(type) {
    return types.find(t => t.value === type)?.label || type || '-'
}

function libelleStatut(statut) {
    return { actif: 'Actif', inactif: 'Inactif', suspendu: 'Suspendu' }[statut] || statut
}

function initiales(nom) {
    return (nom || '').split(' ').map(w => w[0]).join('').substring(0, 2).toUpperCase()
}

function logoUrl(logo) {
    if (!logo) return ''
    if (/^https?:\/\//.test(logo)) return logo
    const apiBase = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'
    return `${apiBase.replace(/\/api\/?$/, '')}${logo.startsWith('/') ? '' : '/'}${logo}`
}

function ouvrirModal(e = null) {
    entrepriseEdit.value = e
    logoFichier.value = null
    form.value = e ? {
        nom: e.nom || '',
        type: e.type || 'hopital',
        email: e.email || '',
        telephone: e.telephone || '',
        adresse: e.adresse || '',
        ville: e.ville || '',
        description: e.description || '',
        statut: e.statut || 'actif',
    } : formVide()
    erreur.value = ''
    showModal.value = true
}

function fermerModal() {
    showModal.value = false
    entrepriseEdit.value = null
}

function onLogoChange(event) {
    logoFichier.value = event.target.files?.[0] || null
}

async function chargerEntreprises() {
    const { data } = await api.get('/entreprises', {
        params: {
            paginate: 1,
            page: page.value,
            per_page: 20,
            q: rechercheDebounced.value || undefined,
            type: typeFiltre.value || undefined,
        },
    })
    const liste = data.data ?? data
    entreprises.value = liste
    meta.value = data.data ? data : null
    stats.value.total = data.total ?? liste.length
    stats.value.actives = liste.filter(e => e.statut === 'actif').length
    stats.value.inactives = liste.filter(e => e.statut !== 'actif').length
}

async function charger() {
    chargement.value = entreprises.value.length === 0
    try {
        await chargerEntreprises()
    } catch (e) {
        window.dispatchEvent(new CustomEvent('app-error', { detail: e?.response?.data?.message || 'Une erreur est survenue' }))
    } finally {
        chargement.value = false
    }
}

function changerPage(p) {
    page.value = p
    chargerEntreprises()
}

watch([rechercheDebounced, typeFiltre], () => {
    page.value = 1
    chargerEntreprises()
})

async function sauvegarder() {
    erreur.value = ''
    if (!form.value.nom || !form.value.type || !form.value.email) {
        erreur.value = 'Nom, type et email obligatoires'
        return
    }

    envoi.value = true
    try {
        let payload = form.value
        let config = {}

        if (logoFichier.value) {
            const fd = new FormData()
            Object.entries(form.value).forEach(([k, v]) => { if (v !== null && v !== undefined) fd.append(k, v) })
            fd.append('logo', logoFichier.value)
            payload = fd
            config = { headers: { 'Content-Type': 'multipart/form-data' } }
        }

        if (entrepriseEdit.value) {
            if (logoFichier.value) payload.append('_method', 'PUT')
            const { data } = logoFichier.value
                ? await api.post(`/entreprises/${entrepriseEdit.value.id}`, payload, config)
                : await api.put(`/entreprises/${entrepriseEdit.value.id}`, payload)
            const index = entreprises.value.findIndex(e => e.id === entrepriseEdit.value.id)
            if (index >= 0) entreprises.value[index] = { ...entreprises.value[index], ...(data || {}) }
        } else {
            const { data } = await api.post('/entreprises', payload, config)
            if (data) entreprises.value.unshift(data)
        }

        chargerEntreprises()
        fermerModal()
    } catch (e) {
        const response = e.response?.data
        erreur.value = response?.errors
            ? Object.values(response.errors).flat()[0]
            : response?.message || 'Erreur lors de la sauvegarde'
    } finally {
        envoi.value = false
    }
}

async function supprimer(e) {
    if (!confirm(`Voulez-vous supprimer ${e.nom} ? Cette action est irreversible.`)) return
    try {
        await api.delete(`/entreprises/${e.id}`)
        entreprises.value = entreprises.value.filter(x => x.id !== e.id)
        chargerEntreprises()
    } catch (err) {
        window.dispatchEvent(new CustomEvent('app-error', { detail: err?.response?.data?.message || 'Une erreur est survenue' }))
    }
}

onMounted(charger)
</script>

<style scoped>
.page-header { display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: 20px; }
.titre { font-size: 18px; font-weight: 600; color: var(--color-text-primary); margin: 0; }
.btn-primary { background: #1D9E75; color: white; border: none; border-radius: 8px; padding: 9px 18px; font-size: 13px; font-weight: 500; cursor: pointer; }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
.stats-mini { display: flex; gap: 12px; margin-bottom: 16px; flex-wrap: wrap; }
.stat-mini { background: white; border: 1px solid #e5e7eb; border-radius: 8px; padding: 14px 20px; min-width: 145px; }
.stat-num { font-size: 22px; font-weight: 700; color: var(--color-text-primary); }
.stat-num.success { color: #1D9E75; }
.stat-num.danger { color: #E24B4A; }
.stat-label { font-size: 12px; color: #9ca3af; margin-top: 2px; }
.section { background: white; border-radius: 8px; border: 1px solid #e5e7eb; overflow: hidden; }
.vide { text-align: center; padding: 40px; color: #9ca3af; font-size: 13px; }
.recherche-bar { padding: 14px 16px; border-bottom: 1px solid #f3f4f6; display: flex; gap: 10px; flex-wrap: wrap; }
.recherche-input { width: min(100%, 320px); height: 36px; padding: 0 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 13px; outline: none; }
.recherche-input:focus { border-color: #1D9E75; }
.type-select { height: 36px; padding: 0 10px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 13px; color: #374151; outline: none; cursor: pointer; }
.table { width: 100%; border-collapse: collapse; }
.table th { padding: 11px 16px; text-align: left; font-size: 11px; font-weight: 600; color: #6b7280; background: #f9fafb; border-bottom: 1px solid #e5e7eb; text-transform: uppercase; }
.table td { padding: 12px 16px; font-size: 13px; color: #374151; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
.table tr:last-child td { border-bottom: none; }
.table tr:hover td { background: #f9fafb; }
.ent-cell { display: flex; align-items: center; gap: 10px; }
.ent-av { width: 34px; height: 34px; border-radius: 8px; background: #1D9E75; color: white; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 600; flex-shrink: 0; overflow: hidden; }
.ent-av.has-logo { background: #f3f4f6; }
.ent-av img { width: 100%; height: 100%; object-fit: contain; }
.ent-nom { font-size: 13px; font-weight: 500; color: var(--color-text-primary); }
.type-cell { text-transform: capitalize; }
.email { color: #6b7280; font-size: 12px; }
.badge-statut { font-size: 11px; font-weight: 600; padding: 3px 8px; border-radius: 10px; text-transform: capitalize; }
.badge-statut.actif { background: #E1F5EE; color: #085041; }
.badge-statut.inactif, .badge-statut.suspendu { background: #FCEBEB; color: #791F1F; }
.actions-cell { display: flex; gap: 6px; flex-wrap: wrap; }
.btn-modifier, .btn-toggle { font-size: 11px; padding: 4px 10px; border: none; border-radius: 6px; cursor: pointer; }
.btn-modifier { background: #E6F1FB; color: #0C447C; }
.btn-toggle.danger { background: #FCEBEB; color: #791F1F; }
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.4); display: flex; align-items: center; justify-content: center; z-index: 100; padding: 16px; }
.modal { background: white; border-radius: 8px; padding: 24px; width: 100%; max-width: 620px; max-height: 90vh; overflow-y: auto; }
.modal-titre { font-size: 16px; font-weight: 600; color: var(--color-text-primary); margin: 0 0 20px; }
.champ { display: flex; flex-direction: column; gap: 6px; margin-bottom: 14px; }
.champ label { font-size: 13px; font-weight: 500; color: #374151; }
.champ input, .champ select, .champ textarea { padding: 9px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 13px; color: var(--color-text-primary); outline: none; font-family: inherit; background: white; }
.champ input:focus, .champ select:focus, .champ textarea:focus { border-color: #1D9E75; }
.champ-grille { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
.erreur-box { background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 10px; font-size: 13px; color: #dc2626; margin-bottom: 14px; }
.modal-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 8px; }
.btn-annuler { padding: 9px 18px; border: 1px solid #e5e7eb; border-radius: 8px; background: white; font-size: 13px; cursor: pointer; color: #6b7280; }
@media (max-width: 760px) {
  .page-header { align-items: stretch; flex-direction: column; }
  .champ-grille { grid-template-columns: 1fr; }
  .stats-mini { display: grid; grid-template-columns: 1fr; }
  .stat-mini { min-width: 0; }
  .recherche-input { width: 100%; }
  .table, .table thead, .table tbody, .table tr, .table td { display: block; width: 100%; }
  .table thead { display: none; }
  .table tbody { display: flex; flex-direction: column; gap: 12px; padding: 12px; background: #f8fafc; }
  .table tr { border: 1px solid #e5e7eb; border-radius: 12px; background: white; box-shadow: 0 8px 18px rgba(15, 23, 42, 0.05); overflow: hidden; }
  .table tr:hover td { background: white; }
  .table td { display: flex; align-items: center; justify-content: space-between; gap: 16px; min-height: 46px; padding: 10px 12px; border-bottom: 1px solid #f3f4f6; text-align: right; }
  .table td::before { content: attr(data-label); color: #6b7280; font-size: 11px; font-weight: 700; text-transform: uppercase; text-align: left; }
  .table td:first-child { align-items: flex-start; flex-direction: column; text-align: left; }
  .table td:first-child::before { display: none; }
  .table td:last-child { border-bottom: none; }
  .actions-cell { justify-content: flex-end; }
}
</style>
