<template>
  <div class="site-layout">

    <!-- ══ HEADER ═════════════════════════════════ -->
    <header class="site-nav">
      <div class="nav-inner">

        <!-- Logo -->
        <router-link to="/" class="logo">
          <div class="logo-mark"><img src="/mediflow-logo.jpeg" alt="Logo Mediflow" /></div>
          <div class="logo-texts">
            <span class="logo-name">Mediflow</span>
            <span class="logo-sub">La santé connectée</span>
          </div>
        </router-link>

        <!-- Navigation -->
        <nav class="nav-links">
          <router-link to="/"                class="nav-link" exact-active-class="active">Accueil</router-link>
          <router-link to="/annuaire-public" class="nav-link" active-class="active">Annuaire</router-link>
          <router-link to="/actualites" class="nav-link" active-class="active">Actualités</router-link>
          <router-link to="/a-propos" class="nav-link" active-class="active">À propos</router-link>
          <router-link to="/contact" class="nav-link" active-class="active">Contact</router-link>
        </nav>

        <!-- Auth -->
        <div class="nav-auth" v-if="!auth.isAuthenticated">
          <router-link to="/login"    class="btn-login">Connexion</router-link>
          <router-link to="/register" class="btn-signup">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3">
              <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
              <circle cx="9" cy="7" r="4"/>
              <line x1="19" y1="8" x2="19" y2="14"/><line x1="16" y1="11" x2="22" y2="11"/>
            </svg>
            S'inscrire gratuitement
          </router-link>
        </div>
        <div class="nav-auth" v-else>
          <router-link to="/mes-abonnements" class="btn-login">Mes abonnements</router-link>
          <router-link :to="`/profil-public/${auth.user?.id}`" class="btn-login">Mon profil</router-link>
          <button class="btn-signup btn-deconnexion" @click="deconnexion">Déconnexion</button>
        </div>

      </div>
    </header>

    <!-- ══ CONTENU ════════════════════════════════ -->
    <main class="site-main">
      <RouterView v-slot="{ Component }">
        <keep-alive :max="8">
          <component :is="Component" />
        </keep-alive>
      </RouterView>
    </main>

    <!-- ══ FOOTER ═════════════════════════════════ -->
    <footer class="site-footer">
      <div class="footer-inner">

        <!-- Colonne marque -->
        <div class="footer-brand">
          <div class="footer-logo-wrap">
            <div class="footer-logo-mark">
              <svg width="17" height="17" viewBox="0 0 24 24" fill="none">
                <path d="M12 3v18M3 12h18" stroke="white" stroke-width="2.6" stroke-linecap="round"/>
              </svg>
            </div>
            <div>
              <div class="footer-logo-name">Mediflow</div>
              <div class="footer-logo-sub">Burkina Faso · Afrique de l'Ouest</div>
            </div>
          </div>
          <p class="footer-desc">
            Plateforme numérique de référence connectant les acteurs du secteur de la santé au Burkina Faso :
            cliniques, laboratoires, professionnels de santé et patients.
          </p>
        </div>

        <!-- Plateforme -->
        <div class="footer-col">
          <div class="footer-col-title">Plateforme</div>
          <router-link to="/"                class="footer-link">Accueil</router-link>
          <router-link to="/annuaire-public" class="footer-link">Annuaire des services</router-link>
          <router-link to="/login"           class="footer-link">Espace professionnel</router-link>
          <router-link to="/register"        class="footer-link">Créer un compte</router-link>
          <router-link to="/actualites" class="footer-link">Actualités santé</router-link>
        </div>

        <!-- Services -->
        <div class="footer-col">
          <div class="footer-col-title">Services</div>
          <router-link to="/annuaire-public" class="footer-link">Cliniques &amp; Hôpitaux</router-link>
          <router-link to="/annuaire-public" class="footer-link">Laboratoires</router-link>
          <router-link to="/annuaire-public" class="footer-link">Pharmacies</router-link>
          <router-link to="/annuaire-public" class="footer-link">Médecins spécialistes</router-link>
          <router-link to="/annuaire-public" class="footer-link">Soins à domicile</router-link>
        </div>

        <!-- Informations -->
        <div class="footer-col">
          <div class="footer-col-title">Informations</div>
          <router-link to="/a-propos" class="footer-link">À propos de Mediflow</router-link>
          <router-link to="/conditions" class="footer-link">Conditions d'utilisation</router-link>
          <router-link to="/confidentialite" class="footer-link">Politique de confidentialité</router-link>
          <a href="#" class="footer-link">Mentions légales</a>
          <router-link to="/contact" class="footer-link">Contact</router-link>
        </div>

        <!-- Newsletter -->
        <div class="footer-col newsletter-col">
          <div class="footer-col-title">Newsletter Santé</div>
          <p class="newsletter-desc">Recevez les dernières actualités et opportunités du secteur médical.</p>
          <div class="newsletter-form">
            <input type="email" placeholder="votre@email.com" class="newsletter-input" />
            <button class="newsletter-btn">S'abonner</button>
          </div>
        </div>

      </div>

      <div class="footer-bottom">
        <div class="footer-inner footer-bottom-inner">
          <span class="footer-copy">© 2026 Mediflow — Burkina Faso. Tous droits réservés.</span>
          <div class="footer-bottom-links">
            <a href="#" class="footer-bottom-link">Conditions</a>
            <a href="#" class="footer-bottom-link">Confidentialité</a>
            <a href="#" class="footer-bottom-link">Cookies</a>
            <a href="#" class="footer-bottom-link">Aide</a>
          </div>
        </div>
      </div>
    </footer>

  </div>
</template>

<script setup>
import { RouterLink, RouterView, useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth.js'

const auth   = useAuthStore()
const router = useRouter()

async function deconnexion() {
  await auth.logout()
  router.push('/')
}
</script>

<style scoped>
/* ══════════════════════════════════════════════════ */
/* LAYOUT                                             */
/* ══════════════════════════════════════════════════ */

/* Transition page */
.site-main { animation: fadeIn 0.3s ease; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: none; } }

.site-layout {
  display: flex; flex-direction: column; min-height: 100vh;
  background: #f5f8fd;
}

/* ══════════════════════════════════════════════════ */
/* HEADER                                             */
/* ══════════════════════════════════════════════════ */
.site-nav {
  position: sticky; top: 0; z-index: 100;
  height: 68px;
  background: rgba(255,255,255,0.94);
  backdrop-filter: blur(20px) saturate(180%);
  border-bottom: 1px solid rgba(226,232,240,0.9);
  box-shadow: 0 1px 0 rgba(15,23,42,0.04), 0 2px 12px rgba(15,23,42,0.04);
}
.logo-mark img { width: 100%; height: 100%; object-fit: contain; border-radius: inherit; background: white; }

.nav-inner {
  max-width: 1200px; margin: 0 auto; padding: 0 28px;
  height: 100%; display: flex; align-items: center; gap: 28px;
}

/* Logo */
.logo { display: flex; align-items: center; gap: 11px; text-decoration: none; flex-shrink: 0; }

.logo-mark {
  width: 38px; height: 38px; border-radius: 11px;
  background: linear-gradient(135deg, #1a6fc4 0%, #0d9488 100%);
  display: flex; align-items: center; justify-content: center;
  box-shadow: 0 4px 14px rgba(26,111,196,0.35); flex-shrink: 0;
}

.logo-texts { display: flex; flex-direction: column; line-height: 1.15; }
.logo-name { font-size: 15px; font-weight: 800; color: #0f172a; letter-spacing: -0.02em; }
.logo-sub  { font-size: 10px; font-weight: 500; color: #94a3b8; letter-spacing: 0.04em; text-transform: uppercase; }

/* Nav */
.nav-links { display: flex; align-items: center; gap: 2px; flex: 1; }

.nav-link {
  padding: 7px 14px; border-radius: 8px;
  font-size: 13.5px; font-weight: 500; color: #64748b;
  text-decoration: none; transition: color 0.15s, background 0.15s, transform 0.15s;
  position: relative;
}
.nav-link:hover { color: #1a6fc4; background: #eff6ff; transform: translateY(-1px); }
.nav-link.active { color: #1a6fc4; font-weight: 600; background: #eff6ff; }
.nav-link.active::after {
  content: ''; position: absolute; bottom: 2px; left: 50%; transform: translateX(-50%);
  width: 20px; height: 2px; background: #1a6fc4; border-radius: 999px;
}

/* Auth */
.nav-auth { display: flex; align-items: center; gap: 10px; margin-left: auto; flex-shrink: 0; }

.btn-login {
  padding: 8px 18px; border-radius: 9px;
  font-size: 13.5px; font-weight: 500; color: #475569;
  border: 1.5px solid #e2e8f0; background: white;
  text-decoration: none; transition: all 0.14s;
}
.btn-login:hover { color: #1a6fc4; border-color: #1a6fc4; background: #eff6ff; }

.btn-signup {
  display: inline-flex; align-items: center; gap: 7px;
  padding: 9px 18px; border-radius: 9px;
  font-size: 13.5px; font-weight: 600; color: white;
  background: linear-gradient(135deg, #1a6fc4, #3b8fd8);
  box-shadow: 0 3px 12px rgba(26,111,196,0.35);
  text-decoration: none; transition: all 0.18s;
}
.btn-signup:hover { transform: translateY(-1px); box-shadow: 0 5px 18px rgba(26,111,196,0.45); }
.btn-deconnexion { border: none; cursor: pointer; font-family: inherit; }

/* ══════════════════════════════════════════════════ */
/* MAIN                                               */
/* ══════════════════════════════════════════════════ */
.site-main { flex: 1; }

/* ══════════════════════════════════════════════════ */
/* FOOTER                                             */
/* ══════════════════════════════════════════════════ */
.site-footer { background: #0a1628; margin-top: 0; }

.footer-inner {
  max-width: 1200px; margin: 0 auto; padding: 0 28px;
  display: grid; grid-template-columns: 2fr 1fr 1fr 1fr 1.4fr;
  gap: 44px; padding-top: 64px; padding-bottom: 52px;
}

/* Marque */
.footer-logo-wrap { display: flex; align-items: center; gap: 11px; margin-bottom: 16px; }

.footer-logo-mark {
  width: 34px; height: 34px; border-radius: 10px;
  background: linear-gradient(135deg, #1a6fc4, #0d9488);
  display: flex; align-items: center; justify-content: center;
  box-shadow: 0 4px 14px rgba(26,111,196,0.4); flex-shrink: 0;
}

.footer-logo-name { font-size: 13.5px; font-weight: 800; color: white; letter-spacing: -0.02em; line-height: 1.2; }
.footer-logo-sub  { font-size: 10px; color: #64748b; letter-spacing: 0.03em; text-transform: uppercase; }

.footer-desc { font-size: 13px; color: #64748b; line-height: 1.65; max-width: 280px; margin-bottom: 20px; }


/* Colonnes */
.footer-col { display: flex; flex-direction: column; }

.footer-col-title {
  font-size: 10.5px; font-weight: 700; text-transform: uppercase;
  letter-spacing: 0.09em; color: #475569; margin-bottom: 16px;
}

.footer-link {
  font-size: 13.5px; color: #94a3b8; text-decoration: none;
  margin-bottom: 10px; transition: color 0.12s;
}
.footer-link:hover { color: #e2e8f0; }

/* Newsletter */
.newsletter-desc { font-size: 13px; color: #64748b; margin-bottom: 14px; line-height: 1.55; }

.newsletter-form { display: flex; gap: 6px; }

.newsletter-input {
  flex: 1; padding: 9px 13px; border-radius: 9px;
  background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1);
  color: white; font-size: 13px; outline: none;
  transition: border-color 0.14s;
}
.newsletter-input::placeholder { color: #475569; }
.newsletter-input:focus { border-color: #1a6fc4; }

.newsletter-btn {
  padding: 9px 16px; border-radius: 9px; border: none;
  background: #1a6fc4; color: white; font-size: 13px;
  font-weight: 600; cursor: pointer; transition: background 0.15s; white-space: nowrap;
}
.newsletter-btn:hover { background: #3b8fd8; }

/* Footer bottom */
.footer-bottom { border-top: 1px solid #1e293b; }

.footer-bottom-inner {
  padding-top: 20px !important; padding-bottom: 20px !important;
  grid-template-columns: 1fr auto !important;
  gap: 16px !important; align-items: center;
}

.footer-copy { font-size: 12.5px; color: #475569; }

.footer-bottom-links { display: flex; gap: 20px; }

.footer-bottom-link { font-size: 12.5px; color: #475569; text-decoration: none; transition: color 0.12s; }
.footer-bottom-link:hover { color: #94a3b8; }

/* ══════════════════════════════════════════════════ */
/* RESPONSIVE                                         */
/* ══════════════════════════════════════════════════ */
@media (max-width: 1100px) {
  .footer-inner { grid-template-columns: 1fr 1fr 1fr; }
  .footer-brand { grid-column: 1 / -1; }
  .newsletter-col { grid-column: 1 / -1; }
  .newsletter-form { max-width: 360px; }
}

@media (max-width: 860px) {
  .nav-links { display: none; }
  .nav-inner { gap: 12px; }
  .footer-inner { grid-template-columns: 1fr 1fr; padding: 44px 20px 36px; }
  .footer-brand { grid-column: 1 / -1; }
  .newsletter-col { grid-column: 1 / -1; }
}

@media (max-width: 600px) {
  .btn-login { display: none; }
  .nav-inner { padding: 0 16px; }
  .btn-signup { font-size: 12.5px; padding: 8px 14px; }
  .footer-inner { grid-template-columns: 1fr; }
  .footer-bottom-inner { grid-template-columns: 1fr !important; }
  .footer-bottom-links { flex-wrap: wrap; gap: 14px; }
}
</style>
