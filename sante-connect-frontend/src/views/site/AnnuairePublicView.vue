<template>
  <div class="annuaire-public">

    <!-- En-tête -->
    <div class="page-header">
      <div class="header-inner">
        <div class="header-badge">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
          Annuaire de santé
        </div>
        <h1 class="page-titre">Publications des professionnels<br />de santé</h1>
        <p class="page-sous-titre">Parcourez les publications partagées par les établissements et professionnels de santé</p>

        <div class="search-bar">
          <svg class="search-ico" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
          <input v-model="q" @input="rechercherDebounced" type="text"
            placeholder="Rechercher une publication, un service..."
            class="search-input" />
          <button v-if="q" @click="effacerRecherche" class="search-clear">✕</button>
        </div>

      </div>
    </div>

    <div class="page-body">

      <!-- Filtres -->
      <aside class="filtres">
        <div class="filtre-groupe">
          <div class="filtre-titre">Trier par</div>
          <button class="filtre-btn" :class="{ active: tri === 'recent' }" @click="tri = 'recent'; charger()">Plus récents</button>
          <button class="filtre-btn" :class="{ active: tri === 'alpha' }"  @click="tri = 'alpha';  charger()">Alphabétique</button>
        </div>
      </aside>

      <!-- Résultats -->
      <div class="results">

        <div class="results-top" v-if="!loading">
          <span class="results-count">{{ items.length }} résultat{{ items.length > 1 ? 's' : '' }}</span>
        </div>

        <div v-if="loading" class="cards-grid">
          <div class="skeleton-card" v-for="n in 9" :key="n"></div>
        </div>

        <div v-else-if="items.length === 0" class="vide">
          <div class="vide-icon"><svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg></div>
          <p>Aucun résultat trouvé.</p>
          <button class="btn-reset" @click="reset">Réinitialiser</button>
        </div>

        <div v-else class="cards-grid">
          <!-- Publication -->
          <router-link
            v-for="item in items" :key="`${item._type}-${item.id}`"
            :to="item._type === 'publication' ? `/publication/${item.id}` : `/service/${item.id}`"
            class="item-card"
          >
            <figure class="card-cover">
              <img v-if="item.image_url || item.cover_image"
                :src="item.image_url || imageUrl(item.cover_image)"
                :alt="item.titre || item.service" class="card-img" />
              <div v-else class="card-fallback">{{ (item.titre || item.service || '?').charAt(0) }}</div>
              <span class="type-badge" :class="item._type === 'publication' ? 'pub' : 'svc'">
                {{ item._type === 'publication' ? 'Publication' : 'Service' }}
              </span>
              <span v-if="item._type === 'service'" class="dispo-badge" :class="item.disponible ? 'dispo' : 'indispo'">
                {{ item.disponible ? 'Disponible' : 'Indisponible' }}
              </span>
            </figure>

            <div class="card-body">
              <div class="card-cat" v-if="item.categorie">{{ item.categorie }}</div>
              <h3 class="card-titre">{{ item.titre || item.service }}</h3>
              <p class="card-desc">{{ item.extrait || item.description }}</p>

              <!-- Auteur (publication) -->
              <div class="card-auteur" v-if="item._type === 'publication' && item.user">
                <div class="auteur-avatar">{{ initiales(item.user) }}</div>
                <div>
                  <div class="auteur-nom">{{ item.user.prenom }} {{ item.user.nom }}</div>
                  <div class="auteur-date">{{ formatDate(item.created_at) }}</div>
                </div>
              </div>

              <!-- Entreprise (service) -->
              <div class="card-ent" v-if="item._type === 'service' && item.entreprise">
                <div class="ent-avatar">{{ item.entreprise.nom?.charAt(0) || '?' }}</div>
                <div>
                  <div class="ent-nom">{{ item.entreprise.nom }}</div>
                  <div class="ent-type">{{ item.entreprise.type }}</div>
                </div>
              </div>

              <div class="card-footer">
                <span class="card-cta">{{ item._type === 'publication' ? 'Lire →' : 'Voir le détail →' }}</span>
              </div>
            </div>
          </router-link>
        </div>

      </div>
    </div>

    <!-- CTA -->
    <div class="cta-banner">
      <div class="cta-banner-inner">
        <div>
          <h3 class="cta-titre">Vous êtes professionnel de santé ?</h3>
          <p class="cta-desc">Connectez-vous pour publier vos actualités et services, accéder à la messagerie et à toutes les fonctionnalités.</p>
        </div>
        <div class="cta-actions">
          <router-link to="/login" class="btn-login-banner">Se connecter</router-link>
          <router-link to="/register" class="btn-register-banner">Créer un compte</router-link>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { API_BASE } from '../../api/client.js'
import { useReveal } from '../../composables/useReveal.js'
useReveal()

const items   = ref([])
const loading = ref(true)
const q       = ref('')
const tri     = ref('recent')

let debounceTimer = null
function rechercherDebounced() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => charger(), 400)
}

async function charger() {
  loading.value = true
  try {
    const params = new URLSearchParams({ per_page: 50 })
    if (q.value) params.set('q', q.value)

    const [resPubs, resSvcs] = await Promise.all([
      fetch(`${API_BASE}/site/publications?${params}`),
      fetch(`${API_BASE}/annuaire?${params}&etat_publication=publie`),
    ])

    const dataPubs = await resPubs.json()
    const dataSvcs = await resSvcs.json()

    const pubs = (dataPubs.data || []).map(p => ({ ...p, _type: 'publication' }))
    const svcs = (dataSvcs.data || []).map(s => ({ ...s, _type: 'service' }))

    const merged = [...pubs, ...svcs].sort((a, b) => {
      if (tri.value === 'alpha') {
        const na = a.titre || a.service || ''
        const nb = b.titre || b.service || ''
        return na.localeCompare(nb)
      }
      return new Date(b.created_at) - new Date(a.created_at)
    })

    items.value = merged
  } finally { loading.value = false }
}

function effacerRecherche() { q.value = ''; charger() }
function reset() { q.value = ''; tri.value = 'recent'; charger() }

function imageUrl(path) {
  if (!path) return ''
  if (/^https?:\/\//.test(path)) return path
  const base = (import.meta.env.VITE_API_URL || 'http://localhost:8000/api').replace(/\/api\/?$/, '')
  return `${base}${path.startsWith('/') ? '' : '/'}${path}`
}

function initiales(user) {
  return ((user.prenom?.[0] ?? '') + (user.nom?.[0] ?? '')).toUpperCase() || '?'
}

function formatDate(d) {
  if (!d) return ''
  return new Date(d).toLocaleDateString('fr-FR', { day: 'numeric', month: 'short', year: 'numeric' })
}

onMounted(charger)
</script>

<style scoped>
.annuaire-public { min-height: 100vh; }

.page-header { background: linear-gradient(135deg, #f0fdf9 0%, #ecfdf5 40%, #eff6ff 100%); border-bottom: 1px solid #e2e8f0; padding: 56px 28px 44px; }
.header-inner { max-width: 680px; margin: 0 auto; text-align: center; }
.header-badge { display: inline-flex; align-items: center; gap: 7px; padding: 5px 14px; background: white; border: 1px solid #e2e8f0; border-radius: 999px; font-size: 12px; font-weight: 600; color: #1a6fc4; margin-bottom: 22px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
.page-titre { font-size: clamp(24px, 4vw, 36px); font-weight: 900; color: #0f172a; margin: 0 0 12px; line-height: 1.15; letter-spacing: -0.03em; }
.page-sous-titre { font-size: 15.5px; color: #64748b; margin: 0 0 28px; line-height: 1.5; }

.search-bar { display: flex; align-items: center; background: white; border: 1.5px solid #e2e8f0; border-radius: 14px; padding: 0 16px; box-shadow: 0 4px 16px rgba(0,0,0,0.06); transition: border-color 0.15s; }
.search-bar:focus-within { border-color: #1a6fc4; }
.search-ico { color: #94a3b8; flex-shrink: 0; }
.search-input { flex: 1; border: none; outline: none; padding: 15px 12px; font-size: 15px; background: transparent; color: #0f172a; }
.search-input::placeholder { color: #94a3b8; }
.search-clear { background: #f1f5f9; border: none; color: #64748b; cursor: pointer; font-size: 12px; width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }

.page-body { max-width: 1200px; margin: 0 auto; padding: 36px 28px 56px; display: grid; grid-template-columns: 220px 1fr; gap: 32px; align-items: start; }

.filtres { position: sticky; top: 80px; background: white; border: 1px solid #e8edf4; border-radius: 16px; padding: 20px; display: flex; flex-direction: column; gap: 22px; }
.filtre-groupe { display: flex; flex-direction: column; gap: 5px; }
.filtre-titre { font-size: 10.5px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.07em; color: #94a3b8; margin-bottom: 6px; }
.filtre-btn { text-align: left; padding: 7px 11px; border-radius: 9px; border: 1px solid transparent; background: none; font-size: 13px; color: #64748b; cursor: pointer; transition: all 0.12s; }
.filtre-btn:hover { background: #f8fafc; color: #334155; }
.filtre-btn.active { background: #eff6ff; color: #1a6fc4; font-weight: 600; border-color: #bfdbfe; }
.filtre-check { display: flex; align-items: center; gap: 9px; font-size: 13px; color: #64748b; cursor: pointer; }
.filtre-check input { accent-color: #1a6fc4; width: 14px; height: 14px; cursor: pointer; }
.filtre-select { padding: 9px 11px; border: 1.5px solid #e2e8f0; border-radius: 9px; font-size: 13px; color: #0f172a; background: white; outline: none; cursor: pointer; }
.filtre-select:focus { border-color: #1a6fc4; }

.results-top { margin-bottom: 18px; }
.results-count { font-size: 13px; color: #94a3b8; font-weight: 500; }

.cards-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(268px, 1fr)); gap: 20px; }

.item-card { background: white; border: 1px solid #e8edf4; border-radius: 18px; overflow: hidden; text-decoration: none; display: flex; flex-direction: column; transition: box-shadow 0.25s ease, transform 0.25s cubic-bezier(.22,.68,0,1.2), border-color 0.2s; }
.item-card:hover { box-shadow: 0 16px 48px rgba(15,23,42,0.13); transform: translateY(-6px); border-color: #bfdbfe; }

.card-cover { position: relative; height: 168px; overflow: hidden; background: linear-gradient(135deg, #f0fdf9, #eff6ff); margin: 0; display: flex; align-items: center; justify-content: center; }
.card-img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; display: block; transition: transform 0.35s ease; }
.item-card:hover .card-img { transform: scale(1.04); }
.card-fallback { font-size: 52px; font-weight: 900; color: #0f766e; opacity: 0.2; user-select: none; }

.type-badge { position: absolute; top: 10px; left: 10px; font-size: 10px; font-weight: 700; padding: 3px 9px; border-radius: 999px; }
.type-badge.pub { background: #dbeafe; color: #1d4ed8; }
.type-badge.svc { background: #d1fae5; color: #065f46; }
.dispo-badge { position: absolute; top: 10px; right: 10px; font-size: 10px; font-weight: 700; padding: 3px 9px; border-radius: 999px; }
.dispo   { background: #dcfce7; color: #166534; }
.indispo { background: #fee2e2; color: #991b1b; }

.card-body { padding: 18px; display: flex; flex-direction: column; flex: 1; gap: 7px; }
.card-cat { font-size: 10.5px; font-weight: 700; color: #1a6fc4; text-transform: uppercase; letter-spacing: 0.06em; }
.card-titre { font-size: 15.5px; font-weight: 700; color: #0f172a; margin: 0; line-height: 1.3; }
.card-desc { font-size: 13px; color: #64748b; line-height: 1.55; margin: 0; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }

.card-auteur { display: flex; align-items: center; gap: 9px; padding: 9px 0; border-top: 1px solid #f1f5f9; margin-top: 2px; }
.auteur-avatar { width: 30px; height: 30px; border-radius: 8px; background: linear-gradient(135deg, #1a6fc4, #0d9488); color: white; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; flex-shrink: 0; }
.auteur-nom { font-size: 12.5px; font-weight: 600; color: #0f172a; }
.auteur-date { font-size: 11px; color: #94a3b8; }

.card-ent { display: flex; align-items: center; gap: 9px; padding: 9px 0; border-top: 1px solid #f1f5f9; margin-top: 2px; }
.ent-avatar { width: 30px; height: 30px; border-radius: 8px; background: linear-gradient(135deg, #1a6fc4, #0d9488); color: white; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; flex-shrink: 0; }
.ent-nom { font-size: 12.5px; font-weight: 600; color: #0f172a; }
.ent-type { font-size: 11px; color: #94a3b8; text-transform: capitalize; }

.card-footer { display: flex; justify-content: flex-end; margin-top: auto; padding-top: 10px; }
.card-cta { font-size: 12.5px; font-weight: 700; color: #1a6fc4; }

.skeleton-card { height: 320px; background: linear-gradient(90deg, #f1f5f9 25%, #e8edf4 50%, #f1f5f9 75%); background-size: 200% 100%; border-radius: 18px; animation: shimmer 1.4s infinite; }
@keyframes shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }

.vide { text-align: center; padding: 72px 24px; color: #94a3b8; display: flex; flex-direction: column; align-items: center; gap: 14px; }
.vide-icon { width: 64px; height: 64px; border-radius: 50%; background: #f8fafc; border: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: center; }
.vide p { font-size: 14px; margin: 0; }
.btn-reset { padding: 9px 22px; border: 1.5px solid #e2e8f0; border-radius: 10px; background: white; font-size: 13px; cursor: pointer; color: #64748b; }

.pagination { display: flex; align-items: center; justify-content: center; gap: 12px; margin-top: 36px; }
.page-btn { padding: 9px 22px; border: 1.5px solid #e2e8f0; border-radius: 10px; background: white; font-size: 13px; cursor: pointer; font-weight: 500; color: #475569; transition: all 0.15s; }
.page-btn:hover:not(:disabled) { border-color: #1a6fc4; color: #1a6fc4; background: #eff6ff; }
.page-btn:disabled { opacity: 0.35; cursor: default; }
.page-info { font-size: 13px; color: #94a3b8; }

.cta-banner { background: linear-gradient(135deg, #0a2f5f 0%, #0e4f91 50%, #0d9488 100%); padding: 52px 28px; margin-top: 24px; }
.cta-banner-inner { max-width: 900px; margin: 0 auto; display: flex; align-items: center; justify-content: space-between; gap: 28px; flex-wrap: wrap; }
.cta-titre { font-size: 21px; font-weight: 800; color: white; margin: 0 0 8px; }
.cta-desc { font-size: 14px; color: rgba(255,255,255,0.75); margin: 0; max-width: 480px; line-height: 1.55; }
.cta-actions { display: flex; gap: 12px; flex-wrap: wrap; }
.btn-login-banner { padding: 12px 24px; border: 1.5px solid rgba(255,255,255,0.45); border-radius: 11px; color: white; text-decoration: none; font-size: 14px; font-weight: 600; }
.btn-login-banner:hover { background: rgba(255,255,255,0.12); }
.btn-register-banner { padding: 12px 24px; background: white; border-radius: 11px; color: #1a6fc4; text-decoration: none; font-size: 14px; font-weight: 700; box-shadow: 0 4px 14px rgba(0,0,0,0.12); }
.btn-register-banner:hover { transform: translateY(-1px); }

@media (max-width: 860px) {
  .page-body { grid-template-columns: 1fr; }
  .filtres { position: static; flex-direction: row; flex-wrap: wrap; gap: 16px; padding: 16px; }
  .filtre-groupe { flex-direction: row; align-items: center; flex-wrap: wrap; gap: 6px; }
  .filtre-titre { margin-bottom: 0; }
}
@media (max-width: 560px) {
  .cards-grid { grid-template-columns: 1fr; }
  .cta-banner-inner { flex-direction: column; align-items: flex-start; }
  .page-header { padding: 40px 20px 0; }
  .page-body { padding: 28px 20px 48px; }
}
</style>
