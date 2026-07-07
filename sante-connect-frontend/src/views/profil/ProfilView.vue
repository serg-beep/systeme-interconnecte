<template>
  <div>
    <h2 class="titre">Mon profil</h2>

    <div class="deux-col">
      <!-- Profil utilisateur -->
      <div class="section">
        <div class="section-titre">Informations personnelles</div>

        <div class="avatar-section">
          <div class="avatar">{{ initiales }}</div>
          <div>
            <div class="avatar-nom">{{ auth.user?.prenom }} {{ auth.user?.nom }}</div>
            <div class="avatar-role">{{ roleLabel }}</div>
            <div class="avatar-ent">{{ auth.entreprise?.nom }}</div>
          </div>
        </div>

        <div class="champ-grille">
          <div class="champ">
            <label>Nom</label>
            <input v-model="formUser.nom" type="text" />
          </div>
          <div class="champ">
            <label>Prénom</label>
            <input v-model="formUser.prenom" type="text" />
          </div>
        </div>

        <div class="champ">
          <label>Email</label>
          <input v-model="formUser.email" type="email" />
        </div>

        <div class="champ">
          <label>Téléphone</label>
          <input v-model="formUser.telephone" type="text" placeholder="+226 XX XX XX XX" />
        </div>

        <div class="champ">
          <label>Poste / Fonction</label>
          <input v-model="formUser.poste" type="text" placeholder="Ex: Directeur, Pharmacien..." />
        </div>

        <div class="champ">
          <label>Bio</label>
          <textarea v-model="formUser.bio" rows="3" placeholder="Quelques mots sur vous..."></textarea>
        </div>

        <div v-if="succesUser" class="succes-box">✓ Profil mis à jour avec succès</div>
        <div v-if="erreurUser" class="erreur-box">{{ erreurUser }}</div>

        <button class="btn-primary" @click="sauvegarderProfil" :disabled="envoiUser">
          {{ envoiUser ? 'Sauvegarde...' : 'Sauvegarder' }}
        </button>
      </div>

      <div class="colonne-droite">
        <!-- Changer mot de passe -->
        <div class="section">
          <div class="section-titre">Changer le mot de passe</div>

          <div class="champ">
            <label>Nouveau mot de passe</label>
            <input v-model="formPwd.password" type="password" placeholder="Minimum 8 caractères" />
          </div>
          <div class="champ">
            <label>Confirmer</label>
            <input v-model="formPwd.password_confirmation" type="password" placeholder="Répéter le mot de passe" />
          </div>

          <div v-if="succesPwd" class="succes-box">✓ Mot de passe modifié</div>
          <div v-if="erreurPwd" class="erreur-box">{{ erreurPwd }}</div>

          <button class="btn-secondary" @click="changerMotDePasse" :disabled="envoiPwd">
            {{ envoiPwd ? 'Modification...' : 'Changer le mot de passe' }}
          </button>
        </div>

        <!-- Infos entreprise -->
        <div class="section">
          <div class="section-titre">Mon entreprise</div>

          <div class="entreprise-card">
            <div class="ent-avatar">{{ entInitiales }}</div>
            <div class="ent-info">
              <div class="ent-nom">{{ auth.entreprise?.nom }}</div>
              <div class="ent-type">{{ auth.entreprise?.type }}</div>
              <div class="ent-ville">{{ auth.entreprise?.ville }}</div>
              <div class="ent-email">{{ auth.entreprise?.email }}</div>
            </div>
          </div>

          <div class="ent-statut" :class="auth.entreprise?.statut">
            Statut : {{ auth.entreprise?.statut }}
          </div>
        </div>

        <!-- Mes rôles -->
        <div class="section">
          <div class="section-titre">Mes rôles et permissions</div>
          <div class="roles-liste">
            <div v-for="role in auth.roles" :key="role.id" class="role-item">
              <div class="role-nom">{{ role.nom.replace('_', ' ') }}</div>
              <div class="role-desc">{{ role.description }}</div>
            </div>
          </div>
        </div>

        <div v-if="!estGestionnaire()" class="section">
          <div class="section-titre">Ajout d’utilisateur</div>
          <div class="erreur-box">Seuls les utilisateurs avec le rôle <strong>admin</strong>, <strong>super_admin</strong> ou <strong>gestionnaire</strong> peuvent ajouter un nouvel utilisateur.</div>
        </div>

        <div v-else class="section">
          <div class="section-titre">Ajouter un utilisateur</div>
          <div class="champ-grille">
            <div class="champ">
              <label>Nom *</label>
              <input v-model="formNewUser.nom" type="text" />
            </div>
            <div class="champ">
              <label>Prénom *</label>
              <input v-model="formNewUser.prenom" type="text" />
            </div>
          </div>
          <div class="champ">
            <label>Email *</label>
            <input v-model="formNewUser.email" type="email" />
          </div>
          <div class="champ">
            <label>Mot de passe *</label>
            <input v-model="formNewUser.password" type="password" />
          </div>
          <div class="champ">
            <label>Confirmer le mot de passe *</label>
            <input v-model="formNewUser.password_confirmation" type="password" />
          </div>
          <div class="champ">
            <label>Rôle *</label>
            <select v-model="formNewUser.role_id">
              <option value="" disabled>Choisir un rôle</option>
              <option v-for="role in roles" :key="role.id" :value="role.id">
                {{ role.nom.replace('_', ' ') }}
              </option>
            </select>
          </div>
          <div class="champ">
            <label>Téléphone</label>
            <input v-model="formNewUser.telephone" type="text" placeholder="+226 XX XX XX XX" />
          </div>
          <div class="champ">
            <label>Poste / Fonction</label>
            <input v-model="formNewUser.poste" type="text" placeholder="Ex: Pharmacien" />
          </div>
          <div v-if="succesNew" class="succes-box">✓ Utilisateur ajouté avec succès</div>
          <div v-if="erreurNew" class="erreur-box">{{ erreurNew }}</div>
          <button class="btn-primary" @click="ajouterUtilisateur" :disabled="envoiNew">
            {{ envoiNew ? 'Ajout...' : 'Ajouter l’utilisateur' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '../../stores/auth.js'
import { usePermissions } from '../../composables/usePermissions.js'
import { useReferenceData } from '../../composables/useReferenceData.js'
import api from '../../api/client.js'

const auth = useAuthStore()
const { estGestionnaire } = usePermissions()
const { roles, loadRoles } = useReferenceData()

const envoiUser  = ref(false)
const envoiPwd   = ref(false)
const succesUser = ref(false)
const succesPwd  = ref(false)
const erreurUser = ref('')
const erreurPwd  = ref('')
const envoiNew  = ref(false)
const succesNew = ref(false)
const erreurNew = ref('')

const formUser = ref({
    nom:       auth.user?.nom       || '',
    prenom:    auth.user?.prenom    || '',
    email:     auth.user?.email     || '',
    telephone: auth.user?.telephone || '',
    poste:     auth.user?.poste     || '',
    bio:       auth.user?.bio       || '',
})

const formPwd = ref({
    password:              '',
    password_confirmation: '',
})

const formNewUser = ref({
    nom:                   '',
    prenom:                '',
    email:                 '',
    password:              '',
    password_confirmation: '',
    telephone:             '',
    poste:                 '',
    role_id:               '',
})

const initiales = computed(() => {
    const u = auth.user
    if (!u) return '?'
    return ((u.prenom?.[0] || '') + (u.nom?.[0] || '')).toUpperCase()
})

const entInitiales = computed(() => {
    const nom = auth.entreprise?.nom || ''
    return nom.split(' ').map(w => w[0]).join('').substring(0, 2).toUpperCase()
})

const roleLabel = computed(() => {
    const roles = auth.roles
    if (!roles?.length) return ''
    return roles[0].nom.replace('_', ' ')
})

async function sauvegarderProfil() {
    erreurUser.value  = ''
    succesUser.value  = false
    envoiUser.value   = true
    try {
        const { data } = await api.put(`/users/${auth.user.id}`, formUser.value)
        auth.user = { ...auth.user, ...data }
        localStorage.setItem('user', JSON.stringify(auth.user))
        succesUser.value = true
        setTimeout(() => succesUser.value = false, 3000)
    } catch (e) {
        erreurUser.value = e.response?.data?.message || 'Erreur lors de la sauvegarde'
    } finally { envoiUser.value = false }
}

async function changerMotDePasse() {
    erreurPwd.value = ''
    succesPwd.value = false
    if (!formPwd.value.password) { erreurPwd.value = 'Mot de passe obligatoire'; return }
    if (formPwd.value.password !== formPwd.value.password_confirmation) {
        erreurPwd.value = 'Les mots de passe ne correspondent pas'; return
    }
    if (formPwd.value.password.length < 8) {
        erreurPwd.value = 'Minimum 8 caractères'; return
    }
    envoiPwd.value = true
    try {
        await api.put(`/users/${auth.user.id}`, { password: formPwd.value.password })
        formPwd.value = { password: '', password_confirmation: '' }
        succesPwd.value = true
        setTimeout(() => succesPwd.value = false, 3000)
    } catch (e) {
        erreurPwd.value = e.response?.data?.message || 'Erreur'
    } finally { envoiPwd.value = false }
}

async function chargerRoles() {
    try {
        await loadRoles()
    } catch (e) {
        window.dispatchEvent(new CustomEvent('app-error', { detail: e?.response?.data?.message || 'Une erreur est survenue' }))
    }
}

function reinitialiserFormNewUser() {
    formNewUser.value = {
        nom:                   '',
        prenom:                '',
        email:                 '',
        password:              '',
        password_confirmation: '',
        telephone:             '',
        poste:                 '',
        role_id:               '',
    }
}

async function ajouterUtilisateur() {
    erreurNew.value = ''
    succesNew.value = false
    if (!formNewUser.value.nom || !formNewUser.value.prenom || !formNewUser.value.email || !formNewUser.value.password || !formNewUser.value.password_confirmation || !formNewUser.value.role_id) {
        erreurNew.value = 'Tous les champs obligatoires doivent être remplis'
        return
    }
    if (formNewUser.value.password !== formNewUser.value.password_confirmation) {
        erreurNew.value = 'Les mots de passe ne correspondent pas'
        return
    }
    if (formNewUser.value.password.length < 8) {
        erreurNew.value = 'Le mot de passe doit contenir au moins 8 caractères'
        return
    }
    envoiNew.value = true
    try {
        await api.post('/users', formNewUser.value)
        succesNew.value = true
        reinitialiserFormNewUser()
        setTimeout(() => succesNew.value = false, 3000)
    } catch (e) {
        const response = e.response?.data
        erreurNew.value = response?.errors
            ? Object.values(response.errors).flat()[0]
            : response?.message || 'Erreur lors de la création'
    } finally {
        envoiNew.value = false
    }
}

onMounted(() => {
    chargerRoles()
})
</script>

<style scoped>
.titre { font-size: 18px; font-weight: 600; color: var(--color-text-primary); margin: 0 0 20px; }
.deux-col { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; align-items: start; }
.colonne-droite { display: flex; flex-direction: column; gap: 16px; }
.section { background: white; border-radius: 12px; border: 1px solid #e5e7eb; padding: 20px; }
.section-titre { font-size: 14px; font-weight: 600; color: var(--color-text-primary); margin-bottom: 16px; padding-bottom: 10px; border-bottom: 1px solid #f3f4f6; }
.avatar-section { display: flex; align-items: center; gap: 14px; margin-bottom: 20px; padding: 14px; background: #f9fafb; border-radius: 10px; }
.avatar { width: 52px; height: 52px; border-radius: 50%; background: #1D9E75; color: white; display: flex; align-items: center; justify-content: center; font-size: 18px; font-weight: 600; flex-shrink: 0; }
.avatar-nom  { font-size: 14px; font-weight: 600; color: var(--color-text-primary); }
.avatar-role { font-size: 12px; color: #1D9E75; text-transform: capitalize; margin-top: 2px; }
.avatar-ent  { font-size: 12px; color: #9ca3af; margin-top: 1px; }
.champ { display: flex; flex-direction: column; gap: 6px; margin-bottom: 14px; }
.champ label { font-size: 13px; font-weight: 500; color: #374151; }
.champ input, .champ textarea { padding: 9px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 13px; color: var(--color-text-primary); outline: none; font-family: inherit; }
.champ input:focus, .champ textarea:focus { border-color: #1D9E75; }
.champ-grille { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.succes-box { background: #E1F5EE; border: 1px solid #9FE1CB; border-radius: 8px; padding: 10px; font-size: 13px; color: #085041; margin-bottom: 14px; }
.erreur-box { background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 10px; font-size: 13px; color: #dc2626; margin-bottom: 14px; }
.btn-primary { width: 100%; height: 40px; background: #1D9E75; color: white; border: none; border-radius: 8px; font-size: 13px; font-weight: 500; cursor: pointer; }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-secondary { width: 100%; height: 40px; background: white; color: #374151; border: 1px solid #d1d5db; border-radius: 8px; font-size: 13px; cursor: pointer; }
.btn-secondary:hover { background: #f3f4f6; }
.entreprise-card { display: flex; gap: 12px; align-items: flex-start; padding: 12px; background: #f9fafb; border-radius: 10px; margin-bottom: 12px; }
.ent-avatar { width: 44px; height: 44px; border-radius: 10px; background: #1D9E75; color: white; display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 600; flex-shrink: 0; }
.ent-nom   { font-size: 13px; font-weight: 600; color: var(--color-text-primary); }
.ent-type  { font-size: 12px; color: #1D9E75; text-transform: capitalize; margin-top: 2px; }
.ent-ville { font-size: 12px; color: #6b7280; margin-top: 2px; }
.ent-email { font-size: 11px; color: #9ca3af; margin-top: 2px; font-family: monospace; }
.ent-statut { font-size: 12px; font-weight: 500; padding: 4px 10px; border-radius: 8px; display: inline-block; text-transform: capitalize; }
.ent-statut.actif    { background: #E1F5EE; color: #085041; }
.ent-statut.inactif  { background: #f3f4f6; color: #6b7280; }
.ent-statut.suspendu { background: #FCEBEB; color: #791F1F; }
.roles-liste { display: flex; flex-direction: column; gap: 8px; }
.role-item { padding: 10px 12px; background: #f9fafb; border-radius: 8px; }
.role-nom  { font-size: 13px; font-weight: 500; color: var(--color-text-primary); text-transform: capitalize; }
.role-desc { font-size: 12px; color: #6b7280; margin-top: 2px; }
</style>
