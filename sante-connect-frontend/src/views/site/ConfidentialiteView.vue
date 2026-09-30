<template>
  <div class="confidentialite-page">

    <!-- ═══ EN-TÊTE ═══════════════════════════════════ -->
    <div class="page-header">
      <div class="header-inner">
        <div class="header-badge">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
          Protection des données
        </div>
        <h1 class="page-titre">Politique de confidentialité de Mediflow</h1>
        <p class="page-sous-titre">
          Chez Mediflow, la protection des données personnelles est une priorité. Cette politique explique
          quelles informations sont collectées, comment elles sont utilisées et comment elles sont protégées.
        </p>
        <div class="header-meta">
          <span class="meta-item">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            Dernière mise à jour : 23 juin 2026
          </span>
          <span class="meta-sep">·</span>
          <span class="meta-item">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            Lecture : 6 min
          </span>
          <span class="meta-sep">·</span>
          <span class="meta-item meta-secure">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            Données protégées
          </span>
        </div>

        <!-- Badges de confiance -->
        <div class="trust-badges">
          <div class="trust-badge">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            Données non vendues
          </div>
          <div class="trust-badge">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            Accès sécurisé
          </div>
          <div class="trust-badge">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            Droits des utilisateurs respectés
          </div>
        </div>
      </div>
    </div>

    <div class="page-body">

      <!-- ═══ TABLE DES MATIÈRES ═══════════════════════ -->
      <aside class="toc">
        <div class="toc-header">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
          Table des matières
        </div>
        <nav class="toc-nav">
          <a
            v-for="s in sections" :key="s.id"
            :href="`#${s.id}`"
            class="toc-link"
            :class="{ active: activeSection === s.id }"
            @click.prevent="scrollTo(s.id)"
          >
            <span class="toc-num">{{ s.num }}</span>
            {{ s.titre }}
          </a>
        </nav>

        <!-- Boîte sécurité -->
        <div class="toc-secure">
          <div class="secure-icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
          </div>
          <div class="secure-text">
            <div class="secure-titre">Vos données sont sécurisées</div>
            <div class="secure-sub">Chiffrement et accès contrôlé</div>
          </div>
        </div>

        <div class="toc-footer">
          <router-link to="/contact" class="toc-contact">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            Une question ?
          </router-link>
        </div>
      </aside>

      <!-- ═══ CONTENU ═══════════════════════════════════ -->
      <main class="content">

        <section v-for="s in sections" :key="s.id" :id="s.id" class="conf-section">
          <div class="section-header">
            <div class="section-icon" :style="`background:${s.bg}`">
              <span>{{ s.emoji }}</span>
            </div>
            <div>
              <div class="section-num">Article {{ s.num }}</div>
              <h2 class="section-titre">{{ s.titre }}</h2>
            </div>
          </div>

          <div class="section-body">
            <p v-if="s.texte" class="section-texte">{{ s.texte }}</p>

            <ul v-if="s.liste" class="section-liste">
              <li v-for="(item, i) in s.liste" :key="i">
                <div class="list-dot" :style="`background:${s.color}`"></div>
                {{ item }}
              </li>
            </ul>

            <div v-if="s.alerte" class="alerte-box" :class="s.alerte.type">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
              <span>{{ s.alerte.texte }}</span>
            </div>

            <p v-if="s.note" class="section-note">{{ s.note }}</p>
          </div>
        </section>

        <!-- Engagement final -->
        <div class="engagement-card">
          <div class="eng-left">
            <div class="eng-icon">🔒</div>
            <div>
              <h3 class="eng-titre">Notre engagement envers vous</h3>
              <p class="eng-texte">
                Chez Mediflow, nous nous engageons à traiter vos données avec le plus grand respect
                et la plus grande transparence. Votre confiance est notre priorité absolue.
              </p>
              <div class="eng-actions">
                <router-link to="/contact" class="btn-primary">Exercer mes droits</router-link>
                <router-link to="/conditions" class="btn-outline">Conditions d'utilisation</router-link>
              </div>
            </div>
          </div>
          <div class="eng-right">
            <div class="eng-stat" v-for="stat in engStats" :key="stat.label">
              <div class="eng-stat-icon">{{ stat.emoji }}</div>
              <div class="eng-stat-val">{{ stat.val }}</div>
              <div class="eng-stat-lbl">{{ stat.label }}</div>
            </div>
          </div>
        </div>

      </main>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useReveal } from '../../composables/useReveal.js'
useReveal('.conf-section')

const activeSection = ref('')

const sections = [
  {
    id: 'collecte', num: '01', emoji: '📋', bg: '#dbeafe', color: '#1a6fc4',
    titre: 'Données collectées',
    texte: 'Dans le cadre de l\'utilisation de la plateforme Mediflow, nous pouvons être amenés à collecter les données suivantes :',
    liste: [
      'Nom et prénom de l\'utilisateur.',
      'Adresse e-mail pour la gestion du compte.',
      'Numéro de téléphone, si fourni volontairement.',
      'Informations du profil utilisateur (poste, entreprise, photo).',
      'Publications et contenus créés sur la plateforme.',
      'Données techniques liées à l\'utilisation du service (adresse IP, navigateur, pages visitées).',
    ],
  },
  {
    id: 'utilisation', num: '02', emoji: '⚙️', bg: '#d1fae5', color: '#059669',
    titre: 'Utilisation des données',
    texte: 'Les données personnelles collectées sont utilisées exclusivement aux fins suivantes :',
    liste: [
      'Créer et gérer les comptes utilisateurs sur la plateforme.',
      'Fournir l\'ensemble des services proposés par Mediflow.',
      'Assurer la sécurité et l\'intégrité des comptes.',
      'Répondre aux demandes d\'assistance et de support.',
      'Améliorer les fonctionnalités et l\'expérience sur Mediflow.',
    ],
  },
  {
    id: 'protection', num: '03', emoji: '🛡️', bg: '#f3f0ff', color: '#7c3aed',
    titre: 'Protection des données',
    texte: 'Mediflow met en œuvre des mesures de sécurité techniques et organisationnelles pour protéger vos données contre tout accès non autorisé, perte ou divulgation.',
    alerte: {
      type: 'success',
      texte: 'Toutes les données sont transmises via des connexions sécurisées (HTTPS) et stockées dans des environnements protégés avec contrôle d\'accès strict.',
    },
  },
  {
    id: 'partage', num: '04', emoji: '🤝', bg: '#fff7ed', color: '#ea580c',
    titre: 'Partage des données',
    texte: 'Les données personnelles des utilisateurs ne sont ni vendues, ni louées, ni cédées à des tiers à des fins commerciales. Elles peuvent être partagées uniquement dans les cas suivants :',
    liste: [
      'Lorsque la loi ou une autorité compétente l\'exige.',
      'Lorsque cela est strictement nécessaire au fonctionnement du service.',
      'Avec des prestataires techniques soumis aux mêmes obligations de confidentialité.',
    ],
    alerte: {
      type: 'success',
      texte: 'Mediflow ne vend jamais vos données personnelles à des tiers. C\'est un engagement ferme.',
    },
  },
  {
    id: 'cookies', num: '05', emoji: '🍪', bg: '#fefce8', color: '#ca8a04',
    titre: 'Cookies et technologies similaires',
    texte: 'Mediflow peut utiliser des cookies et technologies similaires afin d\'améliorer l\'expérience utilisateur, mesurer l\'audience et optimiser les performances de la plateforme.',
    liste: [
      'Cookies de session pour maintenir votre connexion.',
      'Cookies de préférences pour mémoriser vos paramètres.',
      'Cookies d\'analyse pour comprendre l\'utilisation du service.',
    ],
    note: 'Vous pouvez configurer votre navigateur pour refuser les cookies. Certaines fonctionnalités de la plateforme pourraient alors être limitées.',
  },
  {
    id: 'conservation', num: '06', emoji: '🗄️', bg: '#ecfeff', color: '#0891b2',
    titre: 'Conservation des données',
    texte: 'Les données personnelles sont conservées aussi longtemps que nécessaire pour fournir les services Mediflow ou respecter les obligations légales en vigueur. Lors de la suppression d\'un compte, les données associées sont effacées dans un délai raisonnable, sauf obligation légale de conservation.',
  },
  {
    id: 'droits', num: '07', emoji: '⚖️', bg: '#eff6ff', color: '#1a6fc4',
    titre: 'Droits des utilisateurs',
    texte: 'Conformément aux réglementations applicables, chaque utilisateur dispose des droits suivants concernant ses données personnelles :',
    liste: [
      'Consulter l\'ensemble de ses données personnelles enregistrées.',
      'Modifier ou corriger ses informations à tout moment.',
      'Demander la suppression définitive de son compte et de ses données.',
      'Demander des informations sur le traitement et l\'utilisation de ses données.',
    ],
    alerte: {
      type: 'info',
      texte: 'Pour exercer ces droits, contactez notre équipe via la page Contact. Nous traitons toutes les demandes dans un délai de 30 jours ouvrés.',
    },
  },
  {
    id: 'securite', num: '08', emoji: '🔐', bg: '#f0fdf4', color: '#16a34a',
    titre: 'Sécurité du compte',
    texte: 'Chaque utilisateur est invité à prendre les mesures suivantes pour protéger son compte sur Mediflow :',
    liste: [
      'Utiliser un mot de passe fort et unique, non partagé avec d\'autres services.',
      'Ne jamais communiquer ses identifiants de connexion à des tiers.',
      'Se déconnecter après chaque session sur un appareil partagé.',
      'Signaler immédiatement toute activité suspecte à l\'équipe Mediflow.',
    ],
  },
  {
    id: 'modifications', num: '09', emoji: '🔄', bg: '#fff7ed', color: '#ea580c',
    titre: 'Modifications de la politique',
    texte: 'Cette politique de confidentialité peut être mise à jour à tout moment afin de refléter l\'évolution des services Mediflow ou de répondre aux nouvelles obligations légales.',
    alerte: {
      type: 'info',
      texte: 'En cas de modification significative, les utilisateurs seront informés par e-mail ou via une notification sur la plateforme.',
    },
  },
  {
    id: 'contact-conf', num: '10', emoji: '💬', bg: '#dbeafe', color: '#1a6fc4',
    titre: 'Contact',
    texte: 'Pour toute question concernant la confidentialité ou vos données personnelles, ou pour exercer vos droits, les utilisateurs peuvent contacter directement l\'équipe Mediflow via la page Contact de la plateforme. Nous nous engageons à répondre dans les meilleurs délais.',
  },
]

const engStats = [
  { emoji: '🔒', val: '100%', label: 'Données chiffrées' },
  { emoji: '🚫', val: '0', label: 'Revente de données' },
  { emoji: '⚡', val: '30j', label: 'Délai de réponse max' },
]

function scrollTo(id) {
  const el = document.getElementById(id)
  if (el) {
    const top = el.getBoundingClientRect().top + window.scrollY - 90
    window.scrollTo({ top, behavior: 'smooth' })
    activeSection.value = id
  }
}

function onScroll() {
  const ids = sections.map(s => s.id)
  for (const id of [...ids].reverse()) {
    const el = document.getElementById(id)
    if (el && el.getBoundingClientRect().top <= 120) {
      activeSection.value = id
      return
    }
  }
  activeSection.value = ids[0]
}

onMounted(() => {
  window.addEventListener('scroll', onScroll, { passive: true })
  onScroll()
})
onUnmounted(() => window.removeEventListener('scroll', onScroll))
</script>

<style scoped>
.confidentialite-page { min-height: 100vh; background: #f5f8fd; }

/* ═══ EN-TÊTE ════════════════════════════════════════ */
.page-header {
  background: linear-gradient(135deg, #f0fdf9 0%, #ecfdf5 40%, #eff6ff 100%);
  border-bottom: 1px solid #e2e8f0;
  padding: 60px 28px 48px;
}
.header-inner { max-width: 720px; margin: 0 auto; text-align: center; }

.header-badge {
  display: inline-flex; align-items: center; gap: 7px;
  padding: 5px 14px; background: white; border: 1px solid #e2e8f0;
  border-radius: 999px; font-size: 12px; font-weight: 600; color: #059669;
  margin-bottom: 22px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.page-titre {
  font-size: clamp(24px, 4vw, 36px); font-weight: 900; color: #0f172a;
  margin: 0 0 14px; line-height: 1.15; letter-spacing: -0.03em;
}
.page-sous-titre { font-size: 15px; color: #64748b; line-height: 1.65; margin: 0 0 22px; }

.header-meta {
  display: inline-flex; align-items: center; gap: 10px; flex-wrap: wrap; justify-content: center;
  background: white; border: 1px solid #e2e8f0; border-radius: 999px;
  padding: 7px 18px; font-size: 12.5px; color: #64748b; margin-bottom: 20px;
}
.meta-item { display: flex; align-items: center; gap: 5px; }
.meta-secure { color: #059669; font-weight: 600; }
.meta-sep { color: #e2e8f0; }

.trust-badges { display: flex; justify-content: center; gap: 10px; flex-wrap: wrap; }
.trust-badge {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 6px 14px; background: #f0fdf4; border: 1px solid #bbf7d0;
  border-radius: 999px; font-size: 12px; font-weight: 600; color: #15803d;
}

/* ═══ LAYOUT ══════════════════════════════════════════ */
.page-body {
  max-width: 1100px; margin: 0 auto; padding: 44px 28px 72px;
  display: grid; grid-template-columns: 260px 1fr; gap: 32px; align-items: start;
}

/* ═══ TABLE DES MATIÈRES ══════════════════════════════ */
.toc {
  position: sticky; top: 80px; background: white;
  border: 1px solid #e8edf4; border-radius: 18px; overflow: hidden;
}

.toc-header {
  display: flex; align-items: center; gap: 8px;
  padding: 16px 20px; border-bottom: 1px solid #f1f5f9;
  font-size: 12px; font-weight: 700; color: #64748b;
  text-transform: uppercase; letter-spacing: 0.07em;
}

.toc-nav { padding: 10px 0; }

.toc-link {
  display: flex; align-items: center; gap: 10px;
  padding: 8px 20px; font-size: 13px; color: #64748b;
  text-decoration: none; transition: all 0.12s;
  border-left: 3px solid transparent; line-height: 1.35;
}
.toc-link:hover { background: #f8fafc; color: #334155; border-left-color: #e2e8f0; }
.toc-link.active { background: #f0fdf4; color: #059669; font-weight: 600; border-left-color: #059669; }

.toc-num {
  font-size: 10px; font-weight: 800; color: #94a3b8; min-width: 22px;
}
.toc-link.active .toc-num { color: #059669; }

.toc-secure {
  margin: 8px 12px; background: linear-gradient(135deg, #f0fdf4, #dcfce7);
  border: 1px solid #bbf7d0; border-radius: 12px; padding: 14px;
  display: flex; align-items: center; gap: 10px;
}
.secure-icon {
  width: 36px; height: 36px; border-radius: 10px; background: #16a34a;
  color: white; display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.secure-titre { font-size: 12.5px; font-weight: 700; color: #15803d; }
.secure-sub { font-size: 11px; color: #4ade80; margin-top: 2px; }

.toc-footer { padding: 14px 20px; border-top: 1px solid #f1f5f9; }
.toc-contact {
  display: flex; align-items: center; gap: 7px;
  font-size: 12.5px; font-weight: 600; color: #1a6fc4; text-decoration: none;
}
.toc-contact:hover { color: #0e4f91; }

/* ═══ CONTENU ══════════════════════════════════════════ */
.content { display: flex; flex-direction: column; }

.conf-section {
  background: white; border: 1px solid #e8edf4; border-radius: 18px;
  padding: 32px 36px; margin-bottom: 14px;
  scroll-margin-top: 90px; transition: box-shadow 0.2s;
}
.conf-section:hover { box-shadow: 0 4px 24px rgba(15,23,42,0.07); }

.section-header { display: flex; align-items: flex-start; gap: 16px; margin-bottom: 20px; }
.section-icon {
  width: 48px; height: 48px; border-radius: 14px; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center; font-size: 22px;
}
.section-num { font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: #94a3b8; margin-bottom: 4px; }
.section-titre { font-size: 18px; font-weight: 800; color: #0f172a; margin: 0; }

.section-body { padding-left: 64px; }
.section-texte { font-size: 14.5px; color: #374151; line-height: 1.75; margin: 0 0 16px; }
.section-note { font-size: 13px; color: #64748b; line-height: 1.65; margin: 12px 0 0; font-style: italic; border-left: 3px solid #e2e8f0; padding-left: 14px; }

.section-liste { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 10px; }
.section-liste li { display: flex; align-items: flex-start; gap: 10px; font-size: 14px; color: #374151; line-height: 1.6; }
.list-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; margin-top: 7px; }

.alerte-box {
  display: flex; align-items: flex-start; gap: 10px;
  padding: 14px 16px; border-radius: 12px; margin-top: 16px;
  font-size: 13.5px; line-height: 1.6;
}
.alerte-box.success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #15803d; }
.alerte-box.info    { background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; }
.alerte-box svg { flex-shrink: 0; margin-top: 2px; }

/* ═══ ENGAGEMENT ══════════════════════════════════════ */
.engagement-card {
  background: linear-gradient(135deg, #0a2f5f 0%, #0e4f91 55%, #0d9488 100%);
  border-radius: 20px; padding: 40px;
  display: grid; grid-template-columns: 1fr auto; gap: 40px; align-items: center;
  margin-top: 8px; position: relative; overflow: hidden;
}
.engagement-card::before {
  content: ''; position: absolute; inset: 0;
  background-image: radial-gradient(circle at 90% 10%, rgba(103,232,249,0.1) 0%, transparent 50%);
}
.eng-left { position: relative; z-index: 1; }
.eng-icon { font-size: 36px; margin-bottom: 14px; }
.eng-titre { font-size: 20px; font-weight: 800; color: white; margin: 0 0 10px; }
.eng-texte { font-size: 14px; color: rgba(255,255,255,0.72); line-height: 1.65; margin: 0 0 24px; max-width: 480px; }
.eng-actions { display: flex; gap: 12px; flex-wrap: wrap; }

.btn-primary {
  padding: 11px 22px; background: white; border-radius: 10px;
  color: #1a6fc4; font-size: 13.5px; font-weight: 700; text-decoration: none;
  transition: all 0.15s; box-shadow: 0 4px 14px rgba(0,0,0,0.15);
}
.btn-primary:hover { transform: translateY(-1px); }
.btn-outline {
  padding: 11px 22px; border: 1.5px solid rgba(255,255,255,0.35); border-radius: 10px;
  color: white; font-size: 13.5px; font-weight: 600; text-decoration: none; transition: all 0.15s;
}
.btn-outline:hover { background: rgba(255,255,255,0.12); }

.eng-right {
  display: flex; flex-direction: column; gap: 12px;
  position: relative; z-index: 1;
}
.eng-stat {
  background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.15);
  border-radius: 14px; padding: 16px 20px; text-align: center; min-width: 120px;
  backdrop-filter: blur(8px);
}
.eng-stat-icon { font-size: 22px; margin-bottom: 6px; }
.eng-stat-val { font-size: 22px; font-weight: 900; color: white; letter-spacing: -0.02em; }
.eng-stat-lbl { font-size: 11px; color: rgba(255,255,255,0.6); margin-top: 3px; }

/* ═══ RESPONSIVE ══════════════════════════════════════ */
@media (max-width: 860px) {
  .page-body { grid-template-columns: 1fr; }
  .toc { position: static; }
  .toc-nav { display: flex; flex-wrap: wrap; gap: 2px; padding: 10px; }
  .toc-link { border-left: none; border-radius: 8px; padding: 7px 12px; flex: 0 0 auto; }
  .toc-link.active { border-left: none; }
  .engagement-card { grid-template-columns: 1fr; gap: 24px; }
  .eng-right { flex-direction: row; justify-content: center; flex-wrap: wrap; }
}

@media (max-width: 560px) {
  .conf-section { padding: 24px 20px; }
  .section-body { padding-left: 0; }
  .section-header { flex-direction: column; gap: 12px; }
  .page-body { padding: 28px 16px 52px; }
  .page-header { padding: 44px 20px 36px; }
  .trust-badges { gap: 6px; }
  .engagement-card { padding: 28px 20px; }
}
</style>
