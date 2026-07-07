<template>
  <div class="apropos-page">

    <!-- ═══ HÉRO ══════════════════════════════════════ -->
    <section class="hero">
      <div class="hero-bg"></div>
      <div class="hero-inner">
        <div class="hero-badge">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          À propos de nous
        </div>
        <h1 class="hero-titre">À propos de <span class="accent">SIS</span></h1>
        <p class="hero-texte">
          SIS est une plateforme numérique qui facilite la découverte de services, d'entreprises,
          d'opportunités et de publications au Burkina Faso. Notre objectif est de rapprocher les
          utilisateurs des informations et services dont ils ont besoin.
        </p>
        <div class="hero-stats">
          <template v-for="(s, i) in heroStats" :key="i">
            <div class="stat-item">
              <div class="stat-n">
                <span v-if="loadingStats" class="stat-skel"></span>
                <span v-else>{{ s.value }}</span>
              </div>
              <div class="stat-l">{{ s.label }}</div>
            </div>
            <div v-if="i < heroStats.length - 1" class="stat-sep"></div>
          </template>
        </div>
      </div>
    </section>

    <!-- ═══ MISSION & VISION ══════════════════════════ -->
    <section class="section mv-section reveal">
      <div class="container">
        <div class="mv-grid">
          <div class="mv-card mission">
            <div class="mv-icon">
              <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polygon points="10 8 16 12 10 16 10 8"/></svg>
            </div>
            <div class="mv-tag">Mission</div>
            <h2 class="mv-titre">Notre mission</h2>
            <p class="mv-texte">
              Offrir une plateforme simple, accessible et efficace permettant aux particuliers,
              professionnels et entreprises de promouvoir leurs activités et de se connecter
              à leur communauté.
            </p>
            <ul class="mv-liste">
              <li>Connecter les acteurs locaux</li>
              <li>Simplifier l'accès aux services</li>
              <li>Valoriser les entreprises burkinabè</li>
            </ul>
          </div>

          <div class="mv-card vision">
            <div class="mv-icon">
              <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            </div>
            <div class="mv-tag vision-tag">Vision</div>
            <h2 class="mv-titre">Notre vision</h2>
            <p class="mv-texte">
              Devenir une référence numérique en Afrique en facilitant l'accès aux services,
              à l'information et aux opportunités grâce à la technologie.
            </p>
            <ul class="mv-liste">
              <li>Référence numérique africaine</li>
              <li>Inclusion numérique pour tous</li>
              <li>Écosystème digital local fort</li>
            </ul>
          </div>
        </div>
      </div>
    </section>

    <!-- ═══ CE QUE PROPOSE SIS ════════════════════════ -->
    <section class="section offres-section reveal">
      <div class="container">
        <div class="section-head">
          <div class="section-label">Notre plateforme</div>
          <h2 class="section-titre">Ce que propose SIS</h2>
          <p class="section-sous">Une solution complète pour connecter les acteurs du numérique au Burkina Faso.</p>
        </div>

        <div class="offres-grid">
          <div class="offre-card anim-fade-up" v-for="(offre, i) in offres" :key="offre.titre" :style="`animation-delay:${i*0.08}s`">
            <div class="offre-icon" :style="`background:${offre.bg}`">
              <component :is="'span'" class="offre-emoji">{{ offre.emoji }}</component>
            </div>
            <h3 class="offre-titre">{{ offre.titre }}</h3>
            <p class="offre-desc">{{ offre.desc }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- ═══ NOS VALEURS ════════════════════════════════ -->
    <section class="section valeurs-section">
      <div class="container">
        <div class="section-head">
          <div class="section-label light">Nos valeurs</div>
          <h2 class="section-titre white">Ce en quoi nous croyons</h2>
          <p class="section-sous white-sub">Les principes qui guident chacune de nos décisions et actions.</p>
        </div>

        <div class="valeurs-grid">
          <div class="valeur-card" v-for="val in valeurs" :key="val.nom">
            <div class="valeur-num">{{ val.num }}</div>
            <div class="valeur-icon">{{ val.emoji }}</div>
            <h3 class="valeur-nom">{{ val.nom }}</h3>
            <p class="valeur-desc">{{ val.desc }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- ═══ POURQUOI CHOISIR SIS ══════════════════════ -->
    <section class="section pourquoi-section reveal">
      <div class="container">
        <div class="pourquoi-grid">
          <div class="pourquoi-left">
            <div class="section-label">Pourquoi nous ?</div>
            <h2 class="section-titre">Pourquoi choisir SIS ?</h2>
            <p class="pourquoi-intro">
              Nous avons conçu SIS pour répondre aux besoins réels des utilisateurs burkinabè
              et africains. Chaque fonctionnalité est pensée pour vous simplifier la vie.
            </p>
            <div class="pourquoi-cta">
              <router-link to="/register" class="btn-primary">Rejoindre la plateforme</router-link>
              <router-link to="/annuaire-public" class="btn-outline">Explorer l'annuaire</router-link>
            </div>
          </div>

          <div class="pourquoi-right">
            <div class="raison-item" v-for="(r, i) in raisons" :key="i">
              <div class="raison-check">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
              </div>
              <div>
                <div class="raison-titre">{{ r.titre }}</div>
                <div class="raison-desc">{{ r.desc }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ═══ CONCLUSION ═════════════════════════════════ -->
    <section class="conclusion-section">
      <div class="container">
        <div class="conclusion-inner">
          <div class="conclusion-icon">🚀</div>
          <h2 class="conclusion-titre">Construisons l'avenir ensemble</h2>
          <p class="conclusion-texte">
            Chez SIS, nous croyons que la technologie peut rapprocher les personnes, les entreprises
            et les opportunités. Rejoignez notre communauté et participez à la construction
            d'un écosystème numérique plus connecté.
          </p>
          <div class="conclusion-actions">
            <router-link to="/register" class="btn-conclusion-primary">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="16" y1="11" x2="22" y2="11"/></svg>
              Créer un compte
            </router-link>
            <router-link to="/contact" class="btn-conclusion-outline">Nous contacter</router-link>
          </div>
        </div>
      </div>
    </section>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { API_BASE } from '../../api/client.js'
import { useReveal } from '../../composables/useReveal.js'
useReveal()

// ── Stats temps réel ──────────────────────────────────
const loadingStats = ref(true)
const heroStats    = ref([
  { value: '—', label: 'Établissements' },
  { value: '—', label: 'Utilisateurs actifs' },
  { value: '—', label: 'Publications' },
  { value: '2024', label: 'Année de création' },
])

function formatVal(n) {
  if (typeof n !== 'number') return n
  if (n >= 1000) return (n / 1000).toFixed(n % 1000 === 0 ? 0 : 1) + ' k'
  return n.toString()
}

async function chargerStats() {
  try {
    const res  = await fetch(`${API_BASE}/site/stats`)
    const data = await res.json()

    const find = (label) => data.find(s => s.label.toLowerCase().includes(label))

    heroStats.value = [
      { value: formatVal(find('etablissement')?.value ?? '—'), label: 'Établissements' },
      { value: formatVal(find('utilisateur')?.value   ?? '—'), label: 'Utilisateurs actifs' },
      { value: formatVal(find('publication')?.value   ?? '—'), label: 'Publications' },
      { value: '2024', label: 'Année de création' },
    ]
  } catch {
    heroStats.value = heroStats.value.map(s => ({ ...s, value: s.value === '—' ? '—' : s.value }))
  } finally {
    loadingStats.value = false
  }
}

onMounted(chargerStats)

// ── Données statiques ─────────────────────────────────
const offres = [
  { emoji: '🏢', bg: '#dbeafe', titre: 'Annuaire d\'entreprises', desc: 'Découvrez et référencez vos établissements, cliniques, laboratoires et professionnels.' },
  { emoji: '📄', bg: '#d1fae5', titre: 'Publications et annonces', desc: 'Partagez vos actualités, offres et informations avec l\'ensemble de la communauté.' },
  { emoji: '📢', bg: '#fff7ed', titre: 'Promotion de services', desc: 'Mettez en avant vos services et atteignez un public plus large sur la plateforme.' },
  { emoji: '💡', bg: '#f3f0ff', titre: 'Découverte d\'opportunités', desc: 'Trouvez des opportunités professionnelles, des partenariats et des collaborations.' },
  { emoji: '🤝', bg: '#fef9c3', titre: 'Mise en relation', desc: 'Connectez-vous directement avec des prestataires, partenaires et utilisateurs.' },
]

const valeurs = [
  { num: '01', emoji: '💡', nom: 'Innovation', desc: 'Nous explorons constamment de nouvelles façons d\'améliorer l\'expérience numérique.' },
  { num: '02', emoji: '🛡️', nom: 'Confiance', desc: 'La sécurité et la fiabilité sont au cœur de chaque fonctionnalité que nous développons.' },
  { num: '03', emoji: '🌍', nom: 'Accessibilité', desc: 'Nous concevons pour tous, quels que soient les appareils ou les niveaux de connexion.' },
  { num: '04', emoji: '🔍', nom: 'Transparence', desc: 'Nous agissons avec honnêteté envers nos utilisateurs, partenaires et équipes.' },
  { num: '05', emoji: '🌱', nom: 'Développement local', desc: 'Nous valorisons les acteurs locaux et contribuons à la croissance de l\'économie numérique africaine.' },
]

const raisons = [
  { titre: 'Plateforme simple à utiliser', desc: 'Interface intuitive conçue pour tous les profils d\'utilisateurs.' },
  { titre: 'Visibilité accrue pour les professionnels', desc: 'Atteignez des milliers d\'utilisateurs à travers tout le Burkina Faso.' },
  { titre: 'Informations accessibles à tous', desc: 'Accédez gratuitement aux publications, services et annuaire.' },
  { titre: 'Communauté en croissance', desc: 'Une base d\'utilisateurs actifs qui s\'agrandit chaque jour.' },
  { titre: 'Services adaptés aux besoins locaux', desc: 'Pensé spécifiquement pour les réalités du marché burkinabè.' },
]
</script>

<style scoped>
.apropos-page { background: #f5f8fd; }

/* ═══ HÉRO ═══════════════════════════════════════════ */
.hero {
  position: relative; overflow: hidden;
  background: linear-gradient(135deg, #0a2f5f 0%, #0e4f91 55%, #0d9488 100%);
  padding: 96px 28px 80px;
}

.hero-bg {
  position: absolute; inset: 0;
  background-image:
    radial-gradient(circle at 20% 50%, rgba(255,255,255,0.04) 0%, transparent 50%),
    radial-gradient(circle at 80% 20%, rgba(13,148,136,0.15) 0%, transparent 50%),
    linear-gradient(rgba(255,255,255,0.02) 1px, transparent 1px),
    linear-gradient(90deg, rgba(255,255,255,0.02) 1px, transparent 1px);
  background-size: auto, auto, 48px 48px, 48px 48px;
}

.hero-inner { position: relative; z-index: 1; max-width: 780px; margin: 0 auto; text-align: center; }

.hero-badge {
  display: inline-flex; align-items: center; gap: 7px;
  padding: 5px 16px; background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.25);
  border-radius: 999px; font-size: 12.5px; font-weight: 600; color: rgba(255,255,255,0.9);
  margin-bottom: 28px; backdrop-filter: blur(8px);
}

.hero-titre {
  font-size: clamp(32px, 5vw, 52px); font-weight: 900; color: white;
  margin: 0 0 20px; letter-spacing: -0.03em; line-height: 1.1;
}
.accent { color: #67e8f9; }

.hero-texte {
  font-size: 17px; color: rgba(255,255,255,0.78); line-height: 1.7;
  margin: 0 auto 44px; max-width: 640px;
}

.hero-stats {
  display: flex; align-items: center; justify-content: center;
  gap: 0; background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.15);
  border-radius: 18px; padding: 24px 32px; backdrop-filter: blur(12px);
  flex-wrap: wrap; gap: 0;
}
.stat-item { text-align: center; padding: 0 32px; }
.stat-n { font-size: 28px; font-weight: 900; color: white; letter-spacing: -0.03em; min-height: 34px; display: flex; align-items: center; justify-content: center; }
.stat-l { font-size: 12px; color: rgba(255,255,255,0.6); margin-top: 4px; }
.stat-sep { width: 1px; height: 40px; background: rgba(255,255,255,0.2); }

.stat-skel {
  display: inline-block; width: 60px; height: 28px; border-radius: 8px;
  background: linear-gradient(90deg, rgba(255,255,255,0.1) 25%, rgba(255,255,255,0.2) 50%, rgba(255,255,255,0.1) 75%);
  background-size: 200% 100%; animation: shimmer 1.4s infinite;
}
@keyframes shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }

/* ═══ CONTAINER ══════════════════════════════════════ */
.container { max-width: 1100px; margin: 0 auto; padding: 0 28px; }
.section { padding: 88px 0; }

.section-head { text-align: center; margin-bottom: 52px; }
.section-label { font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em; color: #1a6fc4; margin-bottom: 10px; }
.section-label.light { color: rgba(255,255,255,0.6); }
.section-titre { font-size: clamp(22px, 3.5vw, 32px); font-weight: 900; color: #0f172a; margin: 0 0 12px; letter-spacing: -0.025em; }
.section-titre.white { color: white; }
.section-sous { font-size: 15px; color: #64748b; max-width: 520px; margin: 0 auto; line-height: 1.6; }
.section-sous.white-sub { color: rgba(255,255,255,0.65); }

/* ═══ MISSION & VISION ═══════════════════════════════ */
.mv-section { background: white; border-top: 1px solid #e8edf4; border-bottom: 1px solid #e8edf4; }

.mv-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }

.mv-card {
  border-radius: 22px; padding: 40px 36px; position: relative; overflow: hidden;
}
.mission { background: linear-gradient(145deg, #eff6ff, #dbeafe); border: 1.5px solid #bfdbfe; }
.vision  { background: linear-gradient(145deg, #f0fdfa, #ccfbf1); border: 1.5px solid #99f6e4; }

.mv-icon {
  width: 52px; height: 52px; border-radius: 16px; display: flex; align-items: center;
  justify-content: center; margin-bottom: 16px;
}
.mission .mv-icon { background: #1a6fc4; color: white; }
.vision  .mv-icon { background: #0d9488; color: white; }

.mv-tag {
  display: inline-block; font-size: 11px; font-weight: 800;
  text-transform: uppercase; letter-spacing: 0.08em; padding: 3px 12px;
  border-radius: 999px; margin-bottom: 10px;
}
.mission .mv-tag { background: #dbeafe; color: #1a6fc4; }
.vision-tag { background: #ccfbf1; color: #0d9488; }

.mv-titre { font-size: 22px; font-weight: 800; color: #0f172a; margin: 0 0 14px; letter-spacing: -0.02em; }
.mv-texte { font-size: 14.5px; color: #374151; line-height: 1.7; margin: 0 0 22px; }

.mv-liste { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 9px; }
.mv-liste li {
  display: flex; align-items: center; gap: 9px; font-size: 13.5px;
  color: #374151; font-weight: 500;
}
.mv-liste li::before { content: '✓'; font-weight: 900; font-size: 13px; }
.mission .mv-liste li::before { color: #1a6fc4; }
.vision  .mv-liste li::before { color: #0d9488; }

/* ═══ OFFRES ══════════════════════════════════════════ */
.offres-section { background: #f5f8fd; }

.offres-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 16px; }

.offre-card {
  background: white; border: 1px solid #e8edf4; border-radius: 18px;
  padding: 28px 22px; text-align: center;
  transition: box-shadow 0.25s ease, transform 0.25s cubic-bezier(.22,.68,0,1.2), border-color 0.2s;
}
.offre-card:hover { box-shadow: 0 16px 48px rgba(15,23,42,0.13); transform: translateY(-6px); border-color: #bfdbfe; }

.offre-icon {
  width: 58px; height: 58px; border-radius: 18px; margin: 0 auto 16px;
  display: flex; align-items: center; justify-content: center;
}
.offre-emoji { font-size: 26px; }
.offre-titre { font-size: 13.5px; font-weight: 700; color: #0f172a; margin: 0 0 8px; line-height: 1.3; }
.offre-desc { font-size: 12.5px; color: #64748b; line-height: 1.55; margin: 0; }

/* ═══ VALEURS ═════════════════════════════════════════ */
.valeurs-section {
  background: linear-gradient(135deg, #0a2f5f 0%, #0e4f91 55%, #0d9488 100%);
  position: relative; overflow: hidden;
}
.valeurs-section::before {
  content: ''; position: absolute; inset: 0;
  background-image: linear-gradient(rgba(255,255,255,0.03) 1px, transparent 1px),
    linear-gradient(90deg, rgba(255,255,255,0.03) 1px, transparent 1px);
  background-size: 50px 50px;
}
.valeurs-section .container { position: relative; z-index: 1; }

.valeurs-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 16px; }

.valeur-card {
  background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.14);
  border-radius: 18px; padding: 28px 20px; text-align: center;
  backdrop-filter: blur(8px); transition: background 0.2s, transform 0.2s;
}
.valeur-card:hover { background: rgba(255,255,255,0.13); transform: translateY(-4px); }

.valeur-num { font-size: 11px; font-weight: 800; color: rgba(255,255,255,0.35); letter-spacing: 0.06em; margin-bottom: 12px; }
.valeur-icon { font-size: 32px; margin-bottom: 14px; }
.valeur-nom { font-size: 15px; font-weight: 800; color: white; margin: 0 0 9px; }
.valeur-desc { font-size: 12.5px; color: rgba(255,255,255,0.62); line-height: 1.6; margin: 0; }

/* ═══ POURQUOI ════════════════════════════════════════ */
.pourquoi-section { background: white; border-top: 1px solid #e8edf4; }

.pourquoi-grid { display: grid; grid-template-columns: 1fr 1.1fr; gap: 72px; align-items: center; }

.pourquoi-left .section-label { text-align: left; }
.pourquoi-left .section-titre { text-align: left; }

.pourquoi-intro { font-size: 15px; color: #64748b; line-height: 1.7; margin: 16px 0 32px; }

.pourquoi-cta { display: flex; gap: 12px; flex-wrap: wrap; }
.btn-primary {
  padding: 12px 24px; border-radius: 11px; background: linear-gradient(135deg, #1a6fc4, #3b8fd8);
  color: white; font-size: 14px; font-weight: 600; text-decoration: none;
  box-shadow: 0 4px 14px rgba(26,111,196,0.4); transition: all 0.18s;
}
.btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(26,111,196,0.5); }
.btn-outline {
  padding: 12px 24px; border-radius: 11px; border: 1.5px solid #e2e8f0;
  color: #475569; font-size: 14px; font-weight: 600; text-decoration: none;
  background: white; transition: all 0.15s;
}
.btn-outline:hover { border-color: #1a6fc4; color: #1a6fc4; background: #eff6ff; }

.raison-item {
  display: flex; align-items: flex-start; gap: 14px;
  padding: 18px 20px; background: #f8fafc; border-radius: 14px;
  border: 1px solid #e8edf4; margin-bottom: 12px;
  transition: box-shadow 0.15s, transform 0.15s;
}
.raison-item:last-child { margin-bottom: 0; }
.raison-item:hover { box-shadow: 0 4px 16px rgba(15,23,42,0.07); transform: translateX(4px); }

.raison-check {
  width: 32px; height: 32px; border-radius: 10px; flex-shrink: 0;
  background: #d1fae5; color: #059669;
  display: flex; align-items: center; justify-content: center;
}
.raison-titre { font-size: 14px; font-weight: 700; color: #0f172a; margin-bottom: 4px; }
.raison-desc { font-size: 12.5px; color: #64748b; line-height: 1.5; }

/* ═══ CONCLUSION ══════════════════════════════════════ */
.conclusion-section { padding: 0 28px 72px; }
.conclusion-inner {
  max-width: 1100px; margin: 0 auto;
  background: linear-gradient(135deg, #0a2f5f 0%, #0e4f91 50%, #0d9488 100%);
  border-radius: 24px; padding: 72px 48px; text-align: center;
  position: relative; overflow: hidden;
}
.conclusion-inner::before {
  content: ''; position: absolute; inset: 0;
  background-image: radial-gradient(circle at 80% 20%, rgba(103,232,249,0.1) 0%, transparent 50%),
    radial-gradient(circle at 20% 80%, rgba(13,148,136,0.15) 0%, transparent 50%);
}
.conclusion-icon { font-size: 52px; margin-bottom: 20px; position: relative; z-index: 1; }
.conclusion-titre {
  font-size: clamp(24px, 4vw, 36px); font-weight: 900; color: white;
  margin: 0 0 18px; letter-spacing: -0.03em; position: relative; z-index: 1;
}
.conclusion-texte {
  font-size: 16px; color: rgba(255,255,255,0.76); line-height: 1.7;
  max-width: 580px; margin: 0 auto 36px; position: relative; z-index: 1;
}
.conclusion-actions { display: flex; justify-content: center; gap: 14px; flex-wrap: wrap; position: relative; z-index: 1; }

.btn-conclusion-primary {
  display: inline-flex; align-items: center; gap: 8px;
  padding: 14px 28px; background: white; border-radius: 12px;
  color: #1a6fc4; font-size: 15px; font-weight: 700; text-decoration: none;
  box-shadow: 0 4px 20px rgba(0,0,0,0.2); transition: all 0.18s;
}
.btn-conclusion-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 28px rgba(0,0,0,0.28); }

.btn-conclusion-outline {
  padding: 14px 28px; border: 1.5px solid rgba(255,255,255,0.4); border-radius: 12px;
  color: white; font-size: 15px; font-weight: 600; text-decoration: none;
  transition: all 0.15s;
}
.btn-conclusion-outline:hover { background: rgba(255,255,255,0.12); border-color: rgba(255,255,255,0.7); }

/* ═══ RESPONSIVE ══════════════════════════════════════ */
@media (max-width: 960px) {
  .offres-grid  { grid-template-columns: repeat(3, 1fr); }
  .valeurs-grid { grid-template-columns: repeat(3, 1fr); }
}

@media (max-width: 760px) {
  .mv-grid       { grid-template-columns: 1fr; }
  .pourquoi-grid { grid-template-columns: 1fr; gap: 36px; }
  .offres-grid   { grid-template-columns: repeat(2, 1fr); }
  .valeurs-grid  { grid-template-columns: repeat(2, 1fr); }
  .hero-stats    { gap: 0; flex-direction: column; }
  .stat-sep      { width: 80px; height: 1px; }
  .stat-item     { padding: 16px 0; }
  .conclusion-inner { padding: 48px 28px; }
}

@media (max-width: 520px) {
  .offres-grid  { grid-template-columns: 1fr; }
  .valeurs-grid { grid-template-columns: 1fr 1fr; }
  .hero { padding: 64px 20px 56px; }
  .conclusion-section { padding: 0 20px 52px; }
}
</style>
