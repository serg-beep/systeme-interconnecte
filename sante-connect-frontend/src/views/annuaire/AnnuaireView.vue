<template>
  <div>
    <div class="page-header">
      <div>
        <h2 class="titre">Annuaire</h2>
        <p class="sous-titre">Publications et services partagés avec le réseau.</p>
      </div>
      <div class="header-actions">
        <button v-if="peutGererPublications" class="btn-primary" @click="ouvrirCreer('publication')">+ Publication</button>
      </div>
    </div>

    <div class="outils">
      <input v-model="recherche" type="text" placeholder="Rechercher..." class="recherche-input" />
    </div>

    <div class="double-colonne">

      <!-- Mes publications & services -->
      <section class="section">
        <div class="section-top">
          <h3 class="section-titre">Mes publications & services</h3>
          <span class="compteur">{{ mesMeta?.total ?? mesItems.length }}</span>
        </div>

        <div v-if="chargement" class="vide">Chargement...</div>
        <div v-else-if="mesItems.length === 0" class="vide">Rien pour l'instant</div>
        <template v-else>
          <div class="liste">
            <div v-for="item in mesItems" :key="`${item._type}-${item.id}`" class="pub-card">
              <div
                class="card-cover"
                :style="itemImage(item) ? `background-image:url(${itemImage(item)})` : ''"
              >
                <div v-if="!itemImage(item)" class="card-cover-fallback">
                  {{ itemTitre(item).charAt(0).toUpperCase() }}
                </div>
                <span class="type-tag" :class="item._type">{{ item._type === 'service' ? 'Service' : 'Publication' }}</span>
                <span v-if="item._type === 'publication'" class="statut-badge" :class="item.statut">{{ labelStatut(item.statut) }}</span>
                <span v-else class="statut-badge" :class="item.disponible ? 'publie' : 'brouillon'">{{ item.disponible ? 'Disponible' : 'Indisponible' }}</span>
              </div>
              <div class="card-body">
                <div class="card-type" v-if="item._type === 'publication' && item.type_publication">{{ item.type_publication }}</div>
                <div class="card-type" v-if="item._type === 'service' && item.categorie">{{ item.categorie }}</div>
                <div class="card-titre">{{ itemTitre(item) }}</div>
                <div class="card-extrait">{{ itemExtrait(item) }}</div>
                <div class="card-meta">{{ formatDate(item.created_at) }}</div>
                <div class="card-actions" v-if="peutGerer(item)">
                  <button class="btn-edit" @click="ouvrirEditer(item)">Modifier</button>
                  <button v-if="item._type === 'service'" class="btn-toggle" @click="toggleDisponible(item)">
                    {{ item.disponible ? 'Marquer indispo.' : 'Marquer dispo.' }}
                  </button>
                  <button class="btn-danger" @click="supprimer(item)">Supprimer</button>
                </div>
              </div>
            </div>
          </div>
          <UiPagination :meta="mesMeta" @change="changerPageMes" />
        </template>
      </section>

      <!-- Réseau -->
      <section class="section">
        <div class="section-top">
          <h3 class="section-titre">Publications & services du réseau</h3>
          <span class="compteur">{{ reseauMeta?.total ?? reseauItems.length }}</span>
        </div>

        <div v-if="chargement" class="vide">Chargement...</div>
        <div v-else-if="reseauItems.length === 0" class="vide">Aucun résultat trouvé</div>
        <template v-else>
          <div class="grille">
            <div v-for="item in reseauItems" :key="`${item._type}-${item.id}`" class="pub-card">
              <div
                class="card-cover small"
                :style="itemImage(item) ? `background-image:url(${itemImage(item)})` : ''"
              >
                <div v-if="!itemImage(item)" class="card-cover-fallback">
                  {{ itemTitre(item).charAt(0).toUpperCase() }}
                </div>
                <span class="type-tag" :class="item._type">{{ item._type === 'service' ? 'Service' : 'Publication' }}</span>
              </div>
              <div class="card-body">
                <div class="card-titre">{{ itemTitre(item) }}</div>
                <div class="card-extrait">{{ itemExtrait(item) }}</div>
                <div class="card-meta">
                  <span v-if="item._type === 'publication'">{{ item.user?.prenom }} {{ item.user?.nom }}</span>
                  <span v-else>{{ item.entreprise?.nom }}</span>
                  · <span>{{ formatDate(item.created_at) }}</span>
                </div>
              </div>
            </div>
          </div>
          <UiPagination :meta="reseauMeta" @change="changerPageReseau" />
        </template>
      </section>
    </div>

    <!-- Modal Créer / Éditer -->
    <div v-if="showModal" class="modal-overlay" @click.self="fermerModal">
      <div class="modal">
        <div class="modal-header">
          <h3 class="modal-titre">
            {{ modeEdition ? 'Modifier' : 'Nouveau' }} {{ formType === 'service' ? 'service' : 'publication' }}
          </h3>
          <button class="modal-close" @click="fermerModal">✕</button>
        </div>

        <div v-if="!modeEdition" class="type-switch">
          <button type="button" :class="{ active: formType === 'publication' }" @click="formType = 'publication'">Publication</button>
          <button type="button" :class="{ active: formType === 'service' }" @click="formType = 'service'">Service</button>
        </div>

        <!-- Zone image -->
        <div class="image-zone" @click="$refs.fileInput.click()" @dragover.prevent @drop.prevent="onDrop">
          <img v-if="imagePreview" :src="imagePreview" class="image-preview" alt="Aperçu" />
          <div v-else class="image-placeholder">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
            <span>Cliquer ou glisser une image</span>
            <small>JPG, PNG, WebP — max 5 Mo</small>
          </div>
          <input ref="fileInput" type="file" accept="image/*" class="hidden" @change="onFileChange" />
          <button v-if="imagePreview" class="image-remove" @click.stop="retirerImage">✕</button>
        </div>

        <!-- Champs Publication -->
        <div class="form-grid" v-if="formType === 'publication'">
          <div class="champ full">
            <label>Titre <span class="requis">*</span></label>
            <input v-model="form.titre" type="text" placeholder="Titre de la publication" />
          </div>

          <div class="champ">
            <label>Type de publication <span class="requis">*</span></label>
            <select v-model="form.type_publication">
              <option value="">-- Sélectionner --</option>
              <option value="Actualité">Actualité</option>
              <option value="Offre de service">Offre de service</option>
              <option value="Annonce">Annonce</option>
              <option value="Événement">Événement</option>
              <option value="Formation">Formation</option>
              <option value="Rapport">Rapport</option>
              <option value="Autre">Autre</option>
            </select>
          </div>

          <div class="champ">
            <label>Statut</label>
            <select v-model="form.statut">
              <option value="publie">Publié</option>
              <option value="brouillon">Brouillon</option>
            </select>
          </div>

          <div class="champ full">
            <label>Contenu <span class="requis">*</span></label>
            <textarea v-model="form.contenu" rows="5" placeholder="Rédigez votre publication..." />
          </div>

          <div class="champ">
            <label>Téléphone</label>
            <input v-model="form.telephone" type="tel" placeholder="Ex: +226 XX XX XX XX" />
          </div>

          <div class="champ champ-check-wrap">
            <label class="champ-check">
              <input v-model="form.visible_site" type="checkbox" />
              Visible sur le site public
            </label>
          </div>
        </div>

        <!-- Champs Service -->
        <div class="form-grid" v-else>
          <div class="champ full">
            <label>Nom du service <span class="requis">*</span></label>
            <input v-model="form.service" type="text" placeholder="Ex: Imagerie médicale" />
          </div>

          <div class="champ">
            <label>Catégorie</label>
            <input v-model="form.categorie" type="text" placeholder="Ex: Radiologie" />
          </div>

          <div class="champ">
            <label>Ville</label>
            <input v-model="form.ville" type="text" placeholder="Ex: Ouagadougou" />
          </div>

          <div class="champ">
            <label>Téléphone</label>
            <input v-model="form.telephone" type="tel" placeholder="Ex: +226 XX XX XX XX" />
          </div>

          <div class="champ full">
            <label>Description</label>
            <textarea v-model="form.description" rows="4" placeholder="Décrivez le service..." />
          </div>

          <div class="champ full">
            <label>Lien externe (optionnel)</label>
            <input v-model="form.visit_url" type="url" placeholder="https://..." />
          </div>

          <div class="champ full">
            <label>Tags (séparés par des virgules)</label>
            <input v-model="form.tagsTexte" type="text" placeholder="urgence, 24h, gratuit" />
          </div>

          <div class="champ">
            <label>Statut de publication</label>
            <select v-model="form.etat_publication">
              <option value="publie">Publié</option>
              <option value="brouillon">Brouillon</option>
              <option value="archive">Archivé</option>
            </select>
          </div>

          <div class="champ champ-check-wrap">
            <label class="champ-check">
              <input v-model="form.disponible" type="checkbox" />
              Service disponible
            </label>
          </div>
        </div>

        <div v-if="erreur" class="erreur-box">{{ erreur }}</div>

        <div class="modal-actions">
          <button class="btn-annuler" @click="fermerModal">Annuler</button>
          <button class="btn-primary" @click="modeEdition ? sauvegarder() : creer()" :disabled="envoi">
            {{ envoi ? 'Enregistrement...' : (modeEdition ? 'Enregistrer' : 'Publier') }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import api from '../../api/client.js'
import { useAuthStore } from '../../stores/auth.js'
import { useDebouncedRef } from '../../composables/useDebouncedRef.js'
import UiPagination from '../../components/ui/UiPagination.vue'

const PER_PAGE = 6

const auth = useAuthStore()
const peutGererAnnuaire = computed(() => auth.hasPermission('gerer_annuaires') || auth.hasRole('super_admin'))
const peutGererPublications = computed(() => auth.hasPermission('gerer_publications') || auth.hasRole('super_admin'))
function peutGerer(item) {
  return item._type === 'service' ? peutGererAnnuaire.value : peutGererPublications.value
}

const chargement       = ref(true)
const mesItems         = ref([])
const reseauItems      = ref([])
const mesPage          = ref(1)
const mesMeta          = ref(null)
const reseauPage       = ref(1)
const reseauMeta       = ref(null)
const showModal        = ref(false)
const modeEdition      = ref(false)
const itemEdite        = ref(null)
const formType         = ref('publication')
const envoi            = ref(false)
const erreur           = ref('')
const recherche        = ref('')
const rechercheDebounced = useDebouncedRef(recherche)
const imagePreview     = ref(null)
const imageFichier     = ref(null)
const fileInput        = ref(null)

const formVide = () => ({
  titre: '', type_publication: '', contenu: '', telephone: '', statut: 'publie', visible_site: false,
  service: '', categorie: '', ville: '', description: '', visit_url: '', tagsTexte: '',
  etat_publication: 'publie', disponible: true,
})
const form = ref(formVide())

function itemTitre(item) { return item._type === 'service' ? item.service : item.titre }
function itemExtrait(item) { return item._type === 'service' ? item.description : item.extrait }

function imageUrl(path) {
  if (!path) return ''
  if (/^https?:\/\//.test(path)) return path
  const base = (import.meta.env.VITE_API_URL || 'http://localhost:8000/api').replace(/\/api\/?$/, '')
  return `${base}${path.startsWith('/') ? '' : '/'}${path}`
}

function itemImage(item) {
  return item._type === 'service' ? imageUrl(item.cover_image) : (item.image_url || '')
}

function labelStatut(s) {
  return s === 'publie' ? 'Publié' : s === 'brouillon' ? 'Brouillon' : s === 'archive' ? 'Archivé' : s
}

function formatDate(d) {
  if (!d) return ''
  return new Date(d).toLocaleDateString('fr-FR', { day: 'numeric', month: 'short', year: 'numeric' })
}

function ouvrirCreer(type) {
  modeEdition.value  = false
  itemEdite.value    = null
  formType.value     = type
  form.value         = formVide()
  imagePreview.value = null
  imageFichier.value = null
  showModal.value    = true
}

function ouvrirEditer(item) {
  modeEdition.value = true
  itemEdite.value   = item
  formType.value    = item._type

  if (item._type === 'service') {
    form.value = {
      ...formVide(),
      service:          item.service,
      categorie:        item.categorie || '',
      ville:            item.ville || '',
      telephone:        item.telephone || '',
      description:      item.description || '',
      visit_url:        item.visit_url || '',
      tagsTexte:        (item.tags || []).join(', '),
      etat_publication: item.etat_publication || 'publie',
      disponible:       !!item.disponible,
    }
    imagePreview.value = item.cover_image ? imageUrl(item.cover_image) : null
  } else {
    form.value = {
      ...formVide(),
      titre:            item.titre,
      type_publication: item.type_publication || '',
      contenu:          item.contenu,
      telephone:        item.telephone || '',
      statut:           item.statut,
      visible_site:     item.visible_site,
    }
    imagePreview.value = item.image_url || null
  }

  imageFichier.value = null
  showModal.value     = true
}

function fermerModal() {
  showModal.value    = false
  erreur.value       = ''
  imageFichier.value = null
}

function onFileChange(e) {
  const file = e.target.files[0]
  if (file) setFichier(file)
}

function onDrop(e) {
  const file = e.dataTransfer.files[0]
  if (file && file.type.startsWith('image/')) setFichier(file)
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

function tagsArray() {
  return form.value.tagsTexte.split(',').map(t => t.trim()).filter(Boolean)
}

async function creer() {
  erreur.value = ''
  if (formType.value === 'publication') {
    if (!form.value.titre.trim())     { erreur.value = 'Le titre est obligatoire'; return }
    if (!form.value.type_publication) { erreur.value = 'Le type de publication est obligatoire'; return }
    if (!form.value.contenu.trim())   { erreur.value = 'Le contenu est obligatoire'; return }
  } else if (!form.value.service.trim()) {
    erreur.value = 'Le nom du service est obligatoire'; return
  }

  envoi.value = true
  try {
    if (formType.value === 'publication') {
      const fd = new FormData()
      fd.append('titre', form.value.titre)
      fd.append('type_publication', form.value.type_publication)
      fd.append('contenu', form.value.contenu)
      fd.append('telephone', form.value.telephone)
      fd.append('statut', form.value.statut)
      fd.append('visible_site', form.value.visible_site ? '1' : '0')
      if (imageFichier.value) fd.append('image', imageFichier.value)
      const { data } = await api.post('/publications', fd)
      mesPage.value = 1
      await chargerMes()
      if (data.statut === 'publie') { reseauPage.value = 1; await chargerReseau() }
    } else {
      const fd = new FormData()
      fd.append('service', form.value.service)
      fd.append('categorie', form.value.categorie)
      fd.append('ville', form.value.ville)
      fd.append('telephone', form.value.telephone)
      fd.append('description', form.value.description)
      fd.append('visit_url', form.value.visit_url)
      fd.append('tags', JSON.stringify(tagsArray()))
      fd.append('etat_publication', form.value.etat_publication)
      fd.append('disponible', form.value.disponible ? '1' : '0')
      if (imageFichier.value) fd.append('cover_image', imageFichier.value)
      const { data } = await api.post('/annuaire', fd)
      mesPage.value = 1
      await chargerMes()
      if (data.etat_publication === 'publie') { reseauPage.value = 1; await chargerReseau() }
    }
    fermerModal()
  } catch (e) {
    erreur.value = e.response?.data?.message || 'Erreur lors de la création'
  } finally {
    envoi.value = false
  }
}

async function sauvegarder() {
  erreur.value = ''
  if (formType.value === 'publication') {
    if (!form.value.titre.trim())     { erreur.value = 'Le titre est obligatoire'; return }
    if (!form.value.type_publication) { erreur.value = 'Le type de publication est obligatoire'; return }
    if (!form.value.contenu.trim())   { erreur.value = 'Le contenu est obligatoire'; return }
  } else if (!form.value.service.trim()) {
    erreur.value = 'Le nom du service est obligatoire'; return
  }

  envoi.value = true
  try {
    if (formType.value === 'publication') {
      const fd = new FormData()
      fd.append('titre', form.value.titre)
      fd.append('type_publication', form.value.type_publication)
      fd.append('contenu', form.value.contenu)
      fd.append('telephone', form.value.telephone)
      fd.append('statut', form.value.statut)
      fd.append('visible_site', form.value.visible_site ? '1' : '0')
      if (imageFichier.value) fd.append('image', imageFichier.value)
      fd.append('_method', 'PUT')
      await api.post(`/publications/${itemEdite.value.id}`, fd)
    } else {
      await api.put(`/annuaire/${itemEdite.value.id}`, {
        service:          form.value.service,
        categorie:        form.value.categorie,
        ville:            form.value.ville,
        telephone:        form.value.telephone,
        description:      form.value.description,
        visit_url:        form.value.visit_url,
        tags:             tagsArray(),
        etat_publication: form.value.etat_publication,
        disponible:       form.value.disponible,
      })
      if (imageFichier.value) {
        const fd = new FormData()
        fd.append('image', imageFichier.value)
        await api.post(`/annuaire/${itemEdite.value.id}/image`, fd)
      }
    }
    await Promise.all([chargerMes(), chargerReseau()])
    fermerModal()
  } catch (e) {
    erreur.value = e.response?.data?.message || 'Erreur lors de la modification'
  } finally {
    envoi.value = false
  }
}

async function supprimer(item) {
  if (!confirm('Supprimer cet élément ?')) return

  try {
    if (item._type === 'service') await api.delete(`/annuaire/${item.id}`)
    else await api.delete(`/publications/${item.id}`)
    await Promise.all([chargerMes(), chargerReseau()])
  } catch {
    window.dispatchEvent(new CustomEvent('app-error', { detail: 'Erreur lors de la suppression' }))
  }
}

async function toggleDisponible(item) {
  try {
    const { data } = await api.post(`/annuaire/${item.id}/disponibilite`)
    const idx = mesItems.value.findIndex(i => i._type === 'service' && i.id === item.id)
    if (idx >= 0) mesItems.value[idx] = { ...data.service, _type: 'service' }
  } catch {
    window.dispatchEvent(new CustomEvent('app-error', { detail: 'Erreur lors du changement de disponibilité' }))
  }
}

function mergeSort(pubs, svcs) {
  return [
    ...pubs.map(p => ({ ...p, _type: 'publication' })),
    ...svcs.map(s => ({ ...s, _type: 'service' })),
  ].sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
}

async function chargerMes() {
  const [rp, rs] = await Promise.all([
    api.get('/publications', { params: { paginate: 1, page: mesPage.value, per_page: PER_PAGE } }),
    api.get('/annuaire/mes-services', { params: { paginate: 1, page: mesPage.value, per_page: PER_PAGE } }),
  ])
  const pubMeta = rp.data.data ? rp.data : null
  const svcMeta = rs.data.data ? rs.data : null
  mesItems.value = mergeSort(rp.data.data ?? rp.data, rs.data.data ?? rs.data)
  mesMeta.value = {
    current_page: mesPage.value,
    last_page: Math.max(pubMeta?.last_page || 1, svcMeta?.last_page || 1),
    total: (pubMeta?.total || 0) + (svcMeta?.total || 0),
  }
}

async function chargerReseau() {
  const q = rechercheDebounced.value || undefined
  const [rp, rs] = await Promise.all([
    api.get('/site/publications', { params: { page: reseauPage.value, per_page: PER_PAGE, q } }),
    api.get('/annuaire', { params: { page: reseauPage.value, per_page: PER_PAGE, q } }),
  ])
  const pubMeta = rp.data.data ? rp.data : null
  const svcMeta = rs.data.data ? rs.data : null
  reseauItems.value = mergeSort(rp.data.data ?? rp.data, rs.data.data ?? rs.data)
  reseauMeta.value = {
    current_page: reseauPage.value,
    last_page: Math.max(pubMeta?.last_page || 1, svcMeta?.last_page || 1),
    total: (pubMeta?.total || 0) + (svcMeta?.total || 0),
  }
}

async function charger() {
  chargement.value = true
  try {
    await Promise.all([chargerMes(), chargerReseau()])
  } catch (e) {
    window.dispatchEvent(new CustomEvent('app-error', { detail: e?.response?.data?.message || 'Une erreur est survenue' }))
  } finally {
    chargement.value = false
  }
}

function changerPageMes(page) {
  mesPage.value = page
  chargerMes()
}

function changerPageReseau(page) {
  reseauPage.value = page
  chargerReseau()
}

watch(rechercheDebounced, () => {
  reseauPage.value = 1
  chargerReseau()
})

onMounted(charger)
</script>

<style scoped>
.page-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; margin-bottom: 20px; }
.titre { font-size: 18px; font-weight: 600; color: var(--color-text-primary); margin: 0; }
.sous-titre { margin: 6px 0 0; color: var(--color-text-muted); font-size: 13px; }
.header-actions { display: flex; gap: 8px; flex-wrap: wrap; }

.outils { margin-bottom: 16px; }
.recherche-input { width: 100%; max-width: 420px; height: 40px; padding: 0 14px; border: 1px solid var(--color-border); border-radius: 8px; font-size: 13px; outline: none; }
.recherche-input:focus { border-color: var(--color-primary); }

.double-colonne { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.section { background: white; border-radius: 12px; border: 1px solid var(--color-border); padding: 18px; }
.section-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; }
.section-titre { font-size: 15px; font-weight: 600; color: var(--color-text-primary); margin: 0; }
.compteur { background: #eef6ff; color: #1d4ed8; border-radius: 999px; padding: 4px 10px; font-size: 12px; font-weight: 600; }
.vide { text-align: center; padding: 40px; color: var(--color-text-muted); font-size: 13px; background: var(--color-surface-muted); border-radius: 12px; }

.liste, .grille { display: grid; gap: 14px; }
.grille { grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); }

.pub-card { background: white; border-radius: 12px; border: 1px solid var(--color-border); overflow: hidden; }

.card-cover {
  height: 110px;
  background: linear-gradient(135deg, var(--color-primary-soft), var(--color-accent-soft));
  background-size: cover;
  background-position: center;
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
}
.card-cover.small { height: 80px; }

.card-cover-fallback { font-size: 28px; font-weight: 800; color: var(--color-primary); opacity: 0.45; }

.type-tag {
  position: absolute; top: 8px; left: 8px;
  font-size: 10px; font-weight: 700; padding: 3px 8px; border-radius: 10px;
  background: rgba(255,255,255,0.92); color: #334155;
}
.type-tag.service { background: #ede9fe; color: #5b21b6; }
.type-tag.publication { background: #dbeafe; color: #1d4ed8; }

.statut-badge {
  position: absolute; top: 8px; right: 8px;
  font-size: 10px; font-weight: 700; padding: 3px 8px; border-radius: 10px;
}
.statut-badge.publie    { background: #d1fae5; color: #065f46; }
.statut-badge.brouillon { background: #fef9c3; color: #854d0e; }
.statut-badge.archive   { background: #f1f5f9; color: #64748b; }

.card-body { padding: 14px; }
.card-type { display: inline-block; font-size: 10.5px; font-weight: 700; padding: 2px 9px; border-radius: 999px; background: #eff6ff; color: #1a6fc4; margin-bottom: 5px; }
.card-titre { font-size: 14px; font-weight: 600; color: var(--color-text-primary); margin-bottom: 5px; }
.card-extrait { font-size: 12.5px; color: var(--color-text-secondary); line-height: 1.45; margin-bottom: 8px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.card-meta { font-size: 11.5px; color: var(--color-text-muted); margin-bottom: 10px; }
.card-actions { display: flex; gap: 6px; flex-wrap: wrap; }

.btn-primary { background: var(--color-primary); color: white; border: none; border-radius: 8px; padding: 9px 18px; font-size: 13px; font-weight: 600; cursor: pointer; transition: background 0.15s; }
.btn-primary:hover { background: var(--color-primary-hover); }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-secondary { background: var(--color-surface-muted); color: var(--color-text-primary); border: 1px solid var(--color-border); border-radius: 8px; padding: 9px 18px; font-size: 13px; font-weight: 600; cursor: pointer; }
.btn-edit { background: #f0f9ff; color: #0369a1; border: none; border-radius: 8px; padding: 7px 10px; font-size: 12px; cursor: pointer; font-weight: 500; }
.btn-toggle { background: #f7fee7; color: #4d7c0f; border: none; border-radius: 8px; padding: 7px 10px; font-size: 12px; cursor: pointer; font-weight: 500; }
.btn-danger { background: #fef2f2; color: #b91c1c; border: none; border-radius: 8px; padding: 7px 10px; font-size: 12px; cursor: pointer; }
.btn-annuler { padding: 9px 18px; border: 1px solid var(--color-border); border-radius: 8px; background: white; font-size: 13px; cursor: pointer; color: var(--color-text-muted); }

.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.45); display: flex; align-items: center; justify-content: center; z-index: 100; padding: 16px; }
.modal { background: white; border-radius: 18px; width: 100%; max-width: 560px; max-height: 90vh; overflow-y: auto; }
.modal-header { display: flex; align-items: center; justify-content: space-between; padding: 22px 24px 0; }
.modal-titre { font-size: 16px; font-weight: 700; color: var(--color-text-primary); margin: 0; text-transform: capitalize; }
.modal-close { background: none; border: none; font-size: 18px; cursor: pointer; color: var(--color-text-muted); line-height: 1; }

.type-switch { display: flex; gap: 8px; margin: 16px 24px 0; }
.type-switch button { flex: 1; padding: 9px 12px; border: 1px solid var(--color-border); border-radius: 8px; background: white; font-size: 13px; font-weight: 600; color: var(--color-text-muted); cursor: pointer; transition: all 0.15s; }
.type-switch button.active { background: var(--color-primary-soft, #eef6ff); border-color: var(--color-primary); color: var(--color-primary); }

.image-zone {
  margin: 18px 24px 0;
  border: 2px dashed var(--color-border);
  border-radius: 12px;
  height: 150px;
  display: flex; align-items: center; justify-content: center;
  cursor: pointer; position: relative; overflow: hidden; transition: border-color 0.15s;
}
.image-zone:hover { border-color: var(--color-primary); }
.image-preview { width: 100%; height: 100%; object-fit: cover; }
.image-placeholder { display: flex; flex-direction: column; align-items: center; gap: 6px; color: var(--color-text-muted); font-size: 13px; text-align: center; padding: 16px; }
.image-placeholder svg { opacity: 0.6; }
.image-placeholder small { font-size: 11px; }
.image-remove { position: absolute; top: 8px; right: 8px; width: 26px; height: 26px; border-radius: 50%; background: rgba(0,0,0,0.5); color: white; border: none; cursor: pointer; font-size: 13px; display: flex; align-items: center; justify-content: center; }

.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; padding: 18px 24px 0; }
.champ { display: flex; flex-direction: column; gap: 5px; }
.champ.full { grid-column: 1 / -1; }
.champ label { font-size: 12.5px; font-weight: 600; color: var(--color-text-secondary); }
.requis { color: var(--color-danger); }
.champ input, .champ textarea, .champ select {
  padding: 9px 12px; border: 1px solid var(--color-border); border-radius: 8px;
  font-size: 13px; color: var(--color-text-primary); outline: none; font-family: inherit; background: white; transition: border-color 0.12s;
}
.champ input:focus, .champ textarea:focus, .champ select:focus { border-color: var(--color-primary); }
.champ-check-wrap { justify-content: flex-end; }
.champ-check { display: flex; align-items: center; gap: 8px; font-size: 13px; color: var(--color-text-secondary); cursor: pointer; margin-top: auto; padding-bottom: 2px; }

.erreur-box { background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 10px; font-size: 13px; color: #dc2626; margin: 14px 24px 0; }
.modal-actions { display: flex; gap: 10px; justify-content: flex-end; padding: 18px 24px 22px; }

.hidden { display: none; }

@media (max-width: 960px) { .double-colonne { grid-template-columns: 1fr; } }
@media (max-width: 540px) { .form-grid { grid-template-columns: 1fr; } .champ.full { grid-column: 1; } }
</style>
