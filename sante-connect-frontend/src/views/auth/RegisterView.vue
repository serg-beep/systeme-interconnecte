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
        <p>Créer votre espace entreprise</p>
      </div>

      <div class="card">
        <!-- Étapes -->
        <div class="etapes">
          <div class="etape" :class="{ active: etape >= 1, done: etape > 1 }">
            <div class="etape-num">1</div>
            <span>Entreprise</span>
          </div>
          <div class="etape-ligne"></div>
          <div class="etape" :class="{ active: etape >= 2, done: etape > 2 }">
            <div class="etape-num">2</div>
            <span>Administrateur</span>
          </div>
          <div class="etape-ligne"></div>
          <div class="etape" :class="{ active: etape >= 3 }">
            <div class="etape-num">3</div>
            <span>Confirmation</span>
          </div>
        </div>

        <!-- Étape 1 — Informations entreprise -->
        <div v-if="etape === 1">
          <h2 class="section-titre">Informations de l'entreprise</h2>

          <div class="champ">
            <label>Nom de l'entreprise *</label>
            <input v-model="form.entreprise_nom" type="text"
              placeholder="Ex: Pharmacie Centrale de Ouaga"
              :class="{ error: errors.entreprise_nom }" />
            <span v-if="errors.entreprise_nom" class="champ-err">{{ errors.entreprise_nom }}</span>
          </div>

          <div class="champ-grille">
            <div class="champ">
              <label>Type d'entreprise *</label>
              <select v-model="form.entreprise_type" :class="{ error: errors.entreprise_type }">
                <option value="">Sélectionner...</option>
                <option value="pharmacie">Pharmacie</option>
                <option value="hopital">Hôpital</option>
                <option value="laboratoire">Laboratoire</option>
                <option value="clinique">Clinique</option>
                <option value="autre">Autre</option>
              </select>
              <span v-if="errors.entreprise_type" class="champ-err">{{ errors.entreprise_type }}</span>
            </div>
            <div class="champ">
              <label>Ville *</label>
              <select v-model="form.entreprise_ville" :class="{ error: errors.entreprise_ville }">
                <option value="">Sélectionner...</option>
                <option value="Ouagadougou">Ouagadougou</option>
                <option value="Bobo-Dioulasso">Bobo-Dioulasso</option>
                <option value="Koudougou">Koudougou</option>
                <option value="Banfora">Banfora</option>
                <option value="Ouahigouya">Ouahigouya</option>
                <option value="Kaya">Kaya</option>
                <option value="Tenkodogo">Tenkodogo</option>
                <option value="Fada N'Gourma">Fada N'Gourma</option>
                <option value="Dédougou">Dédougou</option>
                <option value="Autre">Autre</option>
              </select>
              <span v-if="errors.entreprise_ville" class="champ-err">{{ errors.entreprise_ville }}</span>
            </div>
          </div>

          <div class="champ">
            <label>Email de l'entreprise *</label>
            <input v-model="form.entreprise_email" type="email"
              placeholder="contact@entreprise.bf"
              :class="{ error: errors.entreprise_email }" />
            <span v-if="errors.entreprise_email" class="champ-err">{{ errors.entreprise_email }}</span>
          </div>

          <div class="champ-grille">
            <div class="champ">
              <label>Téléphone</label>
              <input v-model="form.entreprise_telephone" type="text" placeholder="+226 XX XX XX XX" />
            </div>
            <div class="champ">
              <label>Adresse *</label>
              <input v-model="form.entreprise_adresse" type="text" placeholder="Secteur, rue..."
                :class="{ error: errors.entreprise_adresse }" />
              <span v-if="errors.entreprise_adresse" class="champ-err">{{ errors.entreprise_adresse }}</span>
            </div>
          </div>

          <div class="champ">
            <label>Description</label>
            <textarea v-model="form.entreprise_description" rows="2"
              placeholder="Décrivez brièvement votre entreprise..."></textarea>
          </div>

          <div class="champ">
            <label>Logo (optionnel)</label>
            <label class="logo-upload">
              <span v-if="logoPreview" class="logo-preview">
                <img :src="logoPreview" alt="Aperçu du logo" />
              </span>
              <span v-else class="logo-placeholder">Logo</span>
              <span class="logo-upload-text">
                <strong>Importer une image</strong>
                <small>PNG, JPG, WEBP ou SVG - 2 Mo max</small>
              </span>
              <input type="file" accept="image/png,image/jpeg,image/webp,image/svg+xml" @change="choisirLogo" />
            </label>
            <span v-if="errors.entreprise_logo" class="champ-err">{{ errors.entreprise_logo }}</span>
          </div>

          <button class="btn-suivant" @click="etapeSuivante">
            Suivant →
          </button>
        </div>

        <!-- Étape 2 — Compte administrateur -->
        <div v-if="etape === 2">
          <h2 class="section-titre">Compte administrateur</h2>
          <p class="section-sous">Ce compte aura accès complet à votre espace entreprise.</p>

          <div class="champ-grille">
            <div class="champ">
              <label>Nom *</label>
              <input v-model="form.nom" type="text" placeholder="Ouédraogo"
                :class="{ error: errors.nom }" />
              <span v-if="errors.nom" class="champ-err">{{ errors.nom }}</span>
            </div>
            <div class="champ">
              <label>Prénom *</label>
              <input v-model="form.prenom" type="text" placeholder="Moussa"
                :class="{ error: errors.prenom }" />
              <span v-if="errors.prenom" class="champ-err">{{ errors.prenom }}</span>
            </div>
          </div>

          <div class="champ">
            <label>Email *</label>
            <input v-model="form.email" type="email" placeholder="moussa@sante.bf"
              :class="{ error: errors.email }" />
            <span v-if="errors.email" class="champ-err">{{ errors.email }}</span>
          </div>

          <div class="champ">
            <label>Téléphone</label>
            <input v-model="form.telephone" type="text" placeholder="+226 XX XX XX XX" />
          </div>

          <div class="champ">
            <label>Poste / Fonction</label>
            <input v-model="form.poste" type="text" placeholder="Ex: Directeur, Pharmacien..." />
          </div>

          <div class="champ">
            <label>Rôle initial *</label>
            <select v-model="form.role" :class="{ error: errors.role }">
              <option value="admin">Administrateur</option>
              <option value="gestionnaire">Gestionnaire</option>
              <option value="operateur">Opérateur</option>
            </select>
            <small class="role-info">
              <strong>Admin:</strong> Accès complet sauf gestion entreprises et paramètres système |
              <strong>Gestionnaire:</strong> Accès limité |
              <strong>Opérateur:</strong> Lecture seule sur les contenus
            </small>
          </div>

          <div class="champ">
            <label>Mot de passe *</label>
            <div class="input-pwd">
              <input v-model="form.password" :type="showPwd ? 'text' : 'password'"
                placeholder="Minimum 8 caractères"
                :class="{ error: errors.password }" />
              <button type="button" class="toggle-pwd" @click="showPwd = !showPwd">
                {{ showPwd ? 'Masquer' : 'Voir' }}
              </button>
            </div>
            <span v-if="errors.password" class="champ-err">{{ errors.password }}</span>
          </div>

          <div class="champ">
            <label>Confirmer le mot de passe *</label>
            <input v-model="form.password_confirmation" :type="showPwd ? 'text' : 'password'"
              placeholder="Répétez le mot de passe"
              :class="{ error: errors.password_confirmation }" />
            <span v-if="errors.password_confirmation" class="champ-err">{{ errors.password_confirmation }}</span>
          </div>

          <div class="btn-groupe">
            <button class="btn-retour" @click="etape = 1">← Retour</button>
            <button class="btn-suivant" @click="etapeSuivante">Suivant →</button>
          </div>
        </div>

        <!-- Étape 3 — Confirmation -->
        <div v-if="etape === 3">
          <h2 class="section-titre">Confirmation</h2>

          <div class="recap">
            <div class="recap-section">
              <div class="recap-titre">Entreprise</div>
              <div class="recap-ligne">
                <span>Nom</span><strong>{{ form.entreprise_nom }}</strong>
              </div>
              <div class="recap-ligne">
                <span>Type</span><strong>{{ form.entreprise_type }}</strong>
              </div>
              <div class="recap-ligne">
                <span>Ville</span><strong>{{ form.entreprise_ville }}</strong>
              </div>
              <div class="recap-ligne">
                <span>Email</span><strong>{{ form.entreprise_email }}</strong>
              </div>
              <div v-if="form.entreprise_logo" class="recap-ligne">
                <span>Logo</span><strong>Fourni</strong>
              </div>
            </div>
            <div class="recap-section">
              <div class="recap-titre">Administrateur</div>
              <div class="recap-ligne">
                <span>Nom complet</span><strong>{{ form.prenom }} {{ form.nom }}</strong>
              </div>
              <div class="recap-ligne">
                <span>Email</span><strong>{{ form.email }}</strong>
              </div>
              <div class="recap-ligne">
                <span>Poste</span><strong>{{ form.poste || 'Non renseigné' }}</strong>
              </div>
              <div class="recap-ligne">
                <span>Rôle assigné</span>
                <strong class="role-badge" :class="'role-' + form.role">{{ libelle(form.role) }}</strong>
              </div>
            </div>
          </div>

          <div v-if="erreur" class="erreur-box">{{ erreur }}</div>

          <div class="btn-groupe">
            <button class="btn-retour" @click="etape = 2">← Retour</button>
            <button class="btn-inscrire" @click="inscrire" :disabled="loading">
              <span v-if="loading">Création en cours...</span>
              <span v-else>Créer mon espace</span>
            </button>
          </div>
        </div>

        <!-- Succès -->
        <div v-if="etape === 4" class="succes">
          <div class="succes-icone">✓</div>
          <h2>Inscription réussie !</h2>
          <p>Votre espace <strong>{{ form.entreprise_nom }}</strong> a été créé.</p>
          <p class="succes-sous">Vous êtes connecté en tant qu'administrateur.</p>
          <button class="btn-dashboard" @click="allerDashboard">
            Accéder au tableau de bord
          </button>
        </div>

        <div v-if="etape < 4" class="lien-login">
          Vous êtes un particulier ?
          <a @click="$router.push('/register-particulier')" class="lien">Créer un compte simple</a>
        </div>
        <div v-if="etape < 4" class="lien-login">
          Déjà inscrit ?
          <a @click="$router.push('/login')" class="lien">Se connecter</a>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth.js'
import api, { setAuthToken } from '../../api/client.js'

const router  = useRouter()
const auth    = useAuthStore()
const etape   = ref(1)
const loading = ref(false)
const erreur  = ref('')
const showPwd = ref(false)
const logoPreview = ref('')
const brandLogoError = ref(false)
const brandLogoSrc = '/mediflow-logo.jpeg'

const form = reactive({
    entreprise_nom:         '',
    entreprise_type:        '',
    entreprise_ville:       '',
    entreprise_email:       '',
    entreprise_telephone:   '',
    entreprise_adresse:     '',
    entreprise_description: '',
    entreprise_logo:        null,
    role:                   'admin',
    nom:                    '',
    prenom:                 '',
    email:                  '',
    password:               '',
    password_confirmation:  '',
    telephone:              '',
    poste:                  '',
})

const errors = reactive({
    entreprise_nom: '', entreprise_type: '', entreprise_ville: '',
    entreprise_email: '', entreprise_adresse: '', entreprise_logo: '', nom: '', prenom: '', email: '',
    password: '', password_confirmation: '',
})

function reinitErrors() {
    Object.keys(errors).forEach(k => errors[k] = '')
}

function validerEtape1() {
    reinitErrors()
    let ok = true
    if (!form.entreprise_nom)   { errors.entreprise_nom   = 'Obligatoire'; ok = false }
    if (!form.entreprise_type)  { errors.entreprise_type  = 'Obligatoire'; ok = false }
    if (!form.entreprise_ville) { errors.entreprise_ville = 'Obligatoire'; ok = false }
    if (!form.entreprise_email) {
        errors.entreprise_email = 'Obligatoire'; ok = false
    } else if (!/\S+@\S+\.\S+/.test(form.entreprise_email)) {
        errors.entreprise_email = 'Email invalide'; ok = false
    }
    if (!form.entreprise_adresse) {
        errors.entreprise_adresse = 'Obligatoire'; ok = false
    }
    return ok
}

function validerEtape2() {
    reinitErrors()
    let ok = true
    if (!form.nom)    { errors.nom    = 'Obligatoire'; ok = false }
    if (!form.prenom) { errors.prenom = 'Obligatoire'; ok = false }
    if (!form.email)  {
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

function etapeSuivante() {
    if (etape.value === 1 && validerEtape1()) etape.value = 2
    else if (etape.value === 2 && validerEtape2()) etape.value = 3
}

async function inscrire() {
    loading.value = true
    erreur.value  = ''
    try {
        const payload = new FormData()
        payload.append('entreprise_nom', form.entreprise_nom)
        payload.append('entreprise_type', form.entreprise_type)
        payload.append('entreprise_email', form.entreprise_email)
        payload.append('entreprise_ville', form.entreprise_ville)
        payload.append('entreprise_telephone', form.entreprise_telephone || '')
        payload.append('entreprise_adresse', form.entreprise_adresse)
        payload.append('entreprise_description', form.entreprise_description || '')
        if (form.entreprise_logo) payload.append('entreprise_logo', form.entreprise_logo)
        payload.append('role', form.role)
        payload.append('nom', form.nom)
        payload.append('prenom', form.prenom)
        payload.append('email', form.email)
        payload.append('password', form.password)
        payload.append('password_confirmation', form.password_confirmation)
        payload.append('telephone', form.telephone || '')
        payload.append('poste', form.poste || '')

        const { data } = await api.post('/auth/register', payload, {
            headers: { 'Content-Type': 'multipart/form-data' },
        })
        // Sauvegarder le token
        localStorage.setItem('token', data.token)
        localStorage.setItem('user', JSON.stringify(data.user))
        setAuthToken(data.token)
        auth.user  = data.user
        auth.token = data.token
        etape.value = 4
    } catch (e) {
        if (e.response?.data?.errors) {
            const errs = e.response.data.errors
            Object.keys(errs).forEach(k => {
                if (errors[k] !== undefined) errors[k] = errs[k][0]
            })
            erreur.value = 'Veuillez corriger les erreurs ci-dessus'
            etape.value = (
                errs.entreprise_nom ||
                errs.entreprise_type ||
                errs.entreprise_ville ||
                errs.entreprise_email ||
                errs.entreprise_adresse
            ) ? 1 : 2
        } else {
            erreur.value = e.response?.data?.message || 'Erreur lors de l\'inscription'
        }
    } finally {
        loading.value = false
    }
}

function choisirLogo(event) {
    const fichier = event.target.files?.[0]
    errors.entreprise_logo = ''

    if (!fichier) {
        form.entreprise_logo = null
        logoPreview.value = ''
        return
    }

    if (!fichier.type.startsWith('image/')) {
        errors.entreprise_logo = 'Veuillez choisir une image'
        event.target.value = ''
        return
    }

    if (fichier.size > 2 * 1024 * 1024) {
        errors.entreprise_logo = 'Logo trop lourd, maximum 2 Mo'
        event.target.value = ''
        return
    }

    form.entreprise_logo = fichier
    if (logoPreview.value) URL.revokeObjectURL(logoPreview.value)
    logoPreview.value = URL.createObjectURL(fichier)
}

function allerDashboard() {
    router.push('/dashboard')
}

function libelle(valeur) {
    const labels = {
        'admin': 'Administrateur',
        'gestionnaire': 'Gestionnaire',
        'operateur': 'Opérateur',
    }
    return labels[valeur] || valeur
}
</script>

<style scoped>
.register-page { min-height: 100vh; background: #f4f6f9; display: flex; align-items: center; justify-content: center; padding: 24px; }
.register-box { width: 100%; max-width: 540px; }
.logo { text-align: center; margin-bottom: 20px; }
.logo-cercle { width: 48px; height: 48px; background: linear-gradient(135deg, var(--color-primary), var(--color-accent)); border-radius: 14px; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 10px; overflow: hidden; box-shadow: 0 14px 28px rgba(15, 118, 110, 0.18); }
.logo-cercle img { width: 100%; height: 100%; object-fit: contain; padding: 5px; box-sizing: border-box; background: white; }
.logo h1 { font-size: 18px; font-weight: 700; color: #1a1a2e; margin: 0 0 4px; }
.logo p { font-size: 13px; color: #6b7280; margin: 0; }
.card { background: white; border-radius: 16px; border: 1px solid #e5e7eb; padding: 28px; }
.etapes { display: flex; align-items: center; justify-content: center; margin-bottom: 28px; gap: 0; }
.etape { display: flex; flex-direction: column; align-items: center; gap: 4px; }
.etape-num { width: 28px; height: 28px; border-radius: 50%; background: #f3f4f6; color: #9ca3af; font-size: 12px; font-weight: 600; display: flex; align-items: center; justify-content: center; transition: all 0.2s; }
.etape.active .etape-num { background: #1D9E75; color: white; }
.etape.done .etape-num { background: #085041; color: white; }
.etape span { font-size: 11px; color: #9ca3af; }
.etape.active span { color: #1D9E75; font-weight: 500; }
.etape-ligne { flex: 1; height: 1px; background: #e5e7eb; margin: 0 8px; margin-bottom: 16px; min-width: 40px; }
.section-titre { font-size: 16px; font-weight: 600; color: #1a1a2e; margin: 0 0 4px; }
.section-sous  { font-size: 13px; color: #6b7280; margin: 0 0 20px; }
.champ { display: flex; flex-direction: column; gap: 5px; margin-bottom: 14px; }
.champ label { font-size: 13px; font-weight: 500; color: #374151; }
.champ input, .champ select, .champ textarea { padding: 9px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 13px; color: #1a1a2e; outline: none; font-family: inherit; transition: border-color 0.2s; }
.champ input:focus, .champ select:focus, .champ textarea:focus { border-color: #1D9E75; box-shadow: 0 0 0 2px rgba(29,158,117,0.1); }
.champ input.error, .champ select.error { border-color: #dc2626; }
.champ-err { font-size: 11px; color: #dc2626; }
.logo-upload { display: flex; align-items: center; gap: 12px; padding: 12px; border: 1px dashed var(--color-border-strong); border-radius: 12px; background: var(--color-surface-muted); cursor: pointer; transition: border-color 0.2s, background 0.2s; }
.logo-upload:hover { border-color: var(--color-primary); background: var(--color-primary-soft); }
.logo-upload input { display: none; }
.logo-preview, .logo-placeholder { width: 54px; height: 54px; border-radius: 12px; background: white; border: 1px solid var(--color-border); display: flex; align-items: center; justify-content: center; flex-shrink: 0; overflow: hidden; color: var(--color-primary); font-size: 12px; font-weight: 700; }
.logo-preview img { width: 100%; height: 100%; object-fit: contain; padding: 6px; box-sizing: border-box; }
.logo-upload-text { display: flex; flex-direction: column; gap: 2px; color: var(--color-text-secondary); }
.logo-upload-text strong { color: var(--color-text-primary); font-size: 13px; }
.champ-grille { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.input-pwd { position: relative; }
.input-pwd input { width: 100%; padding-right: 60px; }
.toggle-pwd { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; font-size: 12px; color: #6b7280; cursor: pointer; }
.btn-suivant { width: 100%; height: 42px; background: #1D9E75; color: white; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; margin-top: 8px; transition: background 0.2s; }
.btn-suivant:hover { background: #0F6E56; }
.btn-groupe { display: flex; gap: 10px; margin-top: 8px; }
.btn-retour { flex: 1; height: 42px; background: white; color: #6b7280; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px; cursor: pointer; }
.btn-inscrire { flex: 2; height: 42px; background: #1D9E75; color: white; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; }
.btn-inscrire:disabled { opacity: 0.6; cursor: not-allowed; }
.recap { display: flex; flex-direction: column; gap: 12px; margin-bottom: 20px; }
.recap-section { background: #f9fafb; border-radius: 10px; padding: 14px; }
.recap-titre { font-size: 12px; font-weight: 600; color: #1D9E75; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 10px; }
.recap-ligne { display: flex; justify-content: space-between; align-items: center; padding: 4px 0; font-size: 13px; border-bottom: 1px solid #f3f4f6; }
.recap-ligne:last-child { border-bottom: none; }
.recap-ligne span { color: #6b7280; }
.recap-ligne strong { color: #1a1a2e; text-transform: capitalize; }
.role-admin { color: #1D9E75; }
.role-info { font-size: 11px; color: #6b7280; display: block; margin-top: 4px; line-height: 1.4; }
.role-badge { text-transform: capitalize; padding: 2px 8px; border-radius: 4px; font-size: 12px; }
.role-badge.role-admin { background: #E1F5EE; color: #085041; }
.role-badge.role-gestionnaire { background: #FEF3C7; color: #92400E; }
.role-badge.role-operateur { background: #DBEAFE; color: #0C2D6B; }
small { font-size: 12px; color: #9ca3af; display: block; margin-top: 4px; }
.erreur-box { background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 10px; font-size: 13px; color: #dc2626; margin-bottom: 14px; }
.succes { text-align: center; padding: 20px 0; }
.succes-icone { width: 60px; height: 60px; background: #E1F5EE; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; color: #1D9E75; margin: 0 auto 16px; font-weight: 700; }
.succes h2 { font-size: 18px; font-weight: 700; color: #1a1a2e; margin: 0 0 8px; }
.succes p { font-size: 14px; color: #374151; margin: 0 0 4px; }
.succes-sous { font-size: 12px; color: #9ca3af; margin-bottom: 20px !important; }
.btn-dashboard { background: #1D9E75; color: white; border: none; border-radius: 8px; padding: 12px 32px; font-size: 14px; font-weight: 600; cursor: pointer; }
.lien-login { text-align: center; margin-top: 20px; font-size: 13px; color: #6b7280; }
.lien { color: #1D9E75; cursor: pointer; font-weight: 500; margin-left: 4px; }
</style>
