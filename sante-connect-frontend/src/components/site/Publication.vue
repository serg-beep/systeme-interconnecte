<template>
  <div class="pub-page">

    <!-- Skeleton -->
    <div v-if="loading" class="skeleton-page">
      <div class="skel skel-hero"></div>
      <div class="skel-body">
        <div class="skel skel-title"></div>
        <div class="skel skel-line"></div>
        <div class="skel skel-line short"></div>
        <div class="skel skel-line"></div>
        <div class="skel skel-line short"></div>
      </div>
    </div>

    <!-- Erreur -->
    <div v-else-if="erreur" class="pub-erreur">
      <div class="erreur-icon">😕</div>
      <h2>Publication introuvable</h2>
      <p>Cette publication n'existe pas ou n'est plus disponible.</p>
      <router-link to="/annuaire-public" class="btn-retour">← Retour à l'annuaire</router-link>
    </div>

    <!-- Contenu -->
    <template v-else-if="publication">

      <!-- Hero image -->
      <div class="pub-hero" :style="publication.image_url ? `--bg:url(${publication.image_url})` : ''">
        <div class="hero-overlay"></div>
        <img v-if="publication.image_url" :src="publication.image_url" :alt="publication.titre" class="hero-img" />
        <div v-else class="hero-fallback">
          <span>{{ publication.titre?.charAt(0) || '📄' }}</span>
        </div>
        <div class="hero-content">
          <router-link to="/annuaire-public" class="back-btn">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5"/><polyline points="12 19 5 12 12 5"/></svg>
            Retour à l'annuaire
          </router-link>
        </div>
      </div>

      <!-- Corps -->
      <div class="pub-body">
        <div class="pub-inner">

          <!-- En-tête article -->
          <header class="pub-header">
            <h1 class="pub-titre">{{ publication.titre }}</h1>

            <div class="pub-meta">
              <div class="meta-auteur" v-if="publication.user">
                <div class="auteur-avatar">{{ initiales(publication.user) }}</div>
                <div class="auteur-info">
                  <div class="auteur-nom">{{ publication.user.prenom }} {{ publication.user.nom }}</div>
                  <div class="auteur-poste">{{ publication.user.poste || publication.user.entreprise?.nom }}</div>
                </div>
              </div>
              <div class="meta-sep" v-if="publication.user"></div>
              <div class="meta-date">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                {{ formatDate(publication.created_at) }}
              </div>
              <div class="meta-sep"></div>
              <div class="meta-temps">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                {{ tempsLecture(publication.contenu) }} min de lecture
              </div>
            </div>
          </header>

          <!-- Résumé mis en avant -->
          <div class="pub-resume" v-if="publication.extrait">
            {{ publication.extrait }}
          </div>

          <!-- Contenu complet -->
          <div class="pub-contenu" v-if="publication.contenu">
            <p v-for="(para, i) in paragraphes" :key="i" class="para">{{ para }}</p>
          </div>

          <div v-else class="pub-contenu-vide">
            <p>Le contenu détaillé de cette publication n'est pas disponible.</p>
          </div>

          <!-- Pied article -->
          <footer class="pub-footer">
            <div class="footer-left">
              <div class="pub-entreprise" v-if="publication.user?.entreprise">
                <div class="ent-avatar">{{ publication.user.entreprise.nom?.charAt(0) }}</div>
                <div>
                  <div class="ent-nom">{{ publication.user.entreprise.nom }}</div>
                  <div class="ent-type">{{ publication.user.entreprise.type }}</div>
                </div>
              </div>
            </div>
            <div class="footer-right">
              <router-link to="/annuaire-public" class="btn-retour-footer">
                ← Retour à l'annuaire
              </router-link>
            </div>
          </footer>

        </div>

        <!-- Commentaires -->
        <div class="pub-inner">
          <Commentaires
            :publication-id="publication.id"
            :commentaires="publication.commentaires_approuves || []"
          />
        </div>

      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onActivated, watch } from 'vue'
import { useRoute } from 'vue-router'
import { API_BASE } from '../../api/client.js'
import Commentaires from './Commentaires.vue'

const route       = useRoute()
const publication = ref(null)
const loading     = ref(true)
const erreur      = ref(false)

const paragraphes = computed(() => {
  if (!publication.value?.contenu) return []
  return publication.value.contenu
    .split('\n')
    .map(p => p.trim())
    .filter(p => p.length > 0)
})

function initiales(user) {
  return ((user.prenom?.[0] ?? '') + (user.nom?.[0] ?? '')).toUpperCase() || '?'
}

function formatDate(d) {
  if (!d) return ''
  return new Date(d).toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' })
}

function tempsLecture(contenu) {
  if (!contenu) return 1
  const mots = contenu.trim().split(/\s+/).length
  return Math.max(1, Math.round(mots / 200))
}

async function charger() {
  loading.value = true
  erreur.value  = false
  try {
    const res  = await fetch(`${API_BASE}/site/publications/${route.params.id}`)
    if (!res.ok) throw new Error('Not found')
    publication.value = await res.json()
  } catch {
    erreur.value = true
  } finally {
    loading.value = false
  }
}

onMounted(charger)
onActivated(charger)
watch(() => route.params.id, charger)
</script>

<style scoped>
.pub-page { min-height: 100vh; background: #f5f8fd; }

/* ── SKELETON ─────────────────────────────────── */
.skeleton-page { animation: fadeIn 0.3s ease; }
@keyframes fadeIn { from { opacity: 0 } to { opacity: 1 } }
.skel { background: linear-gradient(90deg, #f1f5f9 25%, #e8edf4 50%, #f1f5f9 75%); background-size: 200% 100%; animation: shimmer 1.4s infinite; border-radius: 10px; }
@keyframes shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }
.skel-hero  { height: 380px; border-radius: 0; }
.skel-body  { max-width: 760px; margin: 0 auto; padding: 40px 28px; display: flex; flex-direction: column; gap: 14px; }
.skel-title { height: 36px; width: 80%; }
.skel-line  { height: 16px; }
.skel-line.short { width: 60%; }

/* ── ERREUR ───────────────────────────────────── */
.pub-erreur { max-width: 480px; margin: 80px auto; text-align: center; padding: 0 24px; }
.erreur-icon { font-size: 52px; margin-bottom: 16px; }
.pub-erreur h2 { font-size: 22px; font-weight: 800; color: #0f172a; margin: 0 0 10px; }
.pub-erreur p  { color: #64748b; margin: 0 0 24px; }

/* ── HERO ─────────────────────────────────────── */
.pub-hero {
  position: relative; height: 420px; overflow: hidden;
  background: linear-gradient(135deg, #0a2f5f, #0e4f91, #0d9488);
  display: flex; align-items: flex-end;
}
.hero-overlay {
  position: absolute; inset: 0; z-index: 1;
  background: linear-gradient(to bottom, rgba(10,23,42,0.2) 0%, rgba(10,23,42,0.65) 100%);
}
.hero-img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; transition: transform 8s ease; }
.pub-hero:hover .hero-img { transform: scale(1.04); }
.hero-fallback {
  position: absolute; inset: 0; display: flex; align-items: center; justify-content: center;
  font-size: 96px; opacity: 0.25;
}
.hero-content { position: relative; z-index: 2; width: 100%; max-width: 1100px; margin: 0 auto; padding: 0 28px 28px; }
.back-btn {
  display: inline-flex; align-items: center; gap: 7px;
  padding: 8px 16px; background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.3);
  border-radius: 10px; color: white; font-size: 13px; font-weight: 600;
  text-decoration: none; backdrop-filter: blur(8px); transition: all 0.15s;
}
.back-btn:hover { background: rgba(255,255,255,0.25); }

/* ── CORPS ────────────────────────────────────── */
.pub-body { max-width: 1100px; margin: 0 auto; padding: 0 28px 72px; }
.pub-inner { max-width: 760px; margin: 0 auto; }

/* ── EN-TÊTE ──────────────────────────────────── */
.pub-header { padding: 40px 0 28px; border-bottom: 1px solid #e8edf4; margin-bottom: 28px; }
.pub-titre { font-size: clamp(22px, 3.5vw, 36px); font-weight: 900; color: #0f172a; line-height: 1.2; letter-spacing: -0.025em; margin: 0 0 20px; }

.pub-meta { display: flex; align-items: center; gap: 16px; flex-wrap: wrap; }
.meta-auteur { display: flex; align-items: center; gap: 10px; }
.auteur-avatar {
  width: 38px; height: 38px; border-radius: 10px; flex-shrink: 0;
  background: linear-gradient(135deg, #1a6fc4, #0d9488);
  color: white; display: flex; align-items: center; justify-content: center;
  font-size: 13px; font-weight: 700;
}
.auteur-nom   { font-size: 13.5px; font-weight: 700; color: #0f172a; }
.auteur-poste { font-size: 12px; color: #94a3b8; }
.meta-sep     { width: 4px; height: 4px; border-radius: 50%; background: #cbd5e1; }
.meta-date, .meta-temps { display: flex; align-items: center; gap: 5px; font-size: 13px; color: #64748b; }

/* ── RÉSUMÉ ───────────────────────────────────── */
.pub-resume {
  font-size: 17px; color: #374151; line-height: 1.75; font-weight: 500;
  border-left: 4px solid #1a6fc4; padding: 16px 20px;
  background: #eff6ff; border-radius: 0 12px 12px 0;
  margin-bottom: 32px; font-style: italic;
}

/* ── CONTENU ──────────────────────────────────── */
.pub-contenu { margin-bottom: 40px; }
.para {
  font-size: 16px; color: #374151; line-height: 1.85;
  margin: 0 0 20px; text-align: justify;
}
.para:first-child::first-letter {
  font-size: 3em; font-weight: 900; color: #1a6fc4;
  float: left; line-height: 0.8; margin: 4px 8px 0 0;
}

.pub-contenu-vide { padding: 32px; text-align: center; color: #94a3b8; background: #f8fafc; border-radius: 14px; margin-bottom: 40px; }

/* ── PIED ARTICLE ─────────────────────────────── */
.pub-footer { display: flex; align-items: center; justify-content: space-between; padding: 24px 0; border-top: 1px solid #e8edf4; margin-bottom: 48px; flex-wrap: wrap; gap: 16px; }
.pub-entreprise { display: flex; align-items: center; gap: 12px; }
.ent-avatar { width: 44px; height: 44px; border-radius: 12px; background: linear-gradient(135deg, #1a6fc4, #0d9488); color: white; display: flex; align-items: center; justify-content: center; font-size: 16px; font-weight: 800; flex-shrink: 0; }
.ent-nom  { font-size: 14px; font-weight: 700; color: #0f172a; }
.ent-type { font-size: 12px; color: #94a3b8; text-transform: capitalize; }

.btn-retour       { display: inline-flex; align-items: center; gap: 7px; padding: 9px 20px; background: #1a6fc4; border-radius: 10px; color: white; font-size: 13.5px; font-weight: 600; text-decoration: none; transition: all 0.15s; }
.btn-retour:hover { background: #0e4f91; transform: translateY(-1px); }
.btn-retour-footer { display: inline-flex; align-items: center; gap: 7px; padding: 9px 18px; border: 1.5px solid #e2e8f0; border-radius: 10px; color: #475569; font-size: 13px; font-weight: 600; text-decoration: none; background: white; transition: all 0.15s; }
.btn-retour-footer:hover { border-color: #1a6fc4; color: #1a6fc4; }

/* ── RESPONSIVE ───────────────────────────────── */
@media (max-width: 640px) {
  .pub-hero { height: 260px; }
  .pub-body { padding: 0 20px 52px; }
  .pub-titre { font-size: 22px; }
  .pub-meta { gap: 10px; }
  .meta-sep { display: none; }
}
</style>
