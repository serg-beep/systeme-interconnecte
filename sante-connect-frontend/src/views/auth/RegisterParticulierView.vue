<template>
  <div class="register-page">
    <div class="register-box">
      <div class="logo">
        <div class="logo-cercle">
          <img v-if="!brandLogoError" :src="brandLogoSrc" alt="" @error="brandLogoError = true" />
          <svg v-else width="28" height="28" viewBox="0 0 24 24" fill="none">
            <path d="M12 4v16M4 12h16" stroke="white" stroke-width="2.5" stroke-linecap="round"/>
          </svg>
        </div>
        <h1>Mediflow</h1>
        <p>Créer votre compte particulier</p>
      </div>

      <div class="card" v-if="etape < 2">
        <h2 class="section-titre">Vos informations</h2>
        <p class="section-sous">Suivez des établissements, likez et commentez les publications.</p>

        <div class="champ-grille">
          <div class="champ">
            <label>Nom *</label>
            <input v-model="form.nom" type="text" placeholder="Ouédraogo" :class="{ error: errors.nom }" />
            <span v-if="errors.nom" class="champ-err">{{ errors.nom }}</span>
          </div>
          <div class="champ">
            <label>Prénom *</label>
            <input v-model="form.prenom" type="text" placeholder="Awa" :class="{ error: errors.prenom }" />
            <span v-if="errors.prenom" class="champ-err">{{ errors.prenom }}</span>
          </div>
        </div>

        <div class="champ">
          <label>Email *</label>
          <input v-model="form.email" type="email" placeholder="awa@exemple.bf" :class="{ error: errors.email }" />
          <span v-if="errors.email" class="champ-err">{{ errors.email }}</span>
        </div>

        <div class="champ">
          <label>Téléphone</label>
          <input v-model="form.telephone" type="text" placeholder="+226 XX XX XX XX" />
        </div>

        <div class="champ">
          <label>Mot de passe *</label>
          <div class="input-pwd">
            <input v-model="form.password" :type="showPwd ? 'text' : 'password'"
              placeholder="Minimum 8 caractères" :class="{ error: errors.password }" />
            <button type="button" class="toggle-pwd" @click="showPwd = !showPwd">
              {{ showPwd ? 'Masquer' : 'Voir' }}
            </button>
          </div>
          <span v-if="errors.password" class="champ-err">{{ errors.password }}</span>
        </div>

        <div class="champ">
          <label>Confirmer le mot de passe *</label>
          <input v-model="form.password_confirmation" :type="showPwd ? 'text' : 'password'"
            placeholder="Répétez le mot de passe" :class="{ error: errors.password_confirmation }" />
          <span v-if="errors.password_confirmation" class="champ-err">{{ errors.password_confirmation }}</span>
        </div>

        <div v-if="erreur" class="erreur-box">{{ erreur }}</div>

        <button class="btn-inscrire" @click="inscrire" :disabled="loading">
          <span v-if="loading">Création en cours...</span>
          <span v-else>Créer mon compte</span>
        </button>

        <div class="lien-login">
          Vous représentez un établissement ?
          <router-link :to="{ name: 'register' }" class="lien">Créer un espace entreprise</router-link>
        </div>
        <div class="lien-login">
          Déjà inscrit ?
          <router-link :to="{ name: 'login' }" class="lien">Se connecter</router-link>
        </div>
      </div>

      <div class="card succes" v-else>
        <div class="succes-icone">✓</div>
        <h2>Bienvenue {{ form.prenom }} !</h2>
        <p>Votre compte a été créé avec succès.</p>
        <button class="btn-dashboard" @click="$router.push('/')">Découvrir la plateforme</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useAuthStore } from '../../stores/auth.js'
import api, { setAuthToken } from '../../api/client.js'

const auth   = useAuthStore()
const etape  = ref(1)
const loading = ref(false)
const erreur  = ref('')
const showPwd = ref(false)
const brandLogoError = ref(false)
const brandLogoSrc = '/mediflow-logo.jpeg'

const form = reactive({
    nom: '', prenom: '', email: '', telephone: '',
    password: '', password_confirmation: '',
})

const errors = reactive({
    nom: '', prenom: '', email: '', password: '', password_confirmation: '',
})

function reinitErrors() {
    Object.keys(errors).forEach(k => errors[k] = '')
}

function valider() {
    reinitErrors()
    let ok = true
    if (!form.nom)    { errors.nom    = 'Obligatoire'; ok = false }
    if (!form.prenom) { errors.prenom = 'Obligatoire'; ok = false }
    if (!form.email) {
        errors.email = 'Obligatoire'; ok = false
    } else if (!/\S+@\S+\.\S+/.test(form.email)) {
        errors.email = 'Email invalide'; ok = false
    }
    if (!form.password) {
        errors.password = 'Obligatoire'; ok = false
    } else if (form.password.length < 8) {
        errors.password = 'Minimum 8 caractères'; ok = false
    }
    if (form.password !== form.password_confirmation) {
        errors.password_confirmation = 'Les mots de passe ne correspondent pas'; ok = false
    }
    return ok
}

async function inscrire() {
    if (!valider()) return
    loading.value = true
    erreur.value  = ''
    try {
        const { data } = await api.post('/auth/register-particulier', { ...form })
        setAuthToken(data.token)
        localStorage.setItem('token', data.token)
        localStorage.setItem('user', JSON.stringify(data.user))
        auth.user  = data.user
        auth.token = data.token
        etape.value = 2
    } catch (e) {
        if (e.response?.data?.errors) {
            const errs = e.response.data.errors
            Object.keys(errs).forEach(k => {
                if (errors[k] !== undefined) errors[k] = errs[k][0]
            })
            erreur.value = 'Veuillez corriger les erreurs ci-dessus'
        } else {
            erreur.value = e.response?.data?.message || 'Erreur lors de l\'inscription'
        }
    } finally {
        loading.value = false
    }
}
</script>

<style scoped>
.register-page { min-height: 100vh; background: #f4f6f9; display: flex; align-items: center; justify-content: center; padding: 24px; }
.register-box { width: 100%; max-width: 440px; }
.logo { text-align: center; margin-bottom: 20px; }
.logo-cercle { width: 48px; height: 48px; background: linear-gradient(135deg, var(--color-primary), var(--color-accent)); border-radius: 14px; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 10px; overflow: hidden; box-shadow: 0 14px 28px rgba(15, 118, 110, 0.18); }
.logo-cercle img { width: 100%; height: 100%; object-fit: contain; padding: 5px; box-sizing: border-box; background: white; }
.logo h1 { font-size: 18px; font-weight: 700; color: #1a1a2e; margin: 0 0 4px; }
.logo p { font-size: 13px; color: #6b7280; margin: 0; }
.card { background: white; border-radius: 16px; border: 1px solid #e5e7eb; padding: 28px; }
.section-titre { font-size: 16px; font-weight: 600; color: #1a1a2e; margin: 0 0 4px; }
.section-sous  { font-size: 13px; color: #6b7280; margin: 0 0 20px; }
.champ { display: flex; flex-direction: column; gap: 5px; margin-bottom: 14px; }
.champ label { font-size: 13px; font-weight: 500; color: #374151; }
.champ input { padding: 9px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 13px; color: #1a1a2e; outline: none; font-family: inherit; transition: border-color 0.2s; width: 100%; box-sizing: border-box; }
.champ input:focus { border-color: #1D9E75; box-shadow: 0 0 0 2px rgba(29,158,117,0.1); }
.champ input.error { border-color: #dc2626; }
.champ-err { font-size: 11px; color: #dc2626; }
.champ-grille { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.input-pwd { position: relative; }
.input-pwd input { padding-right: 60px; }
.toggle-pwd { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; font-size: 12px; color: #6b7280; cursor: pointer; }
.erreur-box { background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 10px; font-size: 13px; color: #dc2626; margin-bottom: 14px; }
.btn-inscrire { width: 100%; height: 44px; background: #1D9E75; color: white; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; margin-top: 4px; }
.btn-inscrire:hover:not(:disabled) { background: #0F6E56; }
.btn-inscrire:disabled { opacity: 0.6; cursor: not-allowed; }
.lien-login { text-align: center; margin-top: 14px; font-size: 13px; color: #6b7280; }
.lien { color: #1D9E75; cursor: pointer; font-weight: 500; text-decoration: none; }
.lien:hover { text-decoration: underline; }
.succes { text-align: center; padding: 20px 0; }
.succes-icone { width: 60px; height: 60px; background: #E1F5EE; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; color: #1D9E75; margin: 0 auto 16px; font-weight: 700; }
.succes h2 { font-size: 18px; font-weight: 700; color: #1a1a2e; margin: 0 0 8px; }
.succes p { font-size: 14px; color: #374151; margin: 0 0 16px; }
.btn-dashboard { background: #1D9E75; color: white; border: none; border-radius: 8px; padding: 12px 32px; font-size: 14px; font-weight: 600; cursor: pointer; }
</style>
