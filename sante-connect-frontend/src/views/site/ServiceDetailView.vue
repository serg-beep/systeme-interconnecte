<template>
  <div>
    <div class="service-detail" v-if="service">

      <!-- ══ Hero ══════════════════════════════ -->
      <div class="detail-hero">
        <!-- Image réelle avec <img> -->
        <img
          v-if="service.cover_image"
          :src="imageUrl(service.cover_image)"
          alt=""
          class="hero-img"
        />
        <!-- Fond de secours sans image -->
        <div v-else class="hero-fallback"></div>
        <!-- Overlay toujours présent -->
        <div class="hero-overlay">
          <div class="hero-inner">

            <router-link to="/annuaire-public" class="back-btn">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5"/><polyline points="12 19 5 12 12 5"/></svg>
              Retour à l'annuaire
            </router-link>

            <!-- Badges -->
            <div class="hero-meta">
              <span class="badge-cat" v-if="service.categorie">{{ service.categorie }}</span>
              <span class="badge-dispo dispo" v-if="service.disponible">Disponible</span>
              <span class="badge-dispo indispo" v-else>Indisponible</span>
            </div>

            <h1 class="detail-titre">{{ service.service }}</h1>

            <!-- Entreprise dans le hero -->
            <div class="hero-ent">
              <div class="ent-avatar">{{ service.entreprise?.nom?.charAt(0) || '?' }}</div>
              <div>
                <div class="ent-nom">{{ service.entreprise?.nom }}</div>
                <div class="ent-type" v-if="service.entreprise?.type">{{ service.entreprise.type }}</div>
              </div>
              <LikeButton
                type="annuaire"
                :id="service.id"
                :liked="service.liked_by_me"
                :count="service.likes_count || 0"
              />
            </div>

          </div>
        </div>
      </div>

      <!-- ══ Corps ══════════════════════════════ -->
      <div class="detail-body">
        <div class="detail-grid">

          <!-- Colonne principale -->
          <div class="col-main">

            <section class="detail-card">
              <h2 class="card-titre">À propos de ce service</h2>
              <p class="detail-desc">{{ service.description || 'Aucune description disponible pour ce service.' }}</p>
            </section>

            <section class="detail-card" v-if="service.tags?.length">
              <h2 class="card-titre">Mots-clés</h2>
              <div class="tags-list">
                <span class="tag" v-for="tag in service.tags" :key="tag">{{ tag }}</span>
              </div>
            </section>

            <!-- CTA -->
            <section class="detail-card cta-card" v-if="!isAuthenticated">
              <div class="cta-box-inner">
                <div class="cta-lock-icon">
                  <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                </div>
                <div>
                  <h3 class="cta-heading">Accéder au service complet</h3>
                  <p class="cta-text">Connectez-vous pour contacter ce prestataire, envoyer une demande ou accéder au logiciel.</p>
                </div>
              </div>
              <div class="cta-actions">
                <router-link :to="`/login?redirect=/service/${service.id}`" class="btn-primary">Se connecter</router-link>
                <router-link to="/register" class="btn-outline">Créer un compte gratuit</router-link>
              </div>
            </section>

            <section class="detail-card" v-else-if="service.visit_url">
              <a :href="service.visit_url" target="_blank" rel="noopener" class="btn-visit">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                Visiter le logiciel
              </a>
            </section>

          <!-- Avis -->
          <section class="detail-card">
            <AvisAnnuaire
              :annuaire-id="service.id"
              :avis="service.avis || []"
              :moyenne-initiale="service.note_moyenne"
            />
          </section>

          <!-- Commentaires -->
          <section class="detail-card">
            <CommentairesService
              :service-id="service.id"
              :commentaires="service.commentaires || []"
            />
          </section>

          </div>

          <!-- Sidebar -->
          <aside class="col-side">

            <!-- Prestataire -->
            <div class="side-card">
              <div class="side-titre">Prestataire</div>
              <div class="ent-profile">
                <div class="ent-avatar-lg">{{ service.entreprise?.nom?.charAt(0) || '?' }}</div>
                <div class="ent-nom-lg">{{ service.entreprise?.nom }}</div>
                <div class="ent-type-lg" v-if="service.entreprise?.type">{{ service.entreprise.type }}</div>
                <div class="ent-follow-wrap" v-if="service.entreprise">
                  <FollowButton
                    type="entreprise"
                    :id="service.entreprise.id"
                    :suivi="service.entreprise.suivi_par_moi"
                  />
                </div>
              </div>
              <div class="ent-infos">
                <div class="ent-info-line" v-if="service.entreprise?.ville || service.ville">
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                  {{ service.entreprise?.ville || service.ville }}
                </div>
                <div class="ent-info-line" v-if="service.entreprise?.telephone">
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.37 2 2 0 0 1 3.59 1h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.96a16 16 0 0 0 6.29 6.29l1.42-1.42a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                  {{ service.entreprise.telephone }}
                </div>
                <div class="ent-info-line contact-service" v-if="service.telephone">
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.37 2 2 0 0 1 3.59 1h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.96a16 16 0 0 0 6.29 6.29l1.42-1.42a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                  <a :href="`tel:${service.telephone}`">{{ service.telephone }}</a>
                  <span class="contact-badge">Contact pour ce service</span>
                </div>
              </div>
              <p class="ent-desc" v-if="service.entreprise?.description">{{ service.entreprise.description }}</p>
            </div>

            <!-- Stats -->
            <div class="side-card stats-card">
              <div class="stat-item">
                <div class="stat-val">{{ service.vues ?? 0 }}</div>
                <div class="stat-label">Vues</div>
              </div>
              <div class="stat-sep"></div>
              <div class="stat-item">
                <div class="stat-val date-val">{{ formatDate(service.created_at) }}</div>
                <div class="stat-label">Publié le</div>
              </div>
            </div>

            <!-- Autres services -->
            <div class="side-card" v-if="autresServices.length">
              <div class="side-titre">Autres services</div>
              <div class="autres-list">
                <router-link
                  v-for="s in autresServices"
                  :key="s.id"
                  :to="`/service/${s.id}`"
                  class="autre-item"
                >
                  <figure class="autre-cover">
                    <img v-if="s.cover_image" :src="imageUrl(s.cover_image)" :alt="s.service" class="autre-img" />
                    <div v-else class="autre-icon">{{ s.service.charAt(0) }}</div>
                  </figure>
                  <div class="autre-info">
                    <div class="autre-nom">{{ s.service }}</div>
                    <div class="autre-cat">{{ s.categorie || 'Service' }}</div>
                  </div>
                </router-link>
              </div>
            </div>

          </aside>
        </div>
      </div>

    </div>

    <!-- Chargement -->
    <div v-else-if="loading" class="detail-loading">
      <div class="skeleton-hero"></div>
      <div class="skel-body">
        <div class="skeleton-block" v-for="n in 3" :key="n"></div>
      </div>
    </div>

    <!-- Erreur -->
    <div v-else class="detail-erreur">
      <div class="erreur-icon">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      </div>
      <h2>Service introuvable</h2>
      <p>Ce service n'existe pas ou a été supprimé.</p>
      <router-link to="/annuaire-public" class="btn-outline">← Retour à l'annuaire</router-link>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { API_BASE } from '../../api/client.js'
import { useAuthStore } from '../../stores/auth.js'
import CommentairesService from '../../components/site/CommentairesService.vue'
import AvisAnnuaire from '../../components/site/AvisAnnuaire.vue'
import LikeButton from '../../components/site/LikeButton.vue'
import FollowButton from '../../components/site/FollowButton.vue'

function imageUrl(path) {
  if (!path) return ''
  if (/^https?:\/\//.test(path)) return path
  const base = (import.meta.env.VITE_API_URL || 'http://localhost:8000/api').replace(/\/api\/?$/, '')
  return `${base}${path.startsWith('/') ? '' : '/'}${path}`
}

const route           = useRoute()
const auth            = useAuthStore()
const service         = ref(null)
const autresServices  = ref([])
const loading         = ref(true)
const isAuthenticated = ref(auth.isAuthenticated)

async function charger(id) {
  loading.value = true
  service.value = null
  try {
    const res = await fetch(`${API_BASE}/annuaire/${id}`)
    if (!res.ok) { loading.value = false; return }
    const data = await res.json()
    service.value        = data.service
    autresServices.value = data.autres_services || []
  } finally {
    loading.value = false
  }
}

function formatDate(date) {
  return new Date(date).toLocaleDateString('fr-FR', { day: 'numeric', month: 'short', year: 'numeric' })
}

onMounted(() => charger(route.params.id))
watch(() => route.params.id, id => charger(id))
</script>

<style scoped>
/* ══════════════════════════════════════════ */
/* HERO                                      */
/* ══════════════════════════════════════════ */
.detail-hero {
  position: relative;
  min-height: 300px;
  overflow: hidden;
}

.hero-img {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: center;
  display: block;
}

.hero-fallback {
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, #0a2f5f 0%, #1a6fc4 60%, #0d9488 100%);
}

.hero-overlay {
  position: relative;
  z-index: 1;
  background: linear-gradient(to bottom, rgba(15,23,42,0.38) 0%, rgba(15,23,42,0.78) 100%);
  padding: 40px 28px 48px;
  min-height: 300px;
  display: flex;
  align-items: flex-end;
}

.hero-inner {
  max-width: 960px;
  margin: 0 auto;
  width: 100%;
}

/* Breadcrumb */
.breadcrumb {
  display: flex;
  align-items: center;
  gap: 6px;
  margin-bottom: 18px;
  font-size: 13px;
  flex-wrap: wrap;
}

.bread-link { color: rgba(255,255,255,0.68); text-decoration: none; transition: color 0.12s; }
.bread-link:hover { color: white; }
.bread-sep { color: rgba(255,255,255,0.35); }
.bread-current { color: rgba(255,255,255,0.9); font-weight: 500; }

/* Badges */
.hero-meta { display: flex; gap: 8px; margin-bottom: 12px; flex-wrap: wrap; }

.badge-cat {
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.07em;
  color: white;
  background: rgba(255,255,255,0.16);
  backdrop-filter: blur(4px);
  padding: 4px 12px;
  border-radius: 999px;
  border: 1px solid rgba(255,255,255,0.2);
}

.badge-dispo { font-size: 11px; font-weight: 700; padding: 4px 12px; border-radius: 999px; }
.dispo   { background: #dcfce7; color: #166534; }
.indispo { background: #fee2e2; color: #991b1b; }

.detail-titre {
  font-size: clamp(24px, 4vw, 38px);
  font-weight: 900;
  color: white;
  margin: 0 0 18px;
  line-height: 1.15;
  letter-spacing: -0.025em;
  text-shadow: 0 2px 8px rgba(0,0,0,0.2);
}

.hero-ent { display: flex; align-items: center; gap: 12px; }

.back-btn {
  display: inline-flex; align-items: center; gap: 7px; margin-bottom: 20px;
  padding: 8px 16px; background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.3);
  border-radius: 10px; color: white; font-size: 13px; font-weight: 600;
  text-decoration: none; backdrop-filter: blur(8px); transition: all 0.15s;
}
.back-btn:hover { background: rgba(255,255,255,0.25); }

.ent-avatar {
  width: 40px;
  height: 40px;
  border-radius: 11px;
  background: rgba(255,255,255,0.18);
  backdrop-filter: blur(4px);
  border: 1px solid rgba(255,255,255,0.25);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 16px;
  font-weight: 800;
  flex-shrink: 0;
}

.ent-nom  { font-size: 14px; font-weight: 700; color: white; }
.ent-type { font-size: 12px; color: rgba(255,255,255,0.65); text-transform: capitalize; margin-top: 1px; }

/* ══════════════════════════════════════════ */
/* CORPS                                     */
/* ══════════════════════════════════════════ */
.detail-body {
  max-width: 1100px;
  margin: 0 auto;
  padding: 44px 28px 72px;
}

.detail-grid {
  display: grid;
  grid-template-columns: 1fr 300px;
  gap: 28px;
  align-items: start;
}

/* ══════════════════════════════════════════ */
/* CARTE PRINCIPALE                          */
/* ══════════════════════════════════════════ */
.detail-card {
  background: white;
  border: 1px solid #e8edf4;
  border-radius: 18px;
  padding: 26px;
  margin-bottom: 20px;
}

.card-titre {
  font-size: 15px;
  font-weight: 700;
  color: #0f172a;
  margin: 0 0 14px;
  letter-spacing: -0.01em;
}

.detail-desc {
  font-size: 15px;
  color: #475569;
  line-height: 1.75;
  margin: 0;
  white-space: pre-line;
}

/* Tags */
.tags-list { display: flex; flex-wrap: wrap; gap: 8px; }

.tag {
  font-size: 12.5px;
  padding: 5px 14px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 999px;
  color: #475569;
  font-weight: 500;
}

/* CTA box */
.cta-card { background: linear-gradient(135deg, #eff6ff, #f0fdf9); border-color: #bfdbfe; }

.cta-box-inner {
  display: flex;
  align-items: flex-start;
  gap: 16px;
  margin-bottom: 20px;
}

.cta-lock-icon {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  background: white;
  border: 1px solid #e2e8f0;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #1a6fc4;
  flex-shrink: 0;
  box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}

.cta-heading { font-size: 16px; font-weight: 700; color: #0f172a; margin: 0 0 6px; }
.cta-text { font-size: 13.5px; color: #64748b; margin: 0; line-height: 1.55; }

.cta-actions { display: flex; gap: 10px; flex-wrap: wrap; }

.btn-primary {
  padding: 11px 22px;
  background: linear-gradient(135deg, #1a6fc4, #3b8fd8);
  color: white;
  text-decoration: none;
  border-radius: 10px;
  font-size: 14px;
  font-weight: 700;
  border: none;
  cursor: pointer;
  display: inline-block;
  box-shadow: 0 4px 14px rgba(15,118,110,0.3);
  transition: all 0.15s;
}

.btn-primary:hover { transform: translateY(-1px); box-shadow: 0 6px 18px rgba(26,111,196,0.4); }

.btn-outline {
  padding: 11px 22px;
  border: 1.5px solid #e2e8f0;
  color: #334155;
  text-decoration: none;
  border-radius: 10px;
  font-size: 14px;
  font-weight: 600;
  background: white;
  display: inline-block;
  transition: all 0.15s;
}

.btn-outline:hover { border-color: #0f766e; color: #0f766e; }

.btn-visit {
  display: inline-flex;
  align-items: center;
  gap: 9px;
  padding: 13px 26px;
  background: linear-gradient(135deg, #1a6fc4, #0d9488);
  color: white;
  text-decoration: none;
  border-radius: 12px;
  font-size: 15px;
  font-weight: 700;
  box-shadow: 0 6px 20px rgba(15,118,110,0.3);
  transition: all 0.18s;
}

.btn-visit:hover { transform: translateY(-2px); box-shadow: 0 10px 28px rgba(26,111,196,0.4); }

/* ══════════════════════════════════════════ */
/* SIDEBAR                                   */
/* ══════════════════════════════════════════ */
.col-side { position: sticky; top: 80px; display: flex; flex-direction: column; gap: 16px; }

.side-card {
  background: white;
  border: 1px solid #e8edf4;
  border-radius: 18px;
  padding: 20px;
}

.side-titre {
  font-size: 10.5px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #94a3b8;
  margin-bottom: 16px;
}

/* Profil entreprise */
.ent-profile {
  text-align: center;
  padding-bottom: 16px;
  border-bottom: 1px solid #f1f5f9;
  margin-bottom: 16px;
}

.ent-follow-wrap { margin-top: 12px; }

.ent-avatar-lg {
  width: 56px;
  height: 56px;
  border-radius: 16px;
  background: linear-gradient(135deg, #1a6fc4, #0d9488);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 22px;
  font-weight: 900;
  margin: 0 auto 10px;
}

.ent-nom-lg { font-size: 15px; font-weight: 700; color: #0f172a; }
.ent-type-lg { font-size: 12px; color: #94a3b8; text-transform: capitalize; margin-top: 2px; }

.ent-infos { display: flex; flex-direction: column; gap: 9px; margin-bottom: 12px; }

.ent-info-line {
  display: flex;
  align-items: center;
  gap: 9px;
  font-size: 13px;
  color: #475569;
}

.ent-info-line svg { color: #94a3b8; flex-shrink: 0; }
.ent-info-line.contact-service a { color: #1a6fc4; font-weight: 600; text-decoration: none; }
.ent-info-line.contact-service a:hover { text-decoration: underline; }
.contact-badge { font-size: 10px; font-weight: 600; color: #0d9488; background: #d1fae5; padding: 2px 7px; border-radius: 8px; margin-left: 2px; }

.ent-desc {
  font-size: 12.5px;
  color: #94a3b8;
  line-height: 1.55;
  margin: 0;
  padding-top: 12px;
  border-top: 1px solid #f1f5f9;
}

/* Stats */
.stats-card {
  display: flex;
  align-items: center;
  justify-content: space-around;
  padding: 18px;
}

.stat-item { text-align: center; }
.stat-val { font-size: 20px; font-weight: 800; color: #0f172a; letter-spacing: -0.02em; }
.date-val { font-size: 14px; }
.stat-label { font-size: 11px; color: #94a3b8; margin-top: 2px; }
.stat-sep { width: 1px; height: 44px; background: #f1f5f9; }

/* Autres services */
.autres-list { display: flex; flex-direction: column; gap: 8px; }

.autre-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 9px;
  border-radius: 11px;
  text-decoration: none;
  transition: background 0.12s;
}

.autre-item:hover { background: #f8fafc; }

.autre-cover {
  width: 38px;
  height: 38px;
  border-radius: 9px;
  overflow: hidden;
  flex-shrink: 0;
  margin: 0;
  background: #f0fdf9;
  display: flex;
  align-items: center;
  justify-content: center;
}

.autre-img { width: 100%; height: 100%; object-fit: cover; display: block; }

.autre-icon {
  font-size: 15px;
  font-weight: 800;
  color: #0f766e;
  opacity: 0.5;
}

.autre-info { min-width: 0; }
.autre-nom { font-size: 13px; font-weight: 600; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.autre-cat { font-size: 11px; color: #94a3b8; margin-top: 1px; }

/* ══════════════════════════════════════════ */
/* LOADING / ERREUR                          */
/* ══════════════════════════════════════════ */
.detail-loading { }
.skeleton-hero { height: 300px; background: #e2e8f0; animation: shimmer 1.4s infinite; background: linear-gradient(90deg,#f1f5f9 25%,#e2e8f0 50%,#f1f5f9 75%); background-size: 200% 100%; }
.skel-body { max-width: 1100px; margin: 0 auto; padding: 36px 28px; display: flex; flex-direction: column; gap: 16px; }
.skeleton-block { height: 130px; background: linear-gradient(90deg,#f1f5f9 25%,#e8edf4 50%,#f1f5f9 75%); background-size: 200% 100%; border-radius: 18px; animation: shimmer 1.4s infinite; }

@keyframes shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }

.detail-erreur {
  text-align: center;
  padding: 100px 24px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 14px;
}

.erreur-icon {
  width: 64px;
  height: 64px;
  border-radius: 50%;
  background: #fef2f2;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #dc2626;
}

.detail-erreur h2 { font-size: 20px; font-weight: 700; color: #0f172a; margin: 0; }
.detail-erreur p { font-size: 14px; color: #64748b; margin: 0; }

/* ══════════════════════════════════════════ */
/* RESPONSIVE                                */
/* ══════════════════════════════════════════ */
@media (max-width: 860px) {
  .detail-grid { grid-template-columns: 1fr; }
  .col-side { position: static; }
}

@media (max-width: 600px) {
  .detail-body { padding: 28px 20px 56px; }
  .detail-hero { min-height: 260px; }
  .hero-overlay { min-height: 260px; padding: 28px 20px 36px; }
}
</style>
