<template>
  <div class="home">

    <!-- ══════════════════════════════════════════ -->
    <!-- HERO                                        -->
    <!-- ══════════════════════════════════════════ -->
    <section class="hero">
      <div class="hero-bg"></div>
      <div class="hero-grid">

        <!-- Gauche -->
        <div class="hero-left">
          <div class="hero-eyebrow anim-fade-in">
            <span class="eyebrow-dot"></span>
            Plateforme professionnelle de santé — Burkina Faso
          </div>

          <h1 class="hero-title anim-fade-up delay-1">
            Connecter les acteurs<br />de la santé et faciliter<br />
            l'accès aux services<br /><em class="text-gradient-hero">médicaux de confiance</em>
          </h1>

          <p class="hero-desc anim-fade-up delay-2">
            Trouvez rapidement des établissements, professionnels et services de santé certifiés.
            Une plateforme pensée pour les acteurs du secteur médical africain.
          </p>

          <!-- Barre de recherche 3 onglets -->
          <div class="search-box">
            <div class="search-tabs">
              <button
                v-for="tab in searchTabs" :key="tab.key"
                class="search-tab"
                :class="{ active: activeTab === tab.key }"
                @click="activeTab = tab.key"
              >{{ tab.label }}</button>
            </div>
            <div class="search-field-wrap">
              <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="search-ico"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
              <input
                v-model="searchQ"
                :placeholder="searchTabs.find(t=>t.key===activeTab)?.placeholder"
                class="search-input"
                @keyup.enter="goSearch"
              />
            </div>
            <button class="search-btn" @click="goSearch">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
              Rechercher
            </button>
          </div>

          <!-- Trust signals -->
          <div class="hero-trust">
            <div class="trust-item" v-for="t in trust" :key="t">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#34d399" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
              {{ t }}
            </div>
          </div>
        </div>

        <!-- Droite — Dernières publications -->
        <div class="hero-right">
          <div class="hero-pubs-card">
            <div class="hpc-header">
              <span class="hpc-label">Publications récentes</span>
              <router-link to="/annuaire-public" class="hpc-voir-tout">Voir tout →</router-link>
            </div>

            <div v-if="loadingHeroPubs" class="hpc-loading">
              <div class="hpc-skel" v-for="n in 3" :key="n"></div>
            </div>

            <div v-else class="hpc-list">
              <router-link
                v-for="pub in heroPubs"
                :key="pub.id"
                :to="`/publication/${pub.id}`"
                class="hpc-item"
              >
                <div class="hpc-img-wrap">
                  <img v-if="pub.image_url" :src="pub.image_url" :alt="pub.titre" class="hpc-img" />
                  <div v-else class="hpc-img-fallback">{{ pub.titre.charAt(0) }}</div>
                </div>
                <div class="hpc-content">
                  <div class="hpc-titre">{{ pub.titre }}</div>
                  <div class="hpc-meta">
                    <span>{{ pub.user?.prenom }} {{ pub.user?.nom }}</span>
                    · <span>{{ formatHeroDate(pub.created_at) }}</span>
                  </div>
                </div>
              </router-link>

              <div v-if="heroPubs.length === 0" class="hpc-vide">Aucune publication pour l'instant.</div>
            </div>
          </div>
        </div>

      </div>
    </section>

    <!-- ══════════════════════════════════════════ -->
    <!-- PUBLICATIONS POPULAIRES                     -->
    <!-- ══════════════════════════════════════════ -->
    <section class="section services-section reveal">
      <div class="container">
        <div class="section-head spread">
          <div>
            <div class="section-label">Publications populaires</div>
            <h2 class="section-title">Les dernières publications du réseau</h2>
            <p class="section-sub">Actualités et informations partagées par les professionnels de santé.</p>
          </div>
          <router-link to="/annuaire-public" class="btn-outline-sm">Voir toutes les publications</router-link>
        </div>

        <div v-if="loadingServices" class="services-grid">
          <div class="skel-card" v-for="n in 4" :key="n"></div>
        </div>

        <div v-else class="services-grid">
          <router-link
            v-for="(pub, i) in displayServices"
            :key="pub.id"
            :to="`/publication/${pub.id}`"
            class="svc-card"
          >
            <figure class="svc-cover" :class="`cover-${(i%4)+1}`">
              <img v-if="pub.image_url" :src="pub.image_url" :alt="pub.titre" class="svc-img" />
              <div v-else class="svc-fallback">{{ pub.titre?.charAt(0) || '📄' }}</div>
            </figure>
            <div class="svc-body">
              <div class="svc-name">{{ pub.titre }}</div>
              <div class="svc-desc">{{ pub.extrait }}</div>
              <div class="svc-etab" v-if="pub.user">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
                {{ pub.user.prenom }} {{ pub.user.nom }}
              </div>
              <div class="svc-footer">
                <div class="svc-date">{{ formatDate(pub.created_at) }}</div>
                <span class="svc-btn">Lire →</span>
              </div>
            </div>
          </router-link>
        </div>
      </div>
    </section>

    <!-- ══════════════════════════════════════════ -->
    <!-- ACTUALITES                                  -->
    <!-- ══════════════════════════════════════════ -->
    <section class="section news-section reveal" id="actualites">
      <div class="container">
        <div class="section-head spread">
          <div>
            <div class="section-label">Actualites Sante</div>
            <h2 class="section-title">Informations du secteur medical</h2>
            <p class="section-sub">Restez informe des dernieres avancees et initiatives en sante.</p>
          </div>
        </div>

        <div v-if="loadingPubs" class="news-grid">
          <div class="skel-news" v-for="n in 3" :key="n"></div>
        </div>

        <div v-else-if="displayPubs.length === 0" class="news-vide">
          Aucune actualité disponible pour le moment.
        </div>

        <div v-else class="news-grid">
          <div
            v-for="(art, i) in displayPubs"
            :key="art.id"
            class="news-card"
            :class="{ featured: i === 0 }"
          >
            <figure class="news-img" :class="!art.image_url ? `ni-${(i%3)+1}` : 'ni-photo'">
              <img v-if="art.image_url" :src="art.image_url" :alt="art.titre" class="news-photo" />
              <div v-else class="news-emoji">{{ ['🏥','💉','🧬'][i%3] }}</div>
              <span class="news-tag" :class="art.categorie">{{ labelCatHome(art.categorie) }}</span>
            </figure>
            <div class="news-body">
              <div class="news-date">{{ formatDate(art.date_publication) }}</div>
              <h3 class="news-title">{{ art.titre }}</h3>
              <p class="news-summary">{{ art.extrait }}</p>
              <div class="news-footer">
                <div class="news-author">
                  <div class="news-avatar">{{ art.source?.charAt(0) || 'S' }}</div>
                  {{ art.source }}
                </div>
                <a v-if="art.url_externe" :href="art.url_externe" target="_blank" class="news-read">Lire →</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ══════════════════════════════════════════ -->
    <!-- STATISTIQUES                                -->
    <!-- ══════════════════════════════════════════ -->
    <section class="stats-section">
      <div class="stats-inner">
        <div class="stats-head">
          <div class="section-label light">En chiffres</div>
          <h2 class="stats-title">La plateforme sante de reference au Burkina Faso</h2>
          <p class="stats-sub">Des resultats concrets au service des professionnels et des patients.</p>
        </div>
        <div class="stats-grid">
          <div class="stat-card" v-for="s in stats" :key="s.label">
            <span class="stat-emoji">{{ s.icon }}</span>
            <div class="stat-number">{{ formatStat(s.value) }}</div>
            <div class="stat-label-text">{{ s.label }}</div>
          </div>
        </div>
      </div>
    </section>

    <!-- ══════════════════════════════════════════ -->
    <!-- CTA FINAL                                   -->
    <!-- ══════════════════════════════════════════ -->
    <section class="cta-section">
      <div class="container">
        <div class="cta-inner">
          <div class="cta-icon">➕</div>
          <h2 class="cta-title">
            Rejoignez la plateforme sante<br />
            <em>de reference au Burkina Faso</em>
          </h2>
          <p class="cta-desc">
            Inscrivez votre etablissement, publiez vos services, atteignez des milliers de patients
            et de professionnels de sante. Gratuit pour commencer.
          </p>
          <div class="cta-btns">
            <router-link to="/register" class="cta-btn-primary">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="16" y1="11" x2="22" y2="11"/></svg>
              Creer un compte gratuit
            </router-link>
            <router-link to="/annuaire-public" class="cta-btn-outline">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
              Decouvrir les services
            </router-link>
          </div>
          <p class="cta-note">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            Gratuit · Sans carte bancaire · Acces immediat
          </p>
        </div>
      </div>
    </section>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { API_BASE } from '../../api/client.js'
import { useReveal } from '../../composables/useReveal.js'

useReveal()

const router = useRouter()

/* ── Recherche ────────────────────────────── */
const searchTabs = [
  { key: 'service',       label: 'Service',       placeholder: 'Ex: Cardiologie, Radiologie, Soins a domicile...' },
  { key: 'etablissement', label: 'Etablissement', placeholder: 'Ex: CHU Yalgado, Clinique Gabriel, Labo BioMedical...' },
  { key: 'professionnel', label: 'Professionnel', placeholder: 'Ex: Dr. Traore, Sage-femme, Infirmier...' },
]
const activeTab = ref('service')
const searchQ   = ref('')

function goSearch() {
  router.push({ path: '/annuaire-public', query: searchQ.value ? { q: searchQ.value } : {} })
}

const trust = ['Services verifies', 'Acces gratuit', 'Donnees securisees']

/* ── Hero publications ────────────────────── */
const heroPubs        = ref([])
const loadingHeroPubs = ref(true)

async function chargerHeroPubs() {
  try {
    const res  = await fetch(`${API_BASE}/site/publications?per_page=3`)
    const data = await res.json()
    heroPubs.value = data.data || []
  } catch { heroPubs.value = [] }
  finally { loadingHeroPubs.value = false }
}

function formatHeroDate(d) {
  if (!d) return ''
  return new Date(d).toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' })
}

function imageUrl(path) {
  if (!path) return ''
  if (/^https?:\/\//.test(path)) return path
  const base = (import.meta.env.VITE_API_URL || 'http://localhost:8000/api').replace(/\/api\/?$/, '')
  return `${base}${path.startsWith('/') ? '' : '/'}${path}`
}

/* ── Publications populaires ─────────────── */
const services        = ref([])
const loadingServices = ref(true)

const displayServices = computed(() => services.value)

async function chargerServices() {
  try {
    const res  = await fetch(`${API_BASE}/site/publications?per_page=4`)
    const data = await res.json()
    services.value = data.data || []
  } catch { services.value = [] }
  finally { loadingServices.value = false }
}

/* ── Actualités santé (depuis API) ────────── */
const loadingPubs = ref(true)
const actualitesHome = ref([])
const displayPubs = computed(() => actualitesHome.value)

async function chargerActualites() {
  try {
    const res  = await fetch(`${API_BASE}/site/actualites?per_page=3`)
    const data = await res.json()
    actualitesHome.value = data.data || []
  } catch { actualitesHome.value = [] }
  finally { loadingPubs.value = false }
}

function labelCatHome(c) {
  return { burkina: 'Burkina', afrique: 'Afrique', monde: 'Monde' }[c] ?? 'Santé'
}

function formatDate(date) {
  if (!date) return ''
  return new Date(date).toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' })
}

/* ── Stats ────────────────────────────────── */
const stats = ref([])

async function chargerStats() {
  try {
    const res  = await fetch(`${API_BASE}/site/stats`)
    stats.value = await res.json()
  } catch { stats.value = [] }
}

function formatStat(n) {
  if (n >= 1000) return (n / 1000).toFixed(n % 1000 === 0 ? 0 : 1) + ' k'
  return n.toString()
}


onMounted(() => { chargerServices(); chargerHeroPubs(); chargerStats(); chargerActualites() })
</script>

<style scoped>
/* ══════════════════════════════════════════════════ */
/* BASE                                               */
/* ══════════════════════════════════════════════════ */
.home { background: #f5f8fd; }
.container { max-width: 1200px; margin: 0 auto; padding: 0 28px; }
.section { padding: 88px 0; }

.section-head { margin-bottom: 44px; }
.section-head.spread { display: flex; align-items: flex-start; justify-content: space-between; gap: 20px; }

.section-label {
  display: inline-flex; align-items: center; gap: 8px;
  font-size: 12.5px; font-weight: 700; text-transform: uppercase;
  letter-spacing: 0.1em; color: #1a6fc4; margin-bottom: 12px;
}
.section-label::before { content: ''; width: 18px; height: 2px; background: #1a6fc4; border-radius: 2px; }
.section-label.light { color: rgba(255,255,255,0.7); justify-content: center; }
.section-label.light::before { background: rgba(255,255,255,0.45); }
.section-label.centered::before { display: none; }

.section-title { font-size: clamp(22px, 3vw, 30px); font-weight: 800; color: #0f172a; letter-spacing: -0.025em; line-height: 1.2; }
.section-sub   { font-size: 15.5px; color: #64748b; margin-top: 8px; line-height: 1.6; }

.btn-outline-sm {
  padding: 10px 20px; border-radius: 10px; border: 1.5px solid #e2e8f0;
  background: white; color: #475569; font-size: 13.5px; font-weight: 600;
  text-decoration: none; white-space: nowrap; box-shadow: 0 1px 4px rgba(15,23,42,0.06);
  transition: all 0.15s; flex-shrink: 0; display: inline-block;
}
.btn-outline-sm:hover { border-color: #1a6fc4; color: #1a6fc4; }

/* ══════════════════════════════════════════════════ */
/* HERO                                               */
/* ══════════════════════════════════════════════════ */
.hero { position: relative; overflow: hidden; padding: 80px 0 72px; }

.hero-bg {
  position: absolute; inset: 0;
  background: linear-gradient(135deg, #0a2f5f 0%, #0e4f91 35%, #1a6fc4 65%, #0d9488 100%);
}
.hero-bg::before {
  content: ''; position: absolute; inset: 0;
  background-image:
    radial-gradient(circle at 20% 80%, rgba(255,255,255,0.04) 0%, transparent 40%),
    radial-gradient(circle at 80% 20%, rgba(13,148,136,0.15) 0%, transparent 45%);
}
.hero-bg::after {
  content: ''; position: absolute; inset: 0;
  background-image:
    linear-gradient(rgba(255,255,255,0.025) 1px, transparent 1px),
    linear-gradient(90deg, rgba(255,255,255,0.025) 1px, transparent 1px);
  background-size: 60px 60px;
}

.hero-grid {
  position: relative; z-index: 1;
  max-width: 1200px; margin: 0 auto; padding: 0 28px;
  display: grid; grid-template-columns: 1fr 440px; gap: 64px; align-items: center;
}

/* Eyebrow */
.hero-eyebrow {
  display: inline-flex; align-items: center; gap: 8px;
  padding: 6px 14px; border-radius: 999px;
  background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.2);
  font-size: 12.5px; font-weight: 600; color: rgba(255,255,255,0.9);
  margin-bottom: 24px; backdrop-filter: blur(8px);
}

.eyebrow-dot {
  width: 7px; height: 7px; border-radius: 50%;
  background: #34d399; box-shadow: 0 0 0 3px rgba(52,211,153,0.3);
  animation: pulse 2s infinite; flex-shrink: 0;
}

@keyframes pulse {
  0%,100% { box-shadow: 0 0 0 3px rgba(52,211,153,0.3); }
  50%      { box-shadow: 0 0 0 6px rgba(52,211,153,0.1); }
}

.hero-title {
  font-size: clamp(26px, 3.4vw, 48px); font-weight: 900;
  color: white; line-height: 1.1; letter-spacing: -0.035em; margin-bottom: 22px;
}
.text-gradient-hero {
  font-style: normal;
  background: linear-gradient(90deg, #67e8f9 0%, #34d399 60%, #a7f3d0 100%);
  -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
}

.hero-desc { font-size: 16px; color: rgba(255,255,255,0.75); line-height: 1.65; margin-bottom: 32px; max-width: 480px; }

/* Search */
.search-box {
  background: white; border-radius: 16px; padding: 6px;
  display: flex; align-items: center; gap: 6px;
  box-shadow: 0 16px 48px rgba(0,0,0,0.25); margin-bottom: 28px;
}

.search-tabs {
  display: flex; gap: 2px; background: #f1f5f9; border-radius: 10px; padding: 3px; flex-shrink: 0;
}

.search-tab {
  padding: 7px 12px; border-radius: 8px; border: none;
  font-size: 12.5px; font-weight: 500; color: #64748b;
  background: transparent; cursor: pointer; transition: all 0.14s; white-space: nowrap;
}
.search-tab.active { background: white; color: #1a6fc4; font-weight: 600; box-shadow: 0 1px 4px rgba(15,23,42,0.08); }

.search-field-wrap { flex: 1; display: flex; align-items: center; gap: 9px; padding: 0 10px; }
.search-ico { color: #94a3b8; flex-shrink: 0; }

.search-input { flex: 1; border: none; outline: none; font-size: 14px; color: #0f172a; background: transparent; }
.search-input::placeholder { color: #94a3b8; }

.search-btn {
  padding: 12px 22px; border-radius: 11px; border: none;
  background: linear-gradient(135deg, #1a6fc4, #3b8fd8); color: white;
  font-size: 14px; font-weight: 700; display: flex; align-items: center; gap: 7px; flex-shrink: 0;
  box-shadow: 0 4px 14px rgba(26,111,196,0.4); cursor: pointer; transition: all 0.18s;
}
.search-btn:hover { transform: scale(1.02); box-shadow: 0 6px 20px rgba(26,111,196,0.5); }

.hero-trust { display: flex; align-items: center; gap: 20px; flex-wrap: wrap; }
.trust-item { display: flex; align-items: center; gap: 7px; font-size: 13px; color: rgba(255,255,255,0.68); }

/* Hero publications droite */
.hero-right { position: relative; }

.hero-pubs-card {
  background: white; border-radius: 22px; padding: 20px;
  box-shadow: 0 24px 64px rgba(0,0,0,0.18); position: relative; z-index: 2;
}

.hpc-header {
  display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;
}
.hpc-label { font-size: 13px; font-weight: 700; color: #0f172a; }
.hpc-voir-tout { font-size: 12px; font-weight: 600; color: #1a6fc4; text-decoration: none; }
.hpc-voir-tout:hover { text-decoration: underline; }

.hpc-list { display: flex; flex-direction: column; gap: 4px; }

.hpc-item {
  display: flex; align-items: center; gap: 12px;
  padding: 10px 11px; border-radius: 12px; text-decoration: none;
  transition: background 0.12s;
}
.hpc-item:hover { background: #f5f8fd; }

.hpc-img-wrap {
  width: 52px; height: 52px; border-radius: 10px; overflow: hidden;
  flex-shrink: 0; background: linear-gradient(135deg, #dbeafe, #d1fae5);
  display: flex; align-items: center; justify-content: center;
}
.hpc-img { width: 100%; height: 100%; object-fit: cover; }
.hpc-img-fallback { font-size: 20px; font-weight: 800; color: #1a6fc4; opacity: 0.5; }

.hpc-content { flex: 1; min-width: 0; }
.hpc-titre { font-size: 13px; font-weight: 600; color: #0f172a; line-height: 1.35; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.hpc-meta  { font-size: 11px; color: #94a3b8; margin-top: 3px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

.hpc-skel { height: 52px; border-radius: 12px; margin-bottom: 4px; background: linear-gradient(90deg,#f1f5f9 25%,#e8edf4 50%,#f1f5f9 75%); background-size: 200% 100%; animation: shimmer 1.4s infinite; }
.hpc-vide  { font-size: 13px; color: #94a3b8; text-align: center; padding: 20px 0; }

/* ══════════════════════════════════════════════════ */
/* SERVICES                                           */
/* ══════════════════════════════════════════════════ */
.services-section { background: #f5f8fd; }

.services-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; }

.svc-card {
  background: white; border: 1px solid #e8edf4; border-radius: 18px;
  overflow: hidden; text-decoration: none; display: flex; flex-direction: column;
  transition: box-shadow 0.25s ease, transform 0.25s cubic-bezier(.22,.68,0,1.2), border-color 0.2s;
}
.svc-card:hover { box-shadow: 0 16px 48px rgba(15,23,42,0.14); transform: translateY(-6px); border-color: #bfdbfe; }

.svc-cover {
  height: 160px; position: relative; overflow: hidden; margin: 0;
  display: flex; align-items: center; justify-content: center;
}
.cover-1 { background: linear-gradient(135deg, #dbeafe, #bfdbfe); }
.cover-2 { background: linear-gradient(135deg, #d1fae5, #a7f3d0); }
.cover-3 { background: linear-gradient(135deg, #fce7f3, #fbcfe8); }
.cover-4 { background: linear-gradient(135deg, #fff7ed, #fed7aa); }

.svc-img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; transition: transform 0.35s ease; }
.svc-card:hover .svc-img { transform: scale(1.05); }
.svc-fallback { font-size: 50px; opacity: 0.5; }

.svc-cat-pill {
  position: absolute; top: 11px; left: 11px;
  font-size: 10.5px; font-weight: 700; padding: 3px 10px;
  border-radius: 999px; background: white; color: #1a6fc4;
  box-shadow: 0 1px 4px rgba(15,23,42,0.1); z-index: 1;
}

.svc-body { padding: 16px; flex: 1; display: flex; flex-direction: column; gap: 7px; }
.svc-type-badge { position: absolute; top: 10px; left: 10px; font-size: 10px; font-weight: 700; padding: 3px 9px; border-radius: 999px; }
.svc-type-badge.publication { background: #dbeafe; color: #1d4ed8; }
.svc-type-badge.service     { background: #d1fae5; color: #065f46; }

.svc-name  { font-size: 14.5px; font-weight: 700; color: #0f172a; line-height: 1.3; }
.svc-desc  { font-size: 12.5px; color: #64748b; line-height: 1.5; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.svc-etab  { display: flex; align-items: center; gap: 6px; font-size: 12.5px; color: #64748b; }
.svc-etab svg { color: #94a3b8; flex-shrink: 0; }
.svc-date  { font-size: 11.5px; color: #94a3b8; }

.svc-footer { display: flex; align-items: center; justify-content: space-between; margin-top: auto; padding-top: 12px; border-top: 1px solid #f1f5f9; }
.svc-vues { display: flex; align-items: center; gap: 4px; font-size: 12px; color: #94a3b8; }
.svc-btn  { font-size: 12.5px; font-weight: 600; color: #1a6fc4; }

.skel-card { height: 300px; border-radius: 18px; background: linear-gradient(90deg,#f1f5f9 25%,#e8edf4 50%,#f1f5f9 75%); background-size: 200% 100%; animation: shimmer 1.4s infinite; }

@keyframes shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }

/* ══════════════════════════════════════════════════ */
/* ACTUALITES                                         */
/* ══════════════════════════════════════════════════ */
.news-section { background: white; border-top: 1px solid #e8edf4; border-bottom: 1px solid #e8edf4; }

.news-grid { display: grid; grid-template-columns: 1.4fr 1fr 1fr; gap: 20px; }

.news-card {
  background: white; border: 1px solid #e8edf4; border-radius: 18px;
  overflow: hidden; display: flex; flex-direction: column;
  text-decoration: none; transition: box-shadow 0.25s ease, transform 0.25s cubic-bezier(.22,.68,0,1.2), border-color 0.2s;
}
.news-card:hover { box-shadow: 0 16px 48px rgba(15,23,42,0.12); transform: translateY(-5px); border-color: #bfdbfe; }

.news-img {
  height: 190px; overflow: hidden; position: relative;
  display: flex; align-items: center; justify-content: center; margin: 0;
}
.news-card.featured .news-img { height: 228px; }
.ni-1 { background: linear-gradient(135deg, #dbeafe, #93c5fd); }
.ni-2 { background: linear-gradient(135deg, #d1fae5, #6ee7b7); }
.ni-3 { background: linear-gradient(135deg, #fce7f3, #f9a8d4); }
.ni-photo { background: #e2e8f0; overflow: hidden; }
.ni-photo .news-photo { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
.news-tag.burkina { background: #fff7ed; color: #c2410c; }
.news-tag.afrique { background: #f5f3ff; color: #6d28d9; }
.news-tag.monde   { background: #eff6ff; color: #1d4ed8; }
.news-vide { text-align: center; padding: 40px; color: #94a3b8; font-size: 14px; }

.news-photo { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; display: block; transition: transform 0.35s; }
.news-card:hover .news-photo { transform: scale(1.05); }
.news-emoji { font-size: 68px; opacity: 0.5; }

.news-tag { position: absolute; top: 13px; left: 13px; font-size: 10.5px; font-weight: 700; padding: 3px 10px; border-radius: 999px; background: white; color: #1a6fc4; box-shadow: 0 1px 4px rgba(15,23,42,0.1); }

.news-body { padding: 20px; flex: 1; display: flex; flex-direction: column; gap: 8px; }
.news-date    { font-size: 11.5px; color: #94a3b8; }
.news-title   { font-size: 14.5px; font-weight: 700; color: #0f172a; line-height: 1.35; }
.news-card.featured .news-title { font-size: 17px; }
.news-summary { font-size: 13px; color: #64748b; line-height: 1.6; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }

.news-footer { display: flex; align-items: center; justify-content: space-between; margin-top: auto; padding-top: 14px; border-top: 1px solid #f1f5f9; }
.news-author { display: flex; align-items: center; gap: 7px; font-size: 12px; color: #64748b; }
.news-avatar { width: 26px; height: 26px; border-radius: 50%; background: linear-gradient(135deg, #1a6fc4, #0d9488); display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: 700; color: white; flex-shrink: 0; }
.news-read   { font-size: 12.5px; font-weight: 600; color: #1a6fc4; }

.skel-news { height: 360px; border-radius: 18px; background: linear-gradient(90deg,#f1f5f9 25%,#e8edf4 50%,#f1f5f9 75%); background-size: 200% 100%; animation: shimmer 1.4s infinite; }

/* ══════════════════════════════════════════════════ */
/* STATISTIQUES                                       */
/* ══════════════════════════════════════════════════ */
.stats-section { padding: 88px 0; position: relative; overflow: hidden; background: linear-gradient(135deg, #0a2f5f 0%, #0e4f91 50%, #0d9488 100%); }
.stats-section::before { content: ''; position: absolute; inset: 0; background-image: linear-gradient(rgba(255,255,255,0.03) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.03) 1px, transparent 1px); background-size: 50px 50px; }

.stats-inner { position: relative; z-index: 1; max-width: 1200px; margin: 0 auto; padding: 0 28px; }

.stats-head { text-align: center; margin-bottom: 52px; }
.stats-title { font-size: clamp(22px, 3vw, 32px); font-weight: 900; color: white; letter-spacing: -0.025em; margin-top: 10px; }
.stats-sub   { font-size: 16px; color: rgba(255,255,255,0.68); margin-top: 10px; }

.stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 22px; }

.stat-card { background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.15); border-radius: 20px; padding: 30px 22px; text-align: center; backdrop-filter: blur(8px); transition: all 0.2s; }
.stat-card:hover { background: rgba(255,255,255,0.16); transform: translateY(-3px); }

.stat-emoji  { font-size: 38px; display: block; margin-bottom: 14px; }
.stat-number { font-size: clamp(26px, 3.5vw, 42px); font-weight: 900; color: white; letter-spacing: -0.04em; line-height: 1; margin-bottom: 8px; }
.stat-label-text { font-size: 13px; color: rgba(255,255,255,0.72); font-weight: 500; }



/* ══════════════════════════════════════════════════ */
/* CTA                                                */
/* ══════════════════════════════════════════════════ */
.cta-section { padding: 100px 0; background: linear-gradient(135deg, #eff6ff 0%, #f0fdf9 100%); border-top: 1px solid #e8edf4; }

.cta-inner { text-align: center; max-width: 660px; margin: 0 auto; }

.cta-icon { width: 70px; height: 70px; border-radius: 20px; background: linear-gradient(135deg, #1a6fc4, #0d9488); display: flex; align-items: center; justify-content: center; margin: 0 auto 28px; font-size: 30px; box-shadow: 0 12px 36px rgba(26,111,196,0.35); }

.cta-title { font-size: clamp(22px, 3.2vw, 36px); font-weight: 900; color: #0f172a; letter-spacing: -0.03em; line-height: 1.15; margin-bottom: 16px; }
.cta-title em { font-style: normal; color: #1a6fc4; }

.cta-desc { font-size: 16px; color: #64748b; line-height: 1.65; margin-bottom: 36px; }

.cta-btns { display: flex; justify-content: center; gap: 14px; flex-wrap: wrap; margin-bottom: 18px; }

.cta-btn-primary {
  display: inline-flex; align-items: center; gap: 8px;
  padding: 14px 26px; border-radius: 12px;
  background: linear-gradient(135deg, #1a6fc4, #3b8fd8);
  color: white; font-size: 15px; font-weight: 700;
  text-decoration: none; box-shadow: 0 6px 22px rgba(26,111,196,0.4); transition: all 0.2s;
}
.cta-btn-primary:hover { transform: translateY(-2px); box-shadow: 0 10px 30px rgba(26,111,196,0.5); }

.cta-btn-outline {
  display: inline-flex; align-items: center; gap: 8px;
  padding: 14px 26px; border-radius: 12px; border: 2px solid #e2e8f0;
  background: white; color: #334155; font-size: 15px; font-weight: 600;
  text-decoration: none; transition: all 0.18s;
}
.cta-btn-outline:hover { border-color: #1a6fc4; color: #1a6fc4; }

.cta-note { display: flex; align-items: center; justify-content: center; gap: 6px; font-size: 13px; color: #94a3b8; }

/* ══════════════════════════════════════════════════ */
/* RESPONSIVE                                         */
/* ══════════════════════════════════════════════════ */
@media (max-width: 1100px) {
  .services-grid { grid-template-columns: repeat(2, 1fr); }
  .stats-grid { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 900px) {
  .hero-grid { grid-template-columns: 1fr; gap: 40px; }
  .hero-right { display: none; }
  .news-grid { grid-template-columns: 1fr; }
  .news-card.featured .news-img { height: 190px; }
}

@media (max-width: 640px) {
  .hero { padding: 52px 0 48px; }
  .hero-grid { padding: 0 20px; }
  .search-box { flex-direction: column; align-items: stretch; padding: 10px; }
  .search-tabs { overflow-x: auto; }
  .services-grid { grid-template-columns: 1fr; }
  .stats-grid { grid-template-columns: 1fr 1fr; }

  .section { padding: 60px 0; }
  .container { padding: 0 20px; }
  .stats-inner { padding: 0 20px; }
}
</style>
