<template>
  <div>
    <!-- En-tête -->
    <div class="page-header">
      <div>
        <h2 class="titre">Actualités Santé</h2>
        <p class="sous-titre">Gérez les actualités publiées sur le site public.</p>
      </div>
      <div class="header-actions">
        <button class="btn-fetch" @click="fetchRss" :disabled="fetching">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>
          </svg>
          {{ fetching ? 'Récupération...' : 'Fetch RSS' }}
        </button>
        <button class="btn-primary" @click="ouvrirCreer">+ Nouvelle actualité</button>
      </div>
    </div>

    <!-- Stats -->
    <div class="stats-bar">
      <div class="stat-chip">
        <span class="stat-n">{{ stats.total }}</span>
        <span class="stat-l">Total</span>
      </div>
      <div class="stat-chip publie">
        <span class="stat-n">{{ stats.publiees }}</span>
        <span class="stat-l">Publiées</span>
      </div>
      <div class="stat-chip auto">
        <span class="stat-n">{{ stats.auto }}</span>
        <span class="stat-l">Auto-fetchées</span>
      </div>
      <div class="stat-chip burkina">
        <span class="stat-n">{{ stats.burkina }}</span>
        <span class="stat-l">Burkina</span>
      </div>
      <div class="stat-chip afrique">
        <span class="stat-n">{{ stats.afrique }}</span>
        <span class="stat-l">Afrique</span>
      </div>
      <div class="stat-chip monde">
        <span class="stat-n">{{ stats.monde }}</span>
        <span class="stat-l">Monde</span>
      </div>
    </div>

    <!-- Filtres -->
    <div class="filtres">
      <input v-model="q" @input="rechercherDebounced" type="text" placeholder="Rechercher..." class="search-input" />
      <select v-model="filtreCategorie" @change="charger(1)" class="filtre-select">
        <option value="">Toutes catégories</option>
        <option value="burkina">Burkina Faso</option>
        <option value="afrique">Afrique</option>
        <option value="monde">Monde</option>
      </select>
      <select v-model="filtrePublie" @change="charger(1)" class="filtre-select">
        <option value="">Tous statuts</option>
        <option value="1">Publiées</option>
        <option value="0">Dépubliées</option>
      </select>
    </div>

    <!-- Message fetch -->
    <div v-if="fetchMessage" class="fetch-result">{{ fetchMessage }}</div>

    <!-- Table -->
    <div class="table-wrap">
      <div v-if="chargement" class="vide">Chargement...</div>
      <div v-else-if="actualites.length === 0" class="vide">Aucune actualité trouvée.</div>

      <table v-else class="table">
        <thead>
          <tr>
            <th>Image</th>
            <th>Titre</th>
            <th>Catégorie</th>
            <th>Source</th>
            <th>Date</th>
            <th>Statut</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in actualites" :key="item.id">
            <td>
              <div class="img-cell">
                <img v-if="item.image_url" :src="item.image_url" :alt="item.titre" class="table-img" />
                <div v-else class="table-img-fallback">{{ item.titre.charAt(0) }}</div>
              </div>
            </td>
            <td>
              <div class="cell-titre">{{ item.titre }}</div>
              <div class="cell-resume">{{ item.extrait }}</div>
              <span v-if="item.auto_fetched" class="badge-auto">RSS</span>
            </td>
            <td><span class="badge-cat" :class="item.categorie">{{ labelCat(item.categorie) }}</span></td>
            <td class="cell-source">{{ item.source }}</td>
            <td class="cell-date">{{ formatDate(item.date_publication) }}</td>
            <td>
              <button class="toggle-btn" :class="item.publie ? 'active' : 'inactive'" @click="togglePublie(item)">
                {{ item.publie ? 'Publiée' : 'Dépubliée' }}
              </button>
            </td>
            <td>
              <div class="actions">
                <button class="btn-edit" @click="ouvrirEditer(item)" title="Modifier">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                </button>
                <button class="btn-del" @click="supprimer(item)" title="Supprimer">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div class="pagination" v-if="meta && meta.last_page > 1">
      <button @click="charger(meta.current_page - 1)" :disabled="meta.current_page === 1" class="page-btn">← Préc.</button>
      <span class="page-info">{{ meta.current_page }} / {{ meta.last_page }}</span>
      <button @click="charger(meta.current_page + 1)" :disabled="meta.current_page === meta.last_page" class="page-btn">Suiv. →</button>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="modal-overlay" @click.self="fermerModal">
      <div class="modal">
        <div class="modal-header">
          <h3 class="modal-titre">{{ modeEdition ? 'Modifier l\'actualité' : 'Nouvelle actualité' }}</h3>
          <button class="modal-close" @click="fermerModal">✕</button>
        </div>

        <!-- Upload image -->
        <div class="image-zone" @click="$refs.fileInput.click()" @dragover.prevent @drop.prevent="onDrop">
          <img v-if="imagePreview" :src="imagePreview" class="image-preview" />
          <div v-else class="image-placeholder">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
            <span>Cliquer ou glisser une image</span>
          </div>
          <input ref="fileInput" type="file" accept="image/*" class="hidden" @change="onFileChange" />
          <button v-if="imagePreview" class="image-remove" @click.stop="retirerImage">✕</button>
        </div>

        <div class="form-grid">
          <div class="champ full">
            <label>Titre <span class="requis">*</span></label>
            <input v-model="form.titre" type="text" placeholder="Titre de l'actualité" />
          </div>
          <div class="champ">
            <label>Catégorie <span class="requis">*</span></label>
            <select v-model="form.categorie">
              <option value="burkina">Burkina Faso</option>
              <option value="afrique">Afrique</option>
              <option value="monde">Monde</option>
            </select>
          </div>
          <div class="champ">
            <label>Source <span class="requis">*</span></label>
            <input v-model="form.source" type="text" placeholder="Ex : Ministère de la Santé" />
          </div>
          <div class="champ full">
            <label>Résumé <span class="requis">*</span></label>
            <textarea v-model="form.resume" rows="3" placeholder="Résumé de l'actualité..." />
          </div>
          <div class="champ full">
            <label>Contenu complet</label>
            <textarea v-model="form.contenu" rows="5" placeholder="Contenu détaillé (optionnel)..." />
          </div>
          <div class="champ">
            <label>URL source</label>
            <input v-model="form.url_source" type="url" placeholder="https://..." />
          </div>
          <div class="champ">
            <label>Date de publication</label>
            <input v-model="form.date_publication" type="datetime-local" />
          </div>
          <div class="champ champ-check-wrap">
            <label class="champ-check">
              <input v-model="form.publie" type="checkbox" />
              Publier immédiatement
            </label>
          </div>
        </div>

        <div v-if="erreur" class="erreur-box">{{ erreur }}</div>

        <div class="modal-actions">
          <button class="btn-annuler" @click="fermerModal">Annuler</button>
          <button class="btn-primary" @click="modeEdition ? sauvegarder() : creer()" :disabled="envoi">
            {{ envoi ? 'Enregistrement...' : (modeEdition ? 'Enregistrer' : 'Créer') }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '../../api/client.js'
import { useDebouncedRef } from '../../composables/useDebouncedRef.js'

const actualites     = ref([])
const meta           = ref(null)
const chargement     = ref(true)
const showModal      = ref(false)
const modeEdition    = ref(false)
const itemEdite      = ref(null)
const envoi          = ref(false)
const erreur         = ref('')
const fetching       = ref(false)
const fetchMessage   = ref('')
const q              = ref('')
const rechercheDebounced = useDebouncedRef(q)
const filtreCategorie = ref('')
const filtrePublie    = ref('')
const imagePreview   = ref(null)
const imageFichier   = ref(null)
const fileInput      = ref(null)

const formVide = () => ({
  titre: '', resume: '', contenu: '', categorie: 'burkina',
  source: '', url_source: '', date_publication: '', publie: false,
})
const form = ref(formVide())

const stats = computed(() => ({
  total:    meta.value?.total ?? actualites.value.length,
  publiees: actualites.value.filter(a => a.publie).length,
  auto:     actualites.value.filter(a => a.auto_fetched).length,
  burkina:  actualites.value.filter(a => a.categorie === 'burkina').length,
  afrique:  actualites.value.filter(a => a.categorie === 'afrique').length,
  monde:    actualites.value.filter(a => a.categorie === 'monde').length,
}))

function labelCat(c) {
  return { burkina: 'Burkina', afrique: 'Afrique', monde: 'Monde' }[c] ?? c
}

function formatDate(d) {
  if (!d) return ''
  return new Date(d).toLocaleDateString('fr-FR', { day: 'numeric', month: 'short', year: 'numeric' })
}

let debounceTimer = null
function rechercherDebounced2() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => charger(1), 400)
}

async function charger(page = 1) {
  chargement.value = true
  try {
    const params = { page, per_page: 15 }
    if (q.value)                   params.q         = q.value
    if (filtreCategorie.value)     params.categorie  = filtreCategorie.value
    if (filtrePublie.value !== '')  params.publie     = filtrePublie.value
    const { data } = await api.get('/actualites', { params })
    actualites.value = data.data || []
    meta.value       = data
  } catch (e) {
    window.dispatchEvent(new CustomEvent('app-error', { detail: 'Erreur chargement actualités' }))
  } finally {
    chargement.value = false
  }
}

async function fetchRss() {
  fetching.value  = true
  fetchMessage.value = ''
  try {
    const { data } = await api.post('/actualites/fetch')
    fetchMessage.value = data.message || 'Fetch terminé'
    await charger(1)
  } catch (e) {
    fetchMessage.value = 'Erreur lors du fetch RSS'
  } finally {
    fetching.value = false
    setTimeout(() => { fetchMessage.value = '' }, 6000)
  }
}

async function togglePublie(item) {
  const prev = item.publie
  item.publie = !item.publie
  try {
    await api.post(`/actualites/${item.id}/toggle`)
  } catch {
    item.publie = prev
  }
}

function ouvrirCreer() {
  modeEdition.value  = false
  itemEdite.value    = null
  form.value         = formVide()
  imagePreview.value = null
  imageFichier.value = null
  showModal.value    = true
}

function ouvrirEditer(item) {
  modeEdition.value  = true
  itemEdite.value    = item
  form.value = {
    titre:            item.titre,
    resume:           item.resume || '',
    contenu:          item.contenu || '',
    categorie:        item.categorie,
    source:           item.source,
    url_source:       item.url_source || '',
    date_publication: item.date_publication ? item.date_publication.slice(0, 16) : '',
    publie:           item.publie,
  }
  imagePreview.value = item.image_url || null
  imageFichier.value = null
  showModal.value    = true
}

function fermerModal() { showModal.value = false; erreur.value = '' }

function onFileChange(e) {
  const file = e.target.files[0]
  if (file) setFichier(file)
}
function onDrop(e) {
  const file = e.dataTransfer.files[0]
  if (file?.type.startsWith('image/')) setFichier(file)
}
function setFichier(file) {
  imageFichier.value = file
  const reader = new FileReader()
  reader.onload = ev => { imagePreview.value = ev.target.result }
  reader.readAsDataURL(file)
}
function retirerImage() {
  imagePreview.value = null
  imageFichier.value = null
  if (fileInput.value) fileInput.value.value = ''
}

function buildFd() {
  const fd = new FormData()
  Object.entries(form.value).forEach(([k, v]) => {
    if (v !== null && v !== undefined && v !== '') fd.append(k, v === true ? '1' : v === false ? '0' : v)
  })
  if (imageFichier.value) fd.append('image', imageFichier.value)
  return fd
}

async function creer() {
  erreur.value = ''
  if (!form.value.titre.trim() || !form.value.resume.trim() || !form.value.source.trim()) {
    erreur.value = 'Titre, résumé et source sont obligatoires.'
    return
  }
  envoi.value = true
  try {
    const { data } = await api.post('/actualites', buildFd())
    actualites.value.unshift(data)
    fermerModal()
  } catch (e) {
    erreur.value = e.response?.data?.message || 'Erreur lors de la création'
  } finally { envoi.value = false }
}

async function sauvegarder() {
  erreur.value = ''
  if (!form.value.titre.trim() || !form.value.resume.trim() || !form.value.source.trim()) {
    erreur.value = 'Titre, résumé et source sont obligatoires.'
    return
  }
  envoi.value = true
  try {
    const fd = buildFd()
    fd.append('_method', 'PUT')
    const { data } = await api.post(`/actualites/${itemEdite.value.id}`, fd)
    const idx = actualites.value.findIndex(a => a.id === data.id)
    if (idx >= 0) actualites.value[idx] = data
    fermerModal()
  } catch (e) {
    erreur.value = e.response?.data?.message || 'Erreur lors de la modification'
  } finally { envoi.value = false }
}

async function supprimer(item) {
  if (!confirm(`Supprimer "${item.titre}" ?`)) return
  const idx = actualites.value.findIndex(a => a.id === item.id)
  if (idx >= 0) actualites.value.splice(idx, 1)
  try {
    await api.delete(`/actualites/${item.id}`)
  } catch {
    actualites.value.splice(idx, 0, item)
    window.dispatchEvent(new CustomEvent('app-error', { detail: 'Erreur lors de la suppression' }))
  }
}

onMounted(charger)
</script>

<style scoped>
.page-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; margin-bottom: 20px; }
.titre { font-size: 18px; font-weight: 600; color: var(--color-text-primary); margin: 0; }
.sous-titre { margin: 4px 0 0; color: var(--color-text-muted); font-size: 13px; }
.header-actions { display: flex; gap: 10px; align-items: center; }

/* Stats */
.stats-bar { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 18px; }
.stat-chip { background: white; border: 1px solid var(--color-border); border-radius: 10px; padding: 10px 16px; text-align: center; }
.stat-chip.publie   { border-color: #bbf7d0; background: #f0fdf4; }
.stat-chip.auto     { border-color: #bfdbfe; background: #eff6ff; }
.stat-chip.burkina  { border-color: #fed7aa; background: #fff7ed; }
.stat-chip.afrique  { border-color: #ddd6fe; background: #f5f3ff; }
.stat-chip.monde    { border-color: #e2e8f0; background: #f8fafc; }
.stat-n { display: block; font-size: 20px; font-weight: 800; color: var(--color-text-primary); }
.stat-l { font-size: 11px; color: var(--color-text-muted); }

/* Filtres */
.filtres { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 16px; }
.search-input { flex: 1; min-width: 200px; height: 38px; padding: 0 12px; border: 1px solid var(--color-border); border-radius: 8px; font-size: 13px; outline: none; }
.search-input:focus { border-color: var(--color-primary); }
.filtre-select { height: 38px; padding: 0 10px; border: 1px solid var(--color-border); border-radius: 8px; font-size: 13px; background: white; }

.fetch-result { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 10px 14px; font-size: 13px; color: #15803d; margin-bottom: 14px; }

/* Table */
.table-wrap { background: white; border: 1px solid var(--color-border); border-radius: 12px; overflow: hidden; }
.table { width: 100%; border-collapse: collapse; }
.table th { padding: 11px 14px; font-size: 12px; font-weight: 600; color: var(--color-text-muted); text-align: left; background: var(--color-surface-muted); border-bottom: 1px solid var(--color-border); }
.table td { padding: 12px 14px; font-size: 13px; border-bottom: 1px solid var(--color-border); vertical-align: middle; }
.table tr:last-child td { border-bottom: none; }
.table tr:hover td { background: #f8fafc; }

.img-cell { width: 48px; }
.table-img { width: 48px; height: 48px; object-fit: cover; border-radius: 8px; }
.table-img-fallback { width: 48px; height: 48px; border-radius: 8px; background: linear-gradient(135deg, #dbeafe, #d1fae5); display: flex; align-items: center; justify-content: center; font-size: 18px; font-weight: 800; color: #1a6fc4; }

.cell-titre { font-weight: 600; color: var(--color-text-primary); max-width: 280px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.cell-resume { font-size: 12px; color: var(--color-text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 280px; margin-top: 2px; }
.cell-source { font-size: 12px; color: var(--color-text-secondary); max-width: 120px; }
.cell-date { font-size: 12px; color: var(--color-text-muted); white-space: nowrap; }

.badge-auto { display: inline-block; font-size: 10px; font-weight: 700; padding: 1px 6px; border-radius: 4px; background: #dbeafe; color: #1d4ed8; margin-top: 3px; }

.badge-cat { display: inline-block; font-size: 11px; font-weight: 600; padding: 3px 9px; border-radius: 999px; }
.badge-cat.burkina { background: #fff7ed; color: #c2410c; }
.badge-cat.afrique { background: #f5f3ff; color: #6d28d9; }
.badge-cat.monde   { background: #eff6ff; color: #1d4ed8; }

.toggle-btn { font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 999px; border: none; cursor: pointer; transition: all 0.12s; }
.toggle-btn.active   { background: #dcfce7; color: #15803d; }
.toggle-btn.inactive { background: #f1f5f9; color: #94a3b8; }

.actions { display: flex; gap: 6px; }
.btn-edit { background: #f0f9ff; color: #0369a1; border: none; border-radius: 7px; padding: 6px 8px; cursor: pointer; }
.btn-del  { background: #fef2f2; color: #b91c1c; border: none; border-radius: 7px; padding: 6px 8px; cursor: pointer; }

/* Pagination */
.pagination { display: flex; align-items: center; justify-content: center; gap: 12px; margin-top: 16px; }
.page-btn { padding: 7px 16px; border: 1px solid var(--color-border); border-radius: 8px; background: white; font-size: 13px; cursor: pointer; }
.page-btn:disabled { opacity: 0.4; cursor: default; }
.page-info { font-size: 13px; color: var(--color-text-muted); }

/* Boutons */
.btn-primary { background: var(--color-primary); color: white; border: none; border-radius: 8px; padding: 9px 18px; font-size: 13px; font-weight: 600; cursor: pointer; }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-fetch { display: flex; align-items: center; gap: 7px; background: #f0f9ff; color: #0369a1; border: 1px solid #bae6fd; border-radius: 8px; padding: 8px 14px; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.12s; }
.btn-fetch:hover:not(:disabled) { background: #e0f2fe; }
.btn-fetch:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-annuler { padding: 9px 18px; border: 1px solid var(--color-border); border-radius: 8px; background: white; font-size: 13px; cursor: pointer; }

/* Modal */
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.45); display: flex; align-items: center; justify-content: center; z-index: 100; padding: 16px; }
.modal { background: white; border-radius: 18px; width: 100%; max-width: 620px; max-height: 92vh; overflow-y: auto; }
.modal-header { display: flex; justify-content: space-between; align-items: center; padding: 22px 24px 0; }
.modal-titre { font-size: 16px; font-weight: 700; margin: 0; }
.modal-close { background: none; border: none; font-size: 18px; cursor: pointer; color: var(--color-text-muted); }

.image-zone { margin: 16px 24px 0; border: 2px dashed var(--color-border); border-radius: 12px; height: 130px; display: flex; align-items: center; justify-content: center; cursor: pointer; position: relative; overflow: hidden; }
.image-zone:hover { border-color: var(--color-primary); }
.image-preview { width: 100%; height: 100%; object-fit: cover; }
.image-placeholder { display: flex; flex-direction: column; align-items: center; gap: 6px; color: var(--color-text-muted); font-size: 13px; }
.image-remove { position: absolute; top: 8px; right: 8px; width: 24px; height: 24px; border-radius: 50%; background: rgba(0,0,0,0.5); color: white; border: none; cursor: pointer; font-size: 12px; display: flex; align-items: center; justify-content: center; }

.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; padding: 16px 24px 0; }
.champ { display: flex; flex-direction: column; gap: 5px; }
.champ.full { grid-column: 1 / -1; }
.champ label { font-size: 12.5px; font-weight: 600; color: var(--color-text-secondary); }
.requis { color: var(--color-danger); }
.champ input, .champ textarea, .champ select { padding: 9px 12px; border: 1px solid var(--color-border); border-radius: 8px; font-size: 13px; outline: none; font-family: inherit; background: white; transition: border-color 0.12s; }
.champ input:focus, .champ textarea:focus, .champ select:focus { border-color: var(--color-primary); }
.champ-check-wrap { justify-content: flex-end; }
.champ-check { display: flex; align-items: center; gap: 8px; font-size: 13px; cursor: pointer; margin-top: auto; }

.erreur-box { background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 10px; font-size: 13px; color: #dc2626; margin: 12px 24px 0; }
.modal-actions { display: flex; gap: 10px; justify-content: flex-end; padding: 16px 24px 22px; }
.hidden { display: none; }

.vide { text-align: center; padding: 48px; color: var(--color-text-muted); font-size: 13px; }
</style>
