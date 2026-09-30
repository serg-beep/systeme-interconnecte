<template>
  <div class="conditions-page">

    <!-- ═══ EN-TÊTE ═══════════════════════════════════ -->
    <div class="page-header">
      <div class="header-inner">
        <div class="header-badge">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
          Cadre légal
        </div>
        <h1 class="page-titre">Conditions d'utilisation de Mediflow</h1>
        <p class="page-sous-titre">
          Bienvenue sur Mediflow. En utilisant notre plateforme, vous acceptez les présentes conditions
          d'utilisation. Nous vous invitons à les lire attentivement avant d'utiliser nos services.
        </p>
        <div class="header-meta">
          <span class="meta-item">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            Dernière mise à jour : 23 juin 2026
          </span>
          <span class="meta-sep">·</span>
          <span class="meta-item">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            Lecture : 5 min
          </span>
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
            v-for="section in sections" :key="section.id"
            :href="`#${section.id}`"
            class="toc-link"
            :class="{ active: activeSection === section.id }"
            @click.prevent="scrollTo(section.id)"
          >
            <span class="toc-num">{{ section.num }}</span>
            {{ section.titre }}
          </a>
        </nav>
        <div class="toc-footer">
          <router-link to="/contact" class="toc-contact">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            Une question ?
          </router-link>
        </div>
      </aside>

      <!-- ═══ CONTENU ═══════════════════════════════════ -->
      <main class="content">

        <section v-for="section in sections" :key="section.id" :id="section.id" class="cond-section">
          <div class="section-header">
            <div class="section-icon" :style="`background:${section.bg}`">
              <span>{{ section.emoji }}</span>
            </div>
            <div>
              <div class="section-num">Article {{ section.num }}</div>
              <h2 class="section-titre">{{ section.titre }}</h2>
            </div>
          </div>

          <div class="section-body">
            <p v-if="section.texte" class="section-texte">{{ section.texte }}</p>

            <ul v-if="section.liste" class="section-liste">
              <li v-for="(item, i) in section.liste" :key="i">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" :stroke="section.color || '#1a6fc4'" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                {{ item }}
              </li>
            </ul>

            <div v-if="section.alerte" class="alerte-box" :class="section.alerte.type">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
              {{ section.alerte.texte }}
            </div>

            <p v-if="section.note" class="section-note">{{ section.note }}</p>
          </div>
        </section>

        <!-- Pied de document -->
        <div class="doc-footer">
          <div class="doc-footer-inner">
            <div class="doc-footer-logo">
              <div class="logo-mark">
                <img src="/mediflow-logo.jpeg" alt="Logo Mediflow" />
              </div>
              <span>Mediflow — La santé connectée</span>
            </div>
            <p class="doc-footer-texte">
              Ces conditions ont été rédigées pour protéger les utilisateurs et assurer un usage équitable et sécurisé de la plateforme Mediflow au Burkina Faso.
            </p>
            <div class="doc-footer-actions">
              <router-link to="/contact" class="btn-contact">Nous contacter</router-link>
              <router-link to="/a-propos" class="btn-apropos">En savoir plus sur Mediflow</router-link>
            </div>
          </div>
        </div>

      </main>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useReveal } from '../../composables/useReveal.js'
useReveal('.cond-section')

const activeSection = ref('')

const sections = [
  {
    id: 'objet', num: '01', emoji: '🎯', bg: '#dbeafe', color: '#1a6fc4',
    titre: 'Objet de la plateforme',
    texte: 'Mediflow est une plateforme numérique permettant aux utilisateurs de publier, consulter et promouvoir des services, des annonces, des informations et des opportunités. Elle a pour vocation de connecter les acteurs économiques, institutionnels et individuels au Burkina Faso.',
  },
  {
    id: 'compte', num: '02', emoji: '👤', bg: '#d1fae5', color: '#059669',
    titre: 'Création et gestion de compte',
    texte: 'Lors de la création d\'un compte, l\'utilisateur s\'engage à respecter les règles suivantes :',
    liste: [
      'Les informations fournies doivent être exactes et à jour.',
      'Chaque utilisateur est entièrement responsable de son compte.',
      'Le partage de compte avec des tiers est fortement déconseillé.',
      'L\'utilisateur doit protéger ses identifiants de connexion et ne pas les divulguer.',
    ],
  },
  {
    id: 'usage', num: '03', emoji: '✅', bg: '#f0fdf4', color: '#16a34a',
    titre: 'Utilisation acceptable',
    texte: 'En utilisant Mediflow, les utilisateurs s\'engagent à :',
    liste: [
      'Respecter toutes les lois et réglementations en vigueur au Burkina Faso.',
      'Publier uniquement des informations véridiques et vérifiables.',
      'Respecter les autres membres de la communauté Mediflow.',
      'Ne pas porter atteinte aux droits de propriété intellectuelle.',
      'Utiliser la plateforme à des fins légales et éthiques.',
    ],
  },
  {
    id: 'interdits', num: '04', emoji: '🚫', bg: '#fef2f2', color: '#dc2626',
    titre: 'Contenus interdits',
    texte: 'Il est strictement interdit de publier sur Mediflow les contenus suivants :',
    liste: [
      'Des contenus illégaux ou contraires aux lois burkinabè.',
      'Des contenus frauduleux, trompeurs ou mensongers.',
      'Des contenus offensants, haineux ou discriminatoires envers un groupe ou individu.',
      'Du spam, des publicités abusives ou du contenu non sollicité.',
      'Toute activité portant atteinte aux droits ou à la vie privée d\'autrui.',
    ],
    alerte: {
      type: 'danger',
      texte: 'Tout contenu enfreignant ces règles sera supprimé et le compte pourra être suspendu ou définitivement banni.',
    },
  },
  {
    id: 'responsabilite', num: '05', emoji: '⚖️', bg: '#fff7ed', color: '#ea580c',
    titre: 'Responsabilité des utilisateurs',
    texte: 'Chaque utilisateur est seul responsable des contenus qu\'il publie sur Mediflow. La plateforme ne saurait être tenue responsable des informations erronées, inexactes ou trompeuses publiées par ses membres.',
    note: 'Mediflow agit uniquement en tant qu\'hébergeur de contenus et ne vérifie pas systématiquement l\'exactitude des informations publiées par les utilisateurs.',
  },
  {
    id: 'moderation', num: '06', emoji: '🛡️', bg: '#f3f0ff', color: '#7c3aed',
    titre: 'Modération',
    texte: 'Mediflow se réserve le droit de modifier, suspendre ou supprimer tout contenu ou compte ne respectant pas les présentes conditions d\'utilisation, et ce, sans préavis ni justification obligatoire.',
    liste: [
      'Suppression de contenus non conformes.',
      'Suspension temporaire ou définitive d\'un compte.',
      'Signalement aux autorités compétentes en cas d\'infraction grave.',
    ],
  },
  {
    id: 'disponibilite', num: '07', emoji: '🖥️', bg: '#ecfeff', color: '#0891b2',
    titre: 'Disponibilité du service',
    texte: 'Mediflow s\'efforce d\'assurer la disponibilité de la plateforme 24h/24 et 7j/7, mais ne garantit pas un accès ininterrompu. Des interruptions peuvent survenir pour maintenance, mise à jour ou pour des raisons techniques indépendantes de notre volonté.',
  },
  {
    id: 'modifications', num: '08', emoji: '🔄', bg: '#fefce8', color: '#ca8a04',
    titre: 'Modification des conditions',
    texte: 'Les présentes conditions peuvent être modifiées à tout moment par l\'équipe Mediflow, afin d\'améliorer le service, d\'intégrer de nouvelles fonctionnalités ou de respecter les obligations légales en vigueur. Les utilisateurs seront informés de tout changement significatif.',
    alerte: {
      type: 'info',
      texte: 'Nous vous recommandons de consulter régulièrement cette page pour rester informé des éventuelles modifications.',
    },
  },
  {
    id: 'contact', num: '09', emoji: '💬', bg: '#eff6ff', color: '#1a6fc4',
    titre: 'Contact',
    texte: 'Pour toute question relative aux conditions d\'utilisation, ou pour signaler un contenu non conforme, les utilisateurs peuvent contacter directement l\'équipe Mediflow via la page Contact de la plateforme.',
  },
]

function scrollTo(id) {
  const el = document.getElementById(id)
  if (el) {
    const offset = 90
    const top = el.getBoundingClientRect().top + window.scrollY - offset
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
.conditions-page { min-height: 100vh; background: #f5f8fd; }

/* ═══ EN-TÊTE ════════════════════════════════════════ */
.page-header {
  background: linear-gradient(135deg, #f0fdf9 0%, #ecfdf5 40%, #eff6ff 100%);
  border-bottom: 1px solid #e2e8f0;
  padding: 60px 28px 48px;
}
.header-inner { max-width: 680px; margin: 0 auto; text-align: center; }

.header-badge {
  display: inline-flex; align-items: center; gap: 7px;
  padding: 5px 14px; background: white; border: 1px solid #e2e8f0;
  border-radius: 999px; font-size: 12px; font-weight: 600; color: #1a6fc4;
  margin-bottom: 22px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.page-titre {
  font-size: clamp(24px, 4vw, 36px); font-weight: 900; color: #0f172a;
  margin: 0 0 14px; line-height: 1.15; letter-spacing: -0.03em;
}
.page-sous-titre { font-size: 15px; color: #64748b; line-height: 1.65; margin: 0 0 22px; }

.header-meta {
  display: inline-flex; align-items: center; gap: 10px;
  background: white; border: 1px solid #e2e8f0; border-radius: 999px;
  padding: 7px 18px; font-size: 12.5px; color: #64748b;
}
.meta-item { display: flex; align-items: center; gap: 5px; }
.meta-sep { color: #e2e8f0; }

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
  padding: 9px 20px; font-size: 13px; color: #64748b;
  text-decoration: none; transition: all 0.12s; border-left: 3px solid transparent;
  line-height: 1.35;
}
.toc-link:hover { background: #f8fafc; color: #334155; border-left-color: #e2e8f0; }
.toc-link.active { background: #eff6ff; color: #1a6fc4; font-weight: 600; border-left-color: #1a6fc4; }

.toc-num {
  font-size: 10px; font-weight: 800; color: #94a3b8;
  min-width: 22px; font-variant-numeric: tabular-nums;
}
.toc-link.active .toc-num { color: #1a6fc4; }

.toc-footer {
  padding: 14px 20px; border-top: 1px solid #f1f5f9;
}
.toc-contact {
  display: flex; align-items: center; gap: 7px;
  font-size: 12.5px; font-weight: 600; color: #1a6fc4;
  text-decoration: none; transition: color 0.12s;
}
.toc-contact:hover { color: #0e4f91; }

/* ═══ CONTENU ══════════════════════════════════════════ */
.content { display: flex; flex-direction: column; gap: 0; }

.cond-section {
  background: white; border: 1px solid #e8edf4; border-radius: 18px;
  padding: 32px 36px; margin-bottom: 16px;
  scroll-margin-top: 90px; transition: box-shadow 0.2s;
}
.cond-section:hover { box-shadow: 0 4px 24px rgba(15,23,42,0.07); }

.section-header { display: flex; align-items: flex-start; gap: 16px; margin-bottom: 20px; }

.section-icon {
  width: 48px; height: 48px; border-radius: 14px; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center; font-size: 22px;
}

.section-num {
  font-size: 11px; font-weight: 800; text-transform: uppercase;
  letter-spacing: 0.08em; color: #94a3b8; margin-bottom: 4px;
}
.section-titre { font-size: 18px; font-weight: 800; color: #0f172a; margin: 0; line-height: 1.3; }

.section-body { padding-left: 64px; }

.section-texte { font-size: 14.5px; color: #374151; line-height: 1.75; margin: 0 0 16px; }
.section-note { font-size: 13px; color: #64748b; line-height: 1.65; margin: 12px 0 0; font-style: italic; border-left: 3px solid #e2e8f0; padding-left: 14px; }

.section-liste {
  list-style: none; margin: 0; padding: 0;
  display: flex; flex-direction: column; gap: 10px;
}
.section-liste li {
  display: flex; align-items: flex-start; gap: 10px;
  font-size: 14px; color: #374151; line-height: 1.6;
}
.section-liste li svg { flex-shrink: 0; margin-top: 3px; }

.alerte-box {
  display: flex; align-items: flex-start; gap: 10px;
  padding: 14px 16px; border-radius: 12px; margin-top: 16px;
  font-size: 13.5px; line-height: 1.6;
}
.alerte-box.danger { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }
.alerte-box.info   { background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; }
.alerte-box svg { flex-shrink: 0; margin-top: 2px; }

/* ═══ PIED DE DOCUMENT ════════════════════════════════ */
.doc-footer {
  background: linear-gradient(135deg, #0a2f5f 0%, #0e4f91 55%, #0d9488 100%);
  border-radius: 18px; padding: 40px 36px; margin-top: 8px;
}
.doc-footer-logo {
  display: flex; align-items: center; gap: 10px;
  font-size: 14px; font-weight: 700; color: white; margin-bottom: 14px;
}
.logo-mark {
  width: 32px; height: 32px; border-radius: 9px; flex-shrink: 0;
  background: rgba(255,255,255,0.15); display: flex; align-items: center; justify-content: center;
  overflow: hidden;
}
.logo-mark img { width: 100%; height: 100%; object-fit: contain; border-radius: inherit; background: white; }
.doc-footer-texte { font-size: 14px; color: rgba(255,255,255,0.7); line-height: 1.65; margin: 0 0 24px; max-width: 520px; }
.doc-footer-actions { display: flex; gap: 12px; flex-wrap: wrap; }

.btn-contact {
  padding: 11px 22px; background: white; border-radius: 10px;
  color: #1a6fc4; font-size: 13.5px; font-weight: 700; text-decoration: none;
  transition: all 0.15s;
}
.btn-contact:hover { transform: translateY(-1px); box-shadow: 0 4px 14px rgba(0,0,0,0.15); }

.btn-apropos {
  padding: 11px 22px; border: 1.5px solid rgba(255,255,255,0.35); border-radius: 10px;
  color: white; font-size: 13.5px; font-weight: 600; text-decoration: none;
  transition: all 0.15s;
}
.btn-apropos:hover { background: rgba(255,255,255,0.12); border-color: rgba(255,255,255,0.6); }

/* ═══ RESPONSIVE ══════════════════════════════════════ */
@media (max-width: 860px) {
  .page-body { grid-template-columns: 1fr; }
  .toc { position: static; }
  .toc-nav { display: flex; flex-wrap: wrap; gap: 2px; padding: 10px; }
  .toc-link { border-left: none; border-radius: 8px; padding: 7px 12px; flex: 0 0 auto; }
  .toc-link.active { border-left: none; }
}

@media (max-width: 560px) {
  .cond-section { padding: 24px 20px; }
  .section-body { padding-left: 0; }
  .section-header { flex-direction: column; gap: 12px; }
  .page-body { padding: 28px 16px 52px; }
  .page-header { padding: 44px 20px 36px; }
  .doc-footer { padding: 28px 20px; }
}
</style>
