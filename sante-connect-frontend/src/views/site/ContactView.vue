<template>
  <div class="contact-page">

    <!-- ═══ EN-TÊTE ═══════════════════════════════════ -->
    <div class="page-header">
      <div class="header-inner">
        <div class="header-badge">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
          Support & Contact
        </div>
        <h1 class="page-titre">Contactez-nous</h1>
        <p class="page-sous-titre">
          Une question, une suggestion ou besoin d'assistance ?<br />
          Notre équipe est à votre écoute et vous répondra dans les meilleurs délais.
        </p>
      </div>
    </div>

    <div class="page-body">

      <!-- ═══ INFOS CONTACT ════════════════════════════ -->
      <div class="contact-cards">
        <div class="contact-card">
          <div class="cc-icon email-icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
          </div>
          <div class="cc-content">
            <div class="cc-label">Adresse e-mail</div>
            <a href="mailto:contact@lameda.com" class="cc-value">contact@lameda.com</a>
            <div class="cc-note">Réponse sous 24h ouvrées</div>
          </div>
        </div>

        <div class="contact-card">
          <div class="cc-icon whatsapp-icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
          </div>
          <div class="cc-content">
            <div class="cc-label">WhatsApp</div>
            <a href="https://wa.me/22600000000" target="_blank" class="cc-value">+226 XX XX XX XX</a>
            <div class="cc-note">Disponible 8h – 18h (heure locale)</div>
          </div>
        </div>

        <div class="contact-card">
          <div class="cc-icon location-icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
          </div>
          <div class="cc-content">
            <div class="cc-label">Localisation</div>
            <div class="cc-value">Ouagadougou, Burkina Faso</div>
            <div class="cc-note">Afrique de l'Ouest</div>
          </div>
        </div>
      </div>

      <!-- ═══ FORMULAIRE + AIDE ════════════════════════ -->
      <div class="main-grid">

        <!-- Formulaire -->
        <div class="form-card">
          <div class="form-header">
            <h2 class="form-titre">Envoyez-nous un message</h2>
            <p class="form-sous-titre">Remplissez le formulaire ci-dessous et nous vous répondrons rapidement.</p>
          </div>

          <form @submit.prevent="envoyer" class="form">
            <div class="form-row">
              <div class="champ">
                <label>Nom complet <span class="requis">*</span></label>
                <div class="input-wrap">
                  <svg class="input-ico" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                  <input v-model="form.nom" type="text" placeholder="Votre nom et prénom" required />
                </div>
              </div>

              <div class="champ">
                <label>Adresse e-mail <span class="requis">*</span></label>
                <div class="input-wrap">
                  <svg class="input-ico" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                  <input v-model="form.email" type="email" placeholder="votre@email.com" required />
                </div>
              </div>
            </div>

            <div class="champ">
              <label>Sujet <span class="requis">*</span></label>
              <div class="input-wrap">
                <svg class="input-ico" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                <select v-model="form.sujet" required class="select-field">
                  <option value="" disabled>Sélectionner un sujet</option>
                  <option>Question générale</option>
                  <option>Problème technique</option>
                  <option>Publication ou service</option>
                  <option>Compte utilisateur</option>
                  <option>Partenariat</option>
                  <option>Autre</option>
                </select>
              </div>
            </div>

            <div class="champ">
              <label>Message <span class="requis">*</span></label>
              <textarea v-model="form.message" rows="5" placeholder="Décrivez votre demande en détail..." required></textarea>
              <div class="champ-hint">{{ form.message.length }} / 1000 caractères</div>
            </div>

            <div v-if="succes" class="succes-box">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
              Message envoyé avec succès ! Nous vous répondrons très bientôt.
            </div>

            <div v-if="erreur" class="erreur-box">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
              {{ erreur }}
            </div>

            <button type="submit" class="btn-envoyer" :disabled="envoi">
              <span v-if="!envoi" class="btn-content">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                Envoyer le message
              </span>
              <span v-else class="btn-content">
                <svg class="spin" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                Envoi en cours...
              </span>
            </button>
          </form>
        </div>

        <!-- Colonne droite -->
        <div class="side-col">

          <!-- Section aide -->
          <div class="aide-card">
            <div class="aide-icon">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            </div>
            <h3 class="aide-titre">Besoin d'assistance ?</h3>
            <p class="aide-texte">Pour toute question concernant les publications, les services ou votre compte utilisateur, n'hésitez pas à nous contacter.</p>
            <div class="aide-items">
              <div class="aide-item">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#1a6fc4" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                <span>Publications et annuaire</span>
              </div>
              <div class="aide-item">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#1a6fc4" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                <span>Gestion de compte</span>
              </div>
              <div class="aide-item">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#1a6fc4" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                <span>Problèmes techniques</span>
              </div>
              <div class="aide-item">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#1a6fc4" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                <span>Partenariats institutionnels</span>
              </div>
            </div>
          </div>

          <!-- Temps de réponse -->
          <div class="response-card">
            <div class="rc-header">
              <div class="rc-dot"></div>
              <span class="rc-status">Équipe disponible</span>
            </div>
            <div class="rc-grid">
              <div class="rc-item">
                <div class="rc-val">24h</div>
                <div class="rc-lbl">Délai de réponse email</div>
              </div>
              <div class="rc-item">
                <div class="rc-val">8h-18h</div>
                <div class="rc-lbl">Horaires WhatsApp</div>
              </div>
            </div>
          </div>

        </div>
      </div>

      <!-- ═══ PIED DE PAGE SECTION ══════════════════════ -->
      <div class="footer-section">
        <div class="footer-section-inner">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
          <p>Connecter les utilisateurs aux services et opportunités.</p>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useReveal } from '../../composables/useReveal.js'
useReveal()

const envoi  = ref(false)
const succes = ref(false)
const erreur = ref('')

const form = reactive({ nom: '', email: '', sujet: '', message: '' })

async function envoyer() {
  erreur.value = ''
  if (!form.nom.trim() || !form.email.trim() || !form.sujet || !form.message.trim()) {
    erreur.value = 'Veuillez remplir tous les champs obligatoires.'
    return
  }
  if (form.message.length > 1000) {
    erreur.value = 'Le message ne doit pas dépasser 1000 caractères.'
    return
  }

  envoi.value = true
  await new Promise(r => setTimeout(r, 1200))
  envoi.value = false
  succes.value = true
  Object.assign(form, { nom: '', email: '', sujet: '', message: '' })
  setTimeout(() => { succes.value = false }, 6000)
}
</script>

<style scoped>
.contact-page { min-height: 100vh; background: #f5f8fd; }

/* ═══ EN-TÊTE ════════════════════════════════════════ */
.page-header {
  background: linear-gradient(135deg, #f0fdf9 0%, #ecfdf5 40%, #eff6ff 100%);
  border-bottom: 1px solid #e2e8f0;
  padding: 64px 28px 52px;
}
.header-inner { max-width: 600px; margin: 0 auto; text-align: center; }

.header-badge {
  display: inline-flex; align-items: center; gap: 7px;
  padding: 5px 14px; background: white; border: 1px solid #e2e8f0;
  border-radius: 999px; font-size: 12px; font-weight: 600; color: #1a6fc4;
  margin-bottom: 22px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.page-titre {
  font-size: clamp(28px, 5vw, 42px); font-weight: 900; color: #0f172a;
  margin: 0 0 14px; letter-spacing: -0.03em; line-height: 1.1;
}
.page-sous-titre { font-size: 16px; color: #64748b; line-height: 1.65; margin: 0; }

/* ═══ BODY ═══════════════════════════════════════════ */
.page-body { max-width: 1100px; margin: 0 auto; padding: 48px 28px 72px; }

/* ═══ CARTES CONTACT ══════════════════════════════════ */
.contact-cards { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 44px; }

.contact-card {
  background: white; border: 1px solid #e8edf4; border-radius: 18px;
  padding: 24px 22px; display: flex; align-items: flex-start; gap: 16px;
  transition: box-shadow 0.2s, transform 0.2s;
}
.contact-card:hover { box-shadow: 0 8px 32px rgba(15,23,42,0.1); transform: translateY(-3px); }

.cc-icon {
  width: 48px; height: 48px; border-radius: 14px; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center;
}
.email-icon    { background: #eff6ff; color: #1a6fc4; }
.whatsapp-icon { background: #dcfce7; color: #16a34a; }
.location-icon { background: #fff7ed; color: #ea580c; }

.cc-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.07em; color: #94a3b8; margin-bottom: 4px; }
.cc-value { font-size: 14.5px; font-weight: 700; color: #0f172a; text-decoration: none; display: block; transition: color 0.12s; }
a.cc-value:hover { color: #1a6fc4; }
.cc-note { font-size: 12px; color: #94a3b8; margin-top: 4px; }

/* ═══ GRILLE PRINCIPALE ═══════════════════════════════ */
.main-grid { display: grid; grid-template-columns: 1fr 360px; gap: 24px; margin-bottom: 44px; }

/* ═══ FORMULAIRE ══════════════════════════════════════ */
.form-card {
  background: white; border: 1px solid #e8edf4; border-radius: 20px; padding: 36px;
  box-shadow: 0 4px 24px rgba(15,23,42,0.05);
}

.form-header { margin-bottom: 28px; }
.form-titre { font-size: 20px; font-weight: 800; color: #0f172a; margin: 0 0 8px; }
.form-sous-titre { font-size: 13.5px; color: #64748b; margin: 0; }

.form { display: flex; flex-direction: column; gap: 20px; }

.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }

.champ { display: flex; flex-direction: column; gap: 7px; }
.champ label { font-size: 13px; font-weight: 600; color: #374151; }
.requis { color: #ef4444; }

.input-wrap { position: relative; display: flex; align-items: center; }
.input-ico { position: absolute; left: 13px; color: #94a3b8; pointer-events: none; flex-shrink: 0; }

.input-wrap input,
.input-wrap .select-field {
  width: 100%; padding: 11px 14px 11px 38px;
  border: 1.5px solid #e2e8f0; border-radius: 11px;
  font-size: 14px; color: #0f172a; outline: none;
  background: white; transition: border-color 0.15s, box-shadow 0.15s;
  font-family: inherit;
}
.input-wrap input:focus,
.input-wrap .select-field:focus {
  border-color: #1a6fc4;
  box-shadow: 0 0 0 3px rgba(26,111,196,0.1);
}
.input-wrap input::placeholder { color: #94a3b8; }

.select-field { appearance: none; cursor: pointer; }

.champ textarea {
  padding: 12px 14px; border: 1.5px solid #e2e8f0; border-radius: 11px;
  font-size: 14px; color: #0f172a; outline: none; resize: vertical;
  background: white; transition: border-color 0.15s, box-shadow 0.15s;
  font-family: inherit; min-height: 130px;
}
.champ textarea:focus { border-color: #1a6fc4; box-shadow: 0 0 0 3px rgba(26,111,196,0.1); }
.champ textarea::placeholder { color: #94a3b8; }
.champ-hint { font-size: 11.5px; color: #94a3b8; text-align: right; }

.succes-box {
  display: flex; align-items: center; gap: 10px; padding: 14px 16px;
  background: #f0fdf4; border: 1.5px solid #86efac; border-radius: 11px;
  font-size: 13.5px; color: #15803d; font-weight: 500;
}
.erreur-box {
  display: flex; align-items: center; gap: 10px; padding: 14px 16px;
  background: #fef2f2; border: 1.5px solid #fca5a5; border-radius: 11px;
  font-size: 13.5px; color: #dc2626; font-weight: 500;
}

.btn-envoyer {
  display: flex; justify-content: center; align-items: center;
  padding: 14px 28px; border: none; border-radius: 12px;
  background: linear-gradient(135deg, #1a6fc4, #3b8fd8);
  color: white; font-size: 15px; font-weight: 700; cursor: pointer;
  box-shadow: 0 4px 18px rgba(26,111,196,0.4);
  transition: all 0.2s; font-family: inherit;
}
.btn-envoyer:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 8px 28px rgba(26,111,196,0.5); }
.btn-envoyer:active:not(:disabled) { transform: translateY(0); }
.btn-envoyer:disabled { opacity: 0.7; cursor: not-allowed; }
.btn-content { display: flex; align-items: center; gap: 9px; }

@keyframes spin { to { transform: rotate(360deg); } }
.spin { animation: spin 0.9s linear infinite; }

/* ═══ COLONNE DROITE ══════════════════════════════════ */
.side-col { display: flex; flex-direction: column; gap: 16px; }

.aide-card {
  background: white; border: 1px solid #e8edf4; border-radius: 18px; padding: 26px;
  box-shadow: 0 4px 24px rgba(15,23,42,0.05);
}
.aide-icon {
  width: 48px; height: 48px; border-radius: 14px; background: #eff6ff;
  color: #1a6fc4; display: flex; align-items: center; justify-content: center; margin-bottom: 14px;
}
.aide-titre { font-size: 16px; font-weight: 800; color: #0f172a; margin: 0 0 10px; }
.aide-texte { font-size: 13px; color: #64748b; line-height: 1.65; margin: 0 0 18px; }
.aide-items { display: flex; flex-direction: column; gap: 10px; }
.aide-item { display: flex; align-items: center; gap: 9px; font-size: 13px; color: #374151; }

.response-card {
  background: linear-gradient(135deg, #0a2f5f, #0e4f91);
  border-radius: 18px; padding: 22px;
}
.rc-header { display: flex; align-items: center; gap: 8px; margin-bottom: 18px; }
.rc-dot { width: 8px; height: 8px; border-radius: 50%; background: #34d399; box-shadow: 0 0 8px rgba(52,211,153,0.7); }
.rc-status { font-size: 13px; font-weight: 600; color: rgba(255,255,255,0.9); }
.rc-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.rc-item { background: rgba(255,255,255,0.1); border-radius: 12px; padding: 14px; text-align: center; }
.rc-val { font-size: 20px; font-weight: 800; color: white; letter-spacing: -0.02em; }
.rc-lbl { font-size: 11px; color: rgba(255,255,255,0.6); margin-top: 4px; line-height: 1.3; }


/* ═══ PIED DE PAGE SECTION ═══════════════════════════ */
.footer-section {
  background: linear-gradient(135deg, #0a2f5f 0%, #0e4f91 50%, #0d9488 100%);
  border-radius: 20px; padding: 36px 32px;
}
.footer-section-inner {
  display: flex; align-items: center; justify-content: center; gap: 14px;
  color: rgba(255,255,255,0.85); font-size: 15px; font-weight: 500;
}
.footer-section-inner svg { color: #67e8f9; flex-shrink: 0; }
.footer-section-inner p { margin: 0; }

/* ═══ RESPONSIVE ═════════════════════════════════════ */
@media (max-width: 900px) {
  .main-grid { grid-template-columns: 1fr; }
  .side-col { display: grid; grid-template-columns: 1fr 1fr; }
}
@media (max-width: 700px) {
  .contact-cards { grid-template-columns: 1fr; }
  .form-row { grid-template-columns: 1fr; }
  .side-col { grid-template-columns: 1fr; }
  .page-body { padding: 32px 20px 52px; }
  .page-header { padding: 44px 20px 40px; }
  .form-card { padding: 24px 20px; }
}
</style>
