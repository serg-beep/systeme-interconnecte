<template>
  <div class="login-page">
    <div class="login-box">
      <div class="logo">
        <div class="logo-cercle">
          <img v-if="!brandLogoError" :src="brandLogoSrc" alt="" @error="brandLogoError = true" />
          <svg v-else width="28" height="28" viewBox="0 0 24 24" fill="none">
            <path d="M12 4v16M4 12h16" stroke="white" stroke-width="2.5" stroke-linecap="round"/>
          </svg>
        </div>
        <h1>Mediflow</h1>
        <p>Plateforme de coordination santé</p>
      </div>

      <div class="card">
        <h2>Connexion</h2>
        <p class="sous-titre">Accédez à votre espace entreprise</p>

        <div v-if="erreur" class="erreur-box">{{ erreur }}</div>

        <div class="champ">
          <label>Email</label>
          <input v-model="form.email" type="email" placeholder="votre@email.bf" />
          <span v-if="errors.email" class="champ-erreur">{{ errors.email }}</span>
        </div>

        <div class="champ">
          <label>Mot de passe</label>
          <input v-model="form.password" type="password" placeholder="••••••••" />
          <span v-if="errors.password" class="champ-erreur">{{ errors.password }}</span>
        </div>

        <button class="btn-connexion" @click="handleLogin" :disabled="loading">
          <span v-if="loading">Connexion...</span>
          <span v-else>Se connecter</span>
        </button>
         <div class="lien-register">
             Pas encore inscrit ?
          <router-link :to="{ name: 'register-particulier' }" class="lien">
             Créer un compte
            </router-link>
          ·
          <router-link :to="{ name: 'register' }" class="lien">
             Espace entreprise
            </router-link>
        </div>

        <div class="comptes-test">
          <p class="test-titre">Comptes de test</p>
          <div class="test-grille">
            <button v-for="c in comptes" :key="c.email" class="test-btn" @click="remplir(c)">
              <span class="test-role">{{ c.role }}</span>
              <span class="test-email">{{ c.email }}</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth.js'

const router = useRouter()
const auth   = useAuthStore()

const form    = reactive({ email: '', password: '' })
const errors  = reactive({ email: '', password: '' })
const erreur  = ref('')
const loading = ref(false)
const brandLogoError = ref(false)
const brandLogoSrc = '/mediflow-logo.jpeg'

const comptes = [
    { role: 'Super Admin',  email: 'moussa@sante.bf',   password: 'password' },
    { role: 'Admin',        email: 'aicha@sante.bf',    password: 'password' },
    { role: 'Gestionnaire', email: 'ibrahim@sante.bf',  password: 'password' },
    { role: 'Opérateur',    email: 'fatimata@sante.bf', password: 'password' },
]

function remplir(compte) {
    form.email    = compte.email
    form.password = compte.password
    errors.email  = ''
    errors.password = ''
    erreur.value  = ''
}

function valider() {
    let ok = true
    errors.email = errors.password = ''
    if (!form.email) { errors.email = 'Email obligatoire'; ok = false }
    if (!form.password) { errors.password = 'Mot de passe obligatoire'; ok = false }
    return ok
}

async function handleLogin() {
    if (!valider()) return
    loading.value = true
    erreur.value  = ''
    try {
        await auth.login({ email: form.email, password: form.password })
        router.push(auth.hasRole('membre') ? { name: 'home' } : { name: 'dashboard' })
    } catch (e) {
        erreur.value = e.response?.data?.message || 'Email ou mot de passe incorrect'
    } finally {
        loading.value = false
    }
}
</script>

<style scoped>
.login-page { min-height: 100vh; background: radial-gradient(circle at top left, #dff7f2 0, transparent 340px), linear-gradient(135deg, #f8fafc 0%, #eef4f8 100%); display: flex; align-items: center; justify-content: center; padding: 24px; }
.login-box { width: 100%; max-width: 400px; }
.logo { text-align: center; margin-bottom: 24px; }
.logo-cercle { width: 52px; height: 52px; background: linear-gradient(135deg, var(--color-primary), var(--color-accent)); border-radius: 16px; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 12px; box-shadow: 0 16px 32px rgba(15, 118, 110, 0.2); overflow: hidden; }
.logo-cercle img { width: 100%; height: 100%; object-fit: contain; padding: 5px; box-sizing: border-box; background: white; }
.logo h1 { font-size: 20px; font-weight: 750; color: var(--color-text-primary); margin: 0 0 4px; }
.logo p { font-size: 13px; color: var(--color-text-secondary); margin: 0; }
.card { background: rgba(255,255,255,0.94); border-radius: 18px; border: 1px solid var(--color-border); padding: 28px; box-shadow: var(--shadow); backdrop-filter: blur(10px); }
.card h2 { font-size: 18px; font-weight: 750; color: var(--color-text-primary); margin: 0 0 4px; }
.sous-titre { font-size: 13px; color: var(--color-text-secondary); margin: 0 0 20px; }
.erreur-box { background: var(--color-danger-soft); border: 1px solid #fecaca; border-radius: 10px; padding: 10px 14px; font-size: 13px; color: var(--color-danger); margin-bottom: 16px; }
.champ { display: flex; flex-direction: column; gap: 6px; margin-bottom: 14px; }
.champ label { font-size: 13px; font-weight: 600; color: var(--color-text-secondary); }
.champ input { height: 42px; padding: 0 12px; border: 1px solid var(--color-border-strong); border-radius: 10px; font-size: 14px; color: var(--color-text-primary); outline: none; transition: border-color 0.2s, box-shadow 0.2s; }
.champ input:focus { border-color: var(--color-primary); box-shadow: 0 0 0 3px rgba(15,118,110,0.12); }
.champ-erreur { font-size: 12px; color: var(--color-danger); }
.btn-connexion { width: 100%; height: 44px; background: linear-gradient(135deg, var(--color-primary), var(--color-primary-hover)); color: white; border: none; border-radius: 10px; font-size: 15px; font-weight: 700; cursor: pointer; margin-top: 4px; transition: transform 0.2s, box-shadow 0.2s; box-shadow: 0 12px 24px rgba(15, 118, 110, 0.18); }
.btn-connexion:hover:not(:disabled) { transform: translateY(-1px); box-shadow: 0 16px 30px rgba(15, 118, 110, 0.22); }
.btn-connexion:disabled { opacity: 0.7; cursor: not-allowed; }
.comptes-test { margin-top: 24px; padding-top: 20px; border-top: 1px solid var(--color-surface-muted); }
.test-titre { font-size: 11px; color: var(--color-text-muted); text-align: center; margin: 0 0 10px; text-transform: uppercase; letter-spacing: 0.5px; }
.test-grille { display: grid; grid-template-columns: 1fr 1fr; gap: 6px; }
.test-btn { display: flex; flex-direction: column; padding: 8px 10px; background: var(--color-surface-muted); border: 1px solid var(--color-border); border-radius: 10px; cursor: pointer; transition: all 0.15s; text-align: left; }
.test-btn:hover { background: var(--color-primary-soft); border-color: var(--color-primary); }
.test-role { font-size: 11px; font-weight: 700; color: var(--color-primary); }
.test-email { font-size: 10px; color: var(--color-text-secondary); font-family: monospace; }
.lien-register { text-align: center; margin-top: 16px; font-size: 13px; color: var(--color-text-secondary); }
.lien { color: var(--color-primary); cursor: pointer; font-weight: 600; margin-left: 4px; }
.lien {
  text-decoration: none;
  transition: all 0.2s ease;
}

.lien:hover {
  text-decoration: underline;
  color: var(--color-primary-hover);
}
</style>
