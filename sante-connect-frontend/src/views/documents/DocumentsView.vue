<template>
  <div>
    <div class="page-header">
      <h2 class="titre">Documents</h2>
      <button class="btn-primary" @click="showModal = true">Ajouter document</button>
    </div>

    <div class="onglets">
      <button :class="{ active: onglet === 'mes' }" @click="afficherMesDocuments">Mes documents</button>
      <button :class="{ active: onglet === 'partages' }" @click="afficherPartages">Partagés</button>
    </div>

    <div v-if="messageErreur" class="erreur-box">{{ messageErreur }}</div>
    <SkeletonList v-if="chargement" grid :count="6" />
    <div v-else-if="documentsCourants.length === 0" class="vide">Aucun document</div>
    <div v-else class="docs-grille">
      <div v-for="d in documentsCourants" :key="d.id" class="doc-card">
        <div class="doc-top">
          <div class="doc-icone" :class="d.type">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="13 2 13 9 20 9"/></svg>
          </div>
          <div class="doc-info">
            <div class="doc-titre">{{ d.titre }}</div>
            <div class="doc-meta">{{ d.type }} · {{ d.taille || 'N/A' }}</div>
            <div class="doc-ent">{{ d.entreprise?.nom }}</div>
          </div>
        </div>
        <div class="doc-footer">
          <span class="doc-visibilite" :class="d.visibilite">{{ d.visibilite }}</span>
          <div class="doc-actions">
            <button class="btn-dl" @click="telecharger(d)">Télécharger</button>
            <button v-if="estMonDoc(d)" class="btn-sup" @click="supprimer(d.id)">Supprimer</button>
          </div>
        </div>
      </div>
    </div>
    <UiPagination :meta="metaCourante" @change="changerPage" />

    <!-- Modal upload -->
    <div v-if="showModal" class="modal-overlay" @click.self="showModal = false">
      <div class="modal">
        <h3 class="modal-titre">Ajouter un document</h3>

        <div class="champ">
          <label>Titre *</label>
          <input v-model="form.titre" type="text" placeholder="Nom du document" />
        </div>

        <!-- Zone upload fichier -->
        <div class="champ">
          <label>Fichier *</label>
          <div class="upload-zone"
            :class="{ 'drag-over': isDragging }"
            @dragover.prevent="isDragging = true"
            @dragleave="isDragging = false"
            @drop.prevent="onDrop"
            @click="$refs.fileInput.click()">
            <div v-if="!fichierSelectionne">
              <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="1.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
              <p class="upload-texte">Glisser-déposer ou <span class="upload-lien">choisir un fichier</span></p>
              <p class="upload-sous">PDF, Word, Excel, Images — Max 10MB</p>
            </div>
            <div v-else class="fichier-selectionne">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#1D9E75" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
              <span>{{ fichierSelectionne.name }}</span>
              <button class="btn-remove" @click.stop="fichierSelectionne = null">×</button>
            </div>
            <input ref="fileInput" type="file" style="display:none"
              accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg"
              @change="onFileChange" />
          </div>
        </div>

        <div class="champ-grille">
          <div class="champ">
            <label>Type</label>
            <select v-model="form.type">
              <option value="rapport">Rapport</option>
              <option value="certificat">Certificat</option>
              <option value="ordonnance">Ordonnance</option>
              <option value="analyse">Analyse</option>
              <option value="note">Note</option>
              <option value="autre">Autre</option>
            </select>
          </div>
          <div class="champ">
            <label>Visibilité</label>
            <select v-model="form.visibilite">
              <option value="prive">Privé</option>
              <option value="partenaires">Partenaires</option>
              <option value="public">Public</option>
            </select>
          </div>
        </div>

        <!-- Barre de progression -->
        <div v-if="progression > 0 && progression < 100" class="progress-wrap">
          <div class="progress-bar" :style="{ width: progression + '%' }"></div>
          <span class="progress-label">{{ progression }}%</span>
        </div>

        <div v-if="erreur" class="erreur-box">{{ erreur }}</div>

        <div class="modal-actions">
          <button class="btn-annuler" @click="showModal = false">Annuler</button>
          <button class="btn-primary" @click="uploader" :disabled="envoi || !fichierSelectionne">
            {{ envoi ? `Upload ${progression}%...` : 'Uploader' }}
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

const auth    = useAuthStore()
const chargement = ref(true)
const docs    = ref([])
const docsPartages = ref([])
const showModal = ref(false)
const envoi   = ref(false)
const erreur  = ref('')
const messageErreur = ref('')
const onglet  = ref('mes')
const isDragging = ref(false)
const fichierSelectionne = ref(null)
const progression = ref(0)
const fileInput = ref(null)
const pageMes = ref(1)
const pagePartages = ref(1)
const metaMes = ref(null)
const metaPartages = ref(null)

const form = ref({ titre: '', type: 'rapport', visibilite: 'prive' })

const documentsCourants = computed(() =>
    onglet.value === 'mes' ? docs.value : docsPartages.value
)

const metaCourante = computed(() =>
    onglet.value === 'mes' ? metaMes.value : metaPartages.value
)

function estMonDoc(d) { return d.entreprise_id === auth.entreprise?.id }

function onFileChange(e) {
    const file = e.target.files[0]
    if (file) fichierSelectionne.value = file
}

function onDrop(e) {
    isDragging.value = false
    const file = e.dataTransfer.files[0]
    if (file) fichierSelectionne.value = file
}

let abortCtrl = null

function abortAndNew() {
    if (abortCtrl) abortCtrl.abort()
    abortCtrl = new AbortController()
    return abortCtrl.signal
}

async function charger() {
    const signal = abortAndNew()
    chargement.value = docs.value.length === 0
    messageErreur.value = ''
    try {
        const { data } = await api.get('/documents', { params: { paginate: 1, page: pageMes.value, per_page: 12 }, signal })
        docs.value = data.data || data
        metaMes.value = data.data ? data : null
    } catch (e) {
        if (e.name === 'AbortError') return
        messageErreur.value = apiErrorMessage(e, 'Impossible de charger les documents')
    } finally { chargement.value = false }
}

async function chargerPartages() {
    // Ne pas recharger si les données sont déjà là et qu'on est sur la première page
    if (docsPartages.value.length > 0 && pagePartages.value === 1) {
        chargement.value = false
        return
    }
    const signal = abortAndNew()
    chargement.value = docsPartages.value.length === 0
    messageErreur.value = ''
    try {
        const { data } = await api.get('/documents/partages', { params: { paginate: 1, page: pagePartages.value, per_page: 12 }, signal })
        docsPartages.value = data.data || data
        metaPartages.value = data.data ? data : null
    } catch (e) {
        if (e.name === 'AbortError') return
        messageErreur.value = apiErrorMessage(e, 'Impossible de charger les documents partages')
    } finally { chargement.value = false }
}

function afficherMesDocuments() {
    if (onglet.value === 'mes') return
    onglet.value = 'mes'
    pageMes.value = 1
    messageErreur.value = ''
    charger()
}

function afficherPartages() {
    onglet.value = 'partages'
    chargerPartages()
}

function changerPage(page) {
    if (onglet.value === 'mes') {
        pageMes.value = page
        charger()
        return
    }

    pagePartages.value = page
    chargerPartages()
}

async function uploader() {
    if (!fichierSelectionne.value || !form.value.titre) {
        erreur.value = 'Titre et fichier obligatoires'
        return
    }
    envoi.value   = true
    erreur.value  = ''
    progression.value = 0

    const formData = new FormData()
    formData.append('titre',      form.value.titre)
    formData.append('type',       form.value.type)
    formData.append('visibilite', form.value.visibilite)
    formData.append('fichier',    fichierSelectionne.value)

    try {
        const { data } = await api.post('/documents', formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
            onUploadProgress: (e) => {
                progression.value = Math.round((e.loaded * 100) / e.total)
            }
        })
        if (data) docs.value.unshift(data)
        showModal.value = false
        fichierSelectionne.value = null
        form.value = { titre: '', type: 'rapport', visibilite: 'prive' }
        progression.value = 0
    } catch (e) {
        erreur.value = apiErrorMessage(e, 'Erreur lors de l\'upload')
    } finally { envoi.value = false }
}

async function telecharger(doc) {
    try {
        const { data } = await api.get(`/documents/${doc.id}/telecharger`)
        if (data.url) window.open(data.url, '_blank')
    } catch (e) { messageErreur.value = apiErrorMessage(e, 'Telechargement impossible') }
}

async function supprimer(id) {
    if (!confirm('Supprimer ce document ?')) return
    const index = docs.value.findIndex(d => d.id === id)
    const deleted = index >= 0 ? docs.value[index] : null
    if (index >= 0) docs.value.splice(index, 1)

    try { await api.delete(`/documents/${id}`) }
    catch (e) {
        if (deleted) docs.value.splice(index, 0, deleted)
        messageErreur.value = apiErrorMessage(e, 'Suppression impossible')
    }
}

onMounted(charger)
onUnmounted(() => { if (abortCtrl) abortCtrl.abort() })
</script>

<style scoped>
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.titre { font-size: 18px; font-weight: 600; color: var(--color-text-primary); margin: 0; }
.btn-primary { background: #1D9E75; color: white; border: none; border-radius: 8px; padding: 9px 18px; font-size: 13px; font-weight: 500; cursor: pointer; }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
.onglets { display: flex; gap: 4px; background: #f3f4f6; border-radius: 10px; padding: 4px; margin-bottom: 16px; width: fit-content; }
.onglets button { padding: 6px 16px; border: none; border-radius: 8px; font-size: 13px; cursor: pointer; background: none; color: #6b7280; }
.onglets button.active { background: white; color: var(--color-text-primary); font-weight: 500; }
.vide { text-align: center; padding: 40px; color: #9ca3af; font-size: 13px; background: white; border-radius: 12px; border: 1px solid #e5e7eb; }
.docs-grille { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 12px; }
.doc-card { background: white; border-radius: 12px; border: 1px solid #e5e7eb; padding: 16px; display: flex; flex-direction: column; gap: 12px; }
.doc-top { display: flex; gap: 12px; }
.doc-icone { width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.doc-icone.rapport     { background: #E6F1FB; color: #0C447C; }
.doc-icone.certificat  { background: #E1F5EE; color: #085041; }
.doc-icone.ordonnance  { background: #EEEDFE; color: #3C3489; }
.doc-icone.analyse     { background: #FAEEDA; color: #633806; }
.doc-icone.note        { background: #FFF4E6; color: #9A6B2A; }
.doc-icone.autre       { background: #f3f4f6; color: #6b7280; }
.doc-titre { font-size: 13px; font-weight: 500; color: var(--color-text-primary); }
.doc-meta  { font-size: 11px; color: #9ca3af; margin-top: 2px; text-transform: capitalize; }
.doc-ent   { font-size: 11px; color: #6b7280; margin-top: 2px; }
.doc-footer { display: flex; justify-content: space-between; align-items: center; padding-top: 10px; border-top: 1px solid #f3f4f6; }
.doc-visibilite { font-size: 10px; font-weight: 600; padding: 3px 8px; border-radius: 8px; text-transform: capitalize; }
.doc-visibilite.prive       { background: #f3f4f6; color: #6b7280; }
.doc-visibilite.partenaires { background: #FAEEDA; color: #633806; }
.doc-visibilite.public      { background: #E1F5EE; color: #085041; }
.doc-actions { display: flex; gap: 6px; }
.btn-dl  { font-size: 11px; padding: 4px 10px; background: #E6F1FB; color: #0C447C; border: none; border-radius: 6px; cursor: pointer; }
.btn-sup { font-size: 11px; padding: 4px 10px; background: #FCEBEB; color: #791F1F; border: none; border-radius: 6px; cursor: pointer; }
.upload-zone { border: 2px dashed #d1d5db; border-radius: 10px; padding: 28px; text-align: center; cursor: pointer; transition: all 0.2s; }
.upload-zone:hover, .upload-zone.drag-over { border-color: #1D9E75; background: #f0fdf4; }
.upload-texte { font-size: 13px; color: #6b7280; margin: 10px 0 4px; }
.upload-lien  { color: #1D9E75; font-weight: 500; }
.upload-sous  { font-size: 11px; color: #9ca3af; }
.fichier-selectionne { display: flex; align-items: center; gap: 8px; justify-content: center; font-size: 13px; color: #1D9E75; }
.btn-remove { background: none; border: none; cursor: pointer; color: #9ca3af; font-size: 18px; }
.champ-grille { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.champ { display: flex; flex-direction: column; gap: 6px; margin-bottom: 14px; }
.champ label { font-size: 13px; font-weight: 500; color: #374151; }
.champ input, .champ select { padding: 9px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 13px; outline: none; }
.champ input:focus, .champ select:focus { border-color: #1D9E75; }
.progress-wrap { background: #f3f4f6; border-radius: 8px; height: 8px; margin-bottom: 14px; overflow: hidden; position: relative; }
.progress-bar  { height: 100%; background: #1D9E75; border-radius: 8px; transition: width 0.3s; }
.progress-label { position: absolute; right: 0; top: -18px; font-size: 11px; color: #1D9E75; }
.erreur-box { background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 10px; font-size: 13px; color: #dc2626; margin-bottom: 14px; }
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.4); display: flex; align-items: center; justify-content: center; z-index: 100; padding: 16px; }
.modal { background: white; border-radius: 16px; padding: 28px; width: 100%; max-width: 500px; max-height: 90vh; overflow-y: auto; }
.modal-titre { font-size: 16px; font-weight: 600; color: var(--color-text-primary); margin: 0 0 20px; }
.modal-actions { display: flex; gap: 10px; justify-content: flex-end; }
.btn-annuler { padding: 9px 18px; border: 1px solid #e5e7eb; border-radius: 8px; background: white; font-size: 13px; cursor: pointer; color: #6b7280; }
</style>
