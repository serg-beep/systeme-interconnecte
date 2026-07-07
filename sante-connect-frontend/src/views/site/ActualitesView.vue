<template>
  <div class="actualites-page">

    <!-- En-tête -->
    <div class="page-header">
      <div class="header-inner">
        <div class="header-badge">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 20H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h10l6 6v8a2 2 0 0 1-2 2z"/></svg>
          Actualités santé
        </div>
        <h1 class="page-titre">Informations du secteur médical</h1>
        <p class="page-sous-titre">Restez informé des dernières avancées et actualités de la santé au Burkina Faso, en Afrique et dans le monde.</p>
        <div class="search-bar">
          <svg class="search-ico" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
          <input v-model="q" @input="rechercherDebounced" type="text" placeholder="Rechercher une actualité..." class="search-input" />
          <button v-if="q" @click="effacerRecherche" class="search-clear">✕</button>
        </div>
      </div>
    </div>

    <div class="page-body">

      <!-- Filtres catégories -->
      <div class="cats-bar">
        <button class="cat-btn" :class="{ active: categorie === '' }"        @click="setCategorie('')">🌐 Toutes</button>
        <button class="cat-btn" :class="{ active: categorie === 'burkina' }" @click="setCategorie('burkina')">🇧🇫 Burkina Faso</button>
        <button class="cat-btn" :class="{ active: categorie === 'afrique' }" @click="setCategorie('afrique')">🌍 Afrique</button>
        <button class="cat-btn" :class="{ active: categorie === 'monde' }"   @click="setCategorie('monde')">🌏 Monde</button>
      </div>

      <!-- Compteur -->
      <div class="results-top" v-if="!loading">
        <span class="results-count">{{ meta?.total ?? actualites.length }} actualité{{ (meta?.total ?? actualites.length) > 1 ? 's' : '' }}</span>
      </div>

      <!-- Skeleton -->
      <div v-if="loading" class="articles-grid">
        <div class="skeleton-card" v-for="n in 9" :key="n"></div>
      </div>

      <!-- À la une + grille -->
      <template v-else-if="actualites.length > 0">
        <article class="une-card" v-if="!q && !categorie">
          <div class="une-img">
            <img v-if="une.image_url" :src="une.image_url" :alt="une.titre" class="une-photo" />
            <div v-else class="une-emoji">🏥</div>
            <span class="une-cat-badge" :class="une.categorie">{{ labelCat(une.categorie) }}</span>
          </div>
          <div class="une-body">
            <div class="une-label">À la une</div>
            <h2 class="une-titre">{{ une.titre }}</h2>
            <p class="une-resume">{{ une.extrait }}</p>
            <div class="une-footer">
              <div class="article-meta">
                <span class="meta-source">{{ une.source }}</span>
                <span class="meta-date">· {{ formatDate(une.date_publication) }}</span>
              </div>
              <a v-if="une.url_externe" :href="une.url_externe" target="_blank" class="btn-lire">Lire la source →</a>
            </div>
          </div>
        </article>

        <div class="articles-header">
          <h3 class="articles-titre">{{ q || categorie ? 'Résultats' : 'Toutes les actualités' }}</h3>
        </div>

        <div class="articles-grid">
          <article v-for="(art, i) in (q || categorie ? actualites : actualites.slice(1))" :key="art.id" class="article-card">
            <div class="art-img" :class="!art.image_url ? `bg-${(i % 5) + 1}` : ''">
              <img v-if="art.image_url" :src="art.image_url" :alt="art.titre" class="art-photo" />
              <div v-else class="art-emoji">{{ emojiCat(art.categorie) }}</div>
              <span class="art-cat-badge" :class="art.categorie">{{ labelCat(art.categorie) }}</span>
            </div>
            <div class="art-body">
              <div class="art-date">{{ formatDate(art.date_publication) }}</div>
              <h3 class="art-titre">{{ art.titre }}</h3>
              <p class="art-resume">{{ art.extrait }}</p>
              <div class="art-footer">
                <span class="art-source">{{ art.source }}</span>
                <a v-if="art.url_externe" :href="art.url_externe" target="_blank" class="art-lire">Lire →</a>
              </div>
            </div>
          </article>
        </div>
      </template>

      <!-- Vide -->
      <div v-else-if="!loading" class="vide">
        <div class="vide-icon"><svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg></div>
        <p>Aucune actualité trouvée{{ q ? ` pour "${q}"` : '' }}.</p>
        <button @click="q = ''; charger(1)" class="btn-reset">Réinitialiser</button>
      </div>

      <!-- Pagination -->
      <div class="pagination" v-if="meta && meta.last_page > 1">
        <button @click="charger(meta.current_page - 1)" :disabled="meta.current_page === 1" class="page-btn">← Précédent</button>
        <div class="page-nums">
          <button v-for="p in pages" :key="p" class="page-num" :class="{ active: p === meta.current_page }" @click="charger(p)">{{ p }}</button>
        </div>
        <button @click="charger(meta.current_page + 1)" :disabled="meta.current_page === meta.last_page" class="page-btn">Suivant →</button>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { API_BASE } from '../../api/client.js'
import { useReveal } from '../../composables/useReveal.js'
useReveal()

const actualites = ref([])
const meta       = ref(null)
const loading    = ref(true)
const q          = ref('')
const categorie  = ref('')

const une = computed(() => actualites.value[0] || null)

const pages = computed(() => {
  if (!meta.value) return []
  const cur = meta.value.current_page, last = meta.value.last_page
  const range = []
  for (let i = Math.max(1, cur - 2); i <= Math.min(last, cur + 2); i++) range.push(i)
  return range
})

function labelCat(c) { return { burkina: 'Burkina', afrique: 'Afrique', monde: 'Monde' }[c] ?? c }
function emojiCat(c) { return { burkina: '🇧🇫', afrique: '🌍', monde: '🌏' }[c] ?? '📰' }
function formatDate(d) {
  if (!d) return ''
  return new Date(d).toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' })
}

let debounceTimer = null
function rechercherDebounced() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => charger(1), 400)
}

function effacerRecherche() { q.value = ''; charger(1) }
function setCategorie(val) { categorie.value = val; charger(1) }

async function charger(page = 1) {
  loading.value = true
  try {
    const params = new URLSearchParams({ page, per_page: 9 })
    if (q.value)         params.set('q', q.value)
    if (categorie.value) params.set('categorie', categorie.value)
    const res  = await fetch(`${API_BASE}/site/actualites?${params}`)
    const data = await res.json()
    actualites.value = data.data || []
    meta.value       = data
  } finally { loading.value = false }
}

onMounted(charger)
</script>

<style scoped>
.actualites-page { min-height: 100vh; background: #f5f8fd; }

.page-header { background: linear-gradient(135deg, #f0fdf9 0%, #ecfdf5 40%, #eff6ff 100%); border-bottom: 1px solid #e2e8f0; padding: 56px 28px 44px; }
.header-inner { max-width: 680px; margin: 0 auto; text-align: center; }
.header-badge { display: inline-flex; align-items: center; gap: 7px; padding: 5px 14px; background: white; border: 1px solid #e2e8f0; border-radius: 999px; font-size: 12px; font-weight: 600; color: #1a6fc4; margin-bottom: 22px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
.page-titre { font-size: clamp(24px, 4vw, 36px); font-weight: 900; color: #0f172a; margin: 0 0 12px; letter-spacing: -0.03em; line-height: 1.15; }
.page-sous-titre { font-size: 15px; color: #64748b; margin: 0 0 28px; line-height: 1.55; }
.search-bar { display: flex; align-items: center; background: white; border: 1.5px solid #e2e8f0; border-radius: 14px; padding: 0 16px; box-shadow: 0 4px 16px rgba(0,0,0,0.06); transition: border-color 0.15s; }
.search-bar:focus-within { border-color: #1a6fc4; }
.search-ico { color: #94a3b8; flex-shrink: 0; }
.search-input { flex: 1; border: none; outline: none; padding: 14px 12px; font-size: 14px; background: transparent; color: #0f172a; }
.search-input::placeholder { color: #94a3b8; }
.search-clear { background: #f1f5f9; border: none; color: #64748b; cursor: pointer; font-size: 12px; width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }

.page-body { max-width: 1100px; margin: 0 auto; padding: 36px 28px 72px; }

.cats-bar { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 24px; }
.cat-btn { padding: 8px 18px; border-radius: 999px; border: 1.5px solid #e2e8f0; background: white; font-size: 13px; font-weight: 500; color: #64748b; cursor: pointer; transition: all 0.14s; }
.cat-btn:hover { border-color: #1a6fc4; color: #1a6fc4; }
.cat-btn.active { background: #1a6fc4; border-color: #1a6fc4; color: white; font-weight: 600; }

.results-top { margin-bottom: 20px; }
.results-count { font-size: 13px; color: #94a3b8; font-weight: 500; }

/* À la une */
.une-card { display: grid; grid-template-columns: 1fr 1.2fr; background: white; border: 1px solid #e8edf4; border-radius: 20px; overflow: hidden; margin-bottom: 32px; box-shadow: 0 4px 24px rgba(15,23,42,0.07); }
.une-img { position: relative; min-height: 260px; background: linear-gradient(135deg, #0a2f5f, #0e4f91, #0d9488); display: flex; align-items: center; justify-content: center; overflow: hidden; }
.une-photo { width: 100%; height: 100%; object-fit: cover; position: absolute; inset: 0; }
.une-emoji { font-size: 72px; opacity: 0.8; position: relative; z-index: 1; }
.une-body { padding: 36px 32px; display: flex; flex-direction: column; justify-content: center; }
.une-label { font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: #1a6fc4; margin-bottom: 10px; }
.une-titre { font-size: 21px; font-weight: 800; color: #0f172a; line-height: 1.3; margin: 0 0 12px; }
.une-resume { font-size: 14px; color: #64748b; line-height: 1.65; margin: 0 0 20px; }
.une-footer { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; }
.article-meta { font-size: 13px; color: #94a3b8; }
.meta-source { font-weight: 600; color: #475569; }
.btn-lire { display: inline-flex; align-items: center; padding: 9px 18px; background: #1a6fc4; border-radius: 10px; color: white; font-size: 13px; font-weight: 600; text-decoration: none; }
.btn-lire:hover { background: #0e4f91; }

.une-cat-badge { position: absolute; top: 12px; left: 12px; z-index: 2; font-size: 10.5px; font-weight: 700; padding: 4px 10px; border-radius: 999px; }
.une-cat-badge.burkina { background: #fff7ed; color: #c2410c; }
.une-cat-badge.afrique { background: #f5f3ff; color: #6d28d9; }
.une-cat-badge.monde   { background: #eff6ff; color: #1d4ed8; }

.articles-header { margin-bottom: 20px; }
.articles-titre { font-size: 16px; font-weight: 700; color: #0f172a; margin: 0; }

.articles-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }

.article-card { background: white; border: 1px solid #e8edf4; border-radius: 16px; overflow: hidden; display: flex; flex-direction: column; transition: box-shadow 0.25s ease, transform 0.25s cubic-bezier(.22,.68,0,1.2), border-color 0.2s; }
.article-card:hover { box-shadow: 0 16px 48px rgba(15,23,42,0.13); transform: translateY(-6px); border-color: #bfdbfe; }

.art-img { height: 140px; display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden; }
.art-photo { width: 100%; height: 100%; object-fit: cover; }
.bg-1 { background: linear-gradient(135deg, #dbeafe, #bfdbfe); }
.bg-2 { background: linear-gradient(135deg, #d1fae5, #a7f3d0); }
.bg-3 { background: linear-gradient(135deg, #fce7f3, #fbcfe8); }
.bg-4 { background: linear-gradient(135deg, #fff7ed, #fed7aa); }
.bg-5 { background: linear-gradient(135deg, #f3f0ff, #ddd6fe); }
.art-emoji { font-size: 42px; }

.art-cat-badge { position: absolute; top: 10px; left: 10px; font-size: 10.5px; font-weight: 700; padding: 3px 10px; border-radius: 999px; }
.art-cat-badge.burkina { background: #dcfce7; color: #166534; }
.art-cat-badge.afrique { background: #f5f3ff; color: #6d28d9; }
.art-cat-badge.monde   { background: #eff6ff; color: #1d4ed8; }

.art-body { padding: 18px; flex: 1; display: flex; flex-direction: column; gap: 6px; }
.art-date { font-size: 11.5px; color: #94a3b8; }
.art-titre { font-size: 14.5px; font-weight: 700; color: #0f172a; line-height: 1.35; margin: 0; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.art-resume { font-size: 13px; color: #64748b; line-height: 1.55; margin: 0; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.art-footer { display: flex; align-items: center; justify-content: space-between; margin-top: auto; padding-top: 12px; border-top: 1px solid #f1f5f9; }
.art-source { font-size: 12px; color: #94a3b8; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 150px; }
.art-lire { font-size: 12.5px; font-weight: 700; color: #1a6fc4; text-decoration: none; flex-shrink: 0; }

.skeleton-card { height: 300px; background: linear-gradient(90deg, #f1f5f9 25%, #e8edf4 50%, #f1f5f9 75%); background-size: 200% 100%; border-radius: 16px; animation: shimmer 1.4s infinite; }
@keyframes shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }

.vide { text-align: center; padding: 72px 24px; color: #94a3b8; display: flex; flex-direction: column; align-items: center; gap: 14px; }
.vide-icon { width: 64px; height: 64px; border-radius: 50%; background: #f8fafc; border: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: center; }
.vide p { font-size: 14px; margin: 0; }
.btn-reset { padding: 9px 22px; border: 1.5px solid #e2e8f0; border-radius: 10px; background: white; font-size: 13px; cursor: pointer; color: #64748b; }

.pagination { display: flex; align-items: center; justify-content: center; gap: 8px; margin-top: 40px; }
.page-btn { padding: 9px 20px; border: 1.5px solid #e2e8f0; border-radius: 10px; background: white; font-size: 13px; cursor: pointer; font-weight: 500; color: #475569; transition: all 0.15s; }
.page-btn:hover:not(:disabled) { border-color: #1a6fc4; color: #1a6fc4; background: #eff6ff; }
.page-btn:disabled { opacity: 0.35; cursor: default; }
.page-nums { display: flex; gap: 4px; }
.page-num { width: 36px; height: 36px; border-radius: 8px; border: 1.5px solid #e2e8f0; background: white; font-size: 13px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.12s; }
.page-num:hover { border-color: #1a6fc4; color: #1a6fc4; }
.page-num.active { background: #1a6fc4; border-color: #1a6fc4; color: white; font-weight: 600; }

@media (max-width: 760px) {
  .une-card { grid-template-columns: 1fr; }
  .une-img { min-height: 160px; }
  .une-body { padding: 24px 20px; }
}
@media (max-width: 560px) {
  .articles-grid { grid-template-columns: 1fr; }
  .page-body { padding: 28px 20px 52px; }
  .page-header { padding: 40px 20px 36px; }
}
</style>
