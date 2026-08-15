<template>
  <div>
    <div class="page-header">
      <h2 class="titre">Gestion des utilisateurs</h2>
      <button class="btn-primary" @click="ouvrirModal()">Ajouter utilisateur</button>
    </div>

    <div class="stats-mini">
      <div class="stat-mini">
        <div class="stat-num">{{ stats.total }}</div>
        <div class="stat-label">Total utilisateurs</div>
      </div>
      <div class="stat-mini">
        <div class="stat-num success">{{ stats.actifs }}</div>
        <div class="stat-label">Actifs</div>
      </div>
      <div class="stat-mini">
        <div class="stat-num danger">{{ stats.inactifs }}</div>
        <div class="stat-label">Desactives</div>
      </div>
    </div>

    <div class="section">
      <div class="recherche-bar">
        <input v-model="recherche" type="text" placeholder="Rechercher un utilisateur..." class="recherche-input" />
      </div>

      <div v-if="chargement" class="vide">Chargement...</div>
      <div v-else-if="users.length === 0" class="vide">Aucun utilisateur</div>
      <template v-else>
        <table class="table">
          <thead>
            <tr>
              <th>Utilisateur</th>
              <th>Email</th>
              <th>Poste</th>
              <th>Rôle</th>
              <th>Statut</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="u in users" :key="u.id">
              <td data-label="Utilisateur">
                <div class="user-cell">
                  <div class="user-av">{{ initiales(u) }}</div>
                  <div>
                    <div class="user-nom">{{ u.prenom }} {{ u.nom }}</div>
                    <div class="user-tel">{{ u.telephone || '-' }}</div>
                  </div>
                </div>
              </td>
              <td data-label="Email" class="email">{{ u.email }}</td>
              <td data-label="Poste" class="poste">{{ u.poste || '-' }}</td>
              <td data-label="Rôle">
                <select class="role-select" :value="u.roles?.[0]?.id || ''" @change="changerRole(u.id, $event.target.value)">
                  <option value="">Aucun role</option>
                  <option v-for="r in roles" :key="r.id" :value="r.id">{{ libelle(r.nom) }}</option>
                </select>
              </td>
              <td data-label="Statut">
                <span class="badge-actif" :class="u.actif ? 'actif' : 'inactif'">
                  {{ u.actif ? 'Actif' : 'Inactif' }}
                </span>
              </td>
              <td data-label="Actions">
                <div class="actions-cell">
                  <button class="btn-modifier" @click="ouvrirModal(u)">Modifier</button>
                  <button class="btn-toggle" :class="u.actif ? 'desactiver' : 'activer'" @click="toggleActif(u)">
                    {{ u.actif ? 'Desactiver' : 'Activer' }}
                  </button>
                  <button v-if="auth.hasRole('super_admin')" class="btn-toggle" :class="u.verifie ? 'desactiver' : 'activer'" @click="toggleVerifie(u)">
                    {{ u.verifie ? 'Retirer badge' : 'Vérifier' }}
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
        <UiPagination :meta="meta" @change="changerPage" />
      </template>
    </div>

    <div v-if="showModal" class="modal-overlay" @click.self="fermerModal">
      <div class="modal">
        <h3 class="modal-titre">{{ userEdit ? 'Modifier l utilisateur' : 'Ajouter un utilisateur' }}</h3>

        <div class="champ-grille">
          <div class="champ">
            <label>Nom *</label>
            <input v-model="form.nom" type="text" placeholder="Ouedraogo" />
          </div>
          <div class="champ">
            <label>Prenom *</label>
            <input v-model="form.prenom" type="text" placeholder="Moussa" />
          </div>
        </div>

        <div class="champ">
          <label>Email *</label>
          <input v-model="form.email" type="email" placeholder="moussa@sante.bf" />
        </div>

        <div class="champ-grille">
          <div class="champ">
            <label>Telephone</label>
            <input v-model="form.telephone" type="text" placeholder="+226 XX XX XX XX" />
          </div>
          <div class="champ">
            <label>Poste / Fonction</label>
            <input v-model="form.poste" type="text" placeholder="Pharmacien" />
          </div>
        </div>

        <div class="champ" v-if="!userEdit">
          <label>Mot de passe *</label>
          <input v-model="form.password" type="password" placeholder="Minimum 8 caracteres" />
        </div>

        <div class="bloc-choix">
          <div class="bloc-head">
            <div>
              <h4>Rôle *</h4>
              <p>Chaque utilisateur possède un seul rôle.</p>
            </div>
          </div>
          <div class="checks role-grid">
            <label v-for="r in roles" :key="r.id" class="check-card">
              <input v-model="form.role_id" type="radio" name="role" :value="r.id" />
              <span>
                <strong>{{ libelle(r.nom) }}</strong>
                <small>{{ r.description || 'Aucune description' }}</small>
              </span>
            </label>
          </div>
        </div>

        <div class="bloc-choix" v-if="estSuperAdmin">
          <div class="bloc-head">
            <div>
              <h4>Permissions directes</h4>
              <p>Ajoutez des droits precis pour cet utilisateur.</p>
            </div>
            <div class="head-actions">
              <button class="btn-lien" type="button" @click="toutPermission(true)">Tout</button>
              <button class="btn-lien" type="button" @click="toutPermission(false)">Vider</button>
            </div>
          </div>
          <div class="checks permission-grid">
            <label v-for="p in permissionsDisponibles" :key="p.id" class="check-line">
              <input v-model="form.permissions" type="checkbox" :value="p.id" />
              <span>{{ libelle(p.nom) }}</span>
            </label>
          </div>
        </div>

        <div class="apercu">
          <strong>Permissions effectives</strong>
          <div class="chips" v-if="permissionsEffectives.length">
            <span v-for="p in permissionsEffectives" :key="p.id" class="chip">{{ libelle(p.nom) }}</span>
          </div>
          <p v-else>Aucune permission selectionnee.</p>
        </div>

        <div v-if="erreur" class="erreur-box">{{ erreur }}</div>

        <div class="modal-actions">
          <button class="btn-annuler" @click="fermerModal">Annuler</button>
          <button class="btn-primary" @click="sauvegarder" :disabled="envoi">
            {{ envoi ? 'Sauvegarde...' : userEdit ? 'Modifier' : 'Creer' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import api from '../../api/client.js'
import { useAuthStore } from '../../stores/auth.js'
import { useReferenceData } from '../../composables/useReferenceData.js'
import { useDebouncedRef } from '../../composables/useDebouncedRef.js'
import UiPagination from '../../components/ui/UiPagination.vue'

const auth = useAuthStore()
const { roles, permissions, loadAdminReferences } = useReferenceData()
const chargement = ref(true)
const users = ref([])
const meta = ref(null)
const page = ref(1)
const stats = ref({ total: 0, actifs: 0, inactifs: 0 })
const showModal = ref(false)
const envoi = ref(false)
const erreur = ref('')
const userEdit = ref(null)
const recherche = ref('')
const rechercheDebounced = useDebouncedRef(recherche)

const form = ref(formVide())
const estSuperAdmin = computed(() => auth.hasRole('super_admin'))
const permissionsDisponibles = computed(() =>
    permissions.value.filter(p => !['consulter_audit_tracabilite'].includes(p.nom))
)

const permissionsEffectives = computed(() => {
    const ids = new Set(form.value.permissions.map(Number))
    const roleIds = new Set([Number(form.value.role_id)])

    roles.value
        .filter(r => roleIds.has(Number(r.id)))
        .forEach(r => (r.permissions || []).forEach(p => ids.add(Number(p.id))))

    return permissionsDisponibles.value.filter(p => ids.has(Number(p.id)))
})

function formVide() {
    return { nom: '', prenom: '', email: '', telephone: '', poste: '', password: '', role_id: '', permissions: [] }
}

function libelle(valeur) {
    return (valeur || '').replaceAll('_', ' ')
}

function initiales(u) {
    return `${u.prenom?.[0] || ''}${u.nom?.[0] || ''}`.toUpperCase()
}

function ouvrirModal(u = null) {
    userEdit.value = u
    form.value = u ? {
        nom: u.nom || '',
        prenom: u.prenom || '',
        email: u.email || '',
        telephone: u.telephone || '',
        poste: u.poste || '',
        password: '',
        role_id: u.roles?.[0]?.id || '',
        permissions: (u.permissions || []).map(p => p.id),
    } : formVide()
    erreur.value = ''
    showModal.value = true
}

function fermerModal() {
    showModal.value = false
    userEdit.value = null
}

async function chargerStats() {
    try {
        const { data } = await api.get('/users/stats')
        stats.value = data
    } catch { /* stats non bloquantes */ }
}

async function chargerUsers() {
    const { data } = await api.get('/users', {
        params: { paginate: 1, page: page.value, per_page: 20, q: rechercheDebounced.value || undefined },
    })
    users.value = data.data ?? data
    meta.value = data.data ? data : null
}

async function charger() {
    chargement.value = users.value.length === 0
    try {
        await Promise.all([chargerUsers(), chargerStats(), loadAdminReferences()])
    } catch (e) {
        window.dispatchEvent(new CustomEvent('app-error', { detail: e?.response?.data?.message || 'Une erreur est survenue' }))
    } finally {
        chargement.value = false
    }
}

function changerPage(p) {
    page.value = p
    chargerUsers()
}

watch(rechercheDebounced, () => {
    page.value = 1
    chargerUsers()
})

function toutRole(cocher) {
    form.value.roles = cocher ? roles.value.map(r => r.id) : []
}

function toutPermission(cocher) {
    form.value.permissions = cocher ? permissionsDisponibles.value.map(p => p.id) : []
}

async function sauvegarder() {
    erreur.value = ''
    if (!form.value.nom || !form.value.prenom || !form.value.email) {
        erreur.value = 'Nom, prenom et email obligatoires'
        return
    }
    if (!form.value.role_id) {
        erreur.value = 'Sélectionnez un rôle'
        return
    }
    if (!userEdit.value && !form.value.password) {
        erreur.value = 'Mot de passe obligatoire pour un nouvel utilisateur'
        return
    }

    envoi.value = true
    try {
        const payload = {
            nom: form.value.nom,
            prenom: form.value.prenom,
            email: form.value.email,
            telephone: form.value.telephone,
            poste: form.value.poste,
            role_id: Number(form.value.role_id),
            permissions: estSuperAdmin.value ? form.value.permissions.map(Number) : [],
        }
        if (form.value.password) payload.password = form.value.password

        if (userEdit.value) {
            const { data } = await api.put(`/users/${userEdit.value.id}`, payload)
            const index = users.value.findIndex(u => u.id === userEdit.value.id)
            if (index >= 0) users.value[index] = { ...users.value[index], ...(data || payload) }
        } else {
            const { data } = await api.post('/users', payload)
            if (data) users.value.unshift(data)
        }

        chargerStats()
        fermerModal()
    } catch (e) {
        const response = e.response?.data
        erreur.value = response?.errors
            ? Object.values(response.errors).flat()[0]
            : response?.message || 'Erreur lors de la sauvegarde'
    } finally {
        envoi.value = false
    }
}

async function changerRole(userId, roleId) {
    if (!roleId) return
    const user = users.value.find(u => u.id === userId)
    const previous = user?.roles || []
    const role = roles.value.find(r => Number(r.id) === Number(roleId))
    if (user && role) user.roles = [role]

    try {
        await api.post(`/users/${userId}/roles`, { role_id: parseInt(roleId) })
    } catch (e) {
        if (user) user.roles = previous
        window.dispatchEvent(new CustomEvent('app-error', { detail: e?.response?.data?.message || 'Une erreur est survenue' }))
    }
}

async function toggleActif(u) {
    const action = u.actif ? 'desactiver' : 'activer'
    if (!confirm(`Voulez-vous ${action} ${u.prenom} ${u.nom} ?`)) return
    const previous = u.actif
    u.actif = !u.actif

    try {
        await api.post(`/users/${u.id}/activer`)
        chargerStats()
    } catch (e) {
        u.actif = previous
        window.dispatchEvent(new CustomEvent('app-error', { detail: e?.response?.data?.message || 'Une erreur est survenue' }))
    }
}

async function toggleVerifie(u) {
    const previous = u.verifie
    u.verifie = !u.verifie

    try {
        await api.post(`/users/${u.id}/verifier`)
    } catch (e) {
        u.verifie = previous
        window.dispatchEvent(new CustomEvent('app-error', { detail: e?.response?.data?.message || 'Une erreur est survenue' }))
    }
}

onMounted(charger)
</script>

<style scoped>
.page-header { display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: 20px; }
.titre { font-size: 18px; font-weight: 600; color: var(--color-text-primary); margin: 0; }
.btn-primary { background: #1D9E75; color: white; border: none; border-radius: 8px; padding: 9px 18px; font-size: 13px; font-weight: 500; cursor: pointer; }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
.stats-mini { display: flex; gap: 12px; margin-bottom: 16px; flex-wrap: wrap; }
.stat-mini { background: white; border: 1px solid #e5e7eb; border-radius: 8px; padding: 14px 20px; min-width: 145px; }
.stat-num { font-size: 22px; font-weight: 700; color: var(--color-text-primary); }
.stat-num.success { color: #1D9E75; }
.stat-num.danger { color: #E24B4A; }
.stat-label { font-size: 12px; color: #9ca3af; margin-top: 2px; }
.section { background: white; border-radius: 8px; border: 1px solid #e5e7eb; overflow: hidden; }
.vide { text-align: center; padding: 40px; color: #9ca3af; font-size: 13px; }
.recherche-bar { padding: 14px 16px; border-bottom: 1px solid #f3f4f6; }
.recherche-input { width: min(100%, 320px); height: 36px; padding: 0 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 13px; outline: none; }
.recherche-input:focus { border-color: #1D9E75; }
.table { width: 100%; border-collapse: collapse; }
.table th { padding: 11px 16px; text-align: left; font-size: 11px; font-weight: 600; color: #6b7280; background: #f9fafb; border-bottom: 1px solid #e5e7eb; text-transform: uppercase; letter-spacing: 0; }
.table td { padding: 12px 16px; font-size: 13px; color: #374151; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
.table tr:last-child td { border-bottom: none; }
.table tr:hover td { background: #f9fafb; }
.user-cell { display: flex; align-items: center; gap: 10px; }
.user-av { width: 34px; height: 34px; border-radius: 50%; background: #1D9E75; color: white; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 600; flex-shrink: 0; }
.user-nom { font-size: 13px; font-weight: 500; color: var(--color-text-primary); }
.user-tel, .email, .poste { color: #6b7280; font-size: 12px; }
.role-select { max-width: 150px; padding: 5px 8px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 12px; color: #374151; outline: none; cursor: pointer; text-transform: capitalize; }
.role-select:focus { border-color: #1D9E75; }
.pill-count { display: inline-flex; min-width: 26px; height: 24px; align-items: center; justify-content: center; border-radius: 999px; background: #eef7f4; color: #085041; font-size: 12px; font-weight: 700; }
.badge-actif { font-size: 11px; font-weight: 600; padding: 3px 8px; border-radius: 10px; }
.badge-actif.actif { background: #E1F5EE; color: #085041; }
.badge-actif.inactif { background: #FCEBEB; color: #791F1F; }
.actions-cell { display: flex; gap: 6px; flex-wrap: wrap; }
.btn-modifier, .btn-toggle { font-size: 11px; padding: 4px 10px; border: none; border-radius: 6px; cursor: pointer; }
.btn-modifier { background: #E6F1FB; color: #0C447C; }
.btn-toggle.desactiver { background: #FCEBEB; color: #791F1F; }
.btn-toggle.activer { background: #E1F5EE; color: #085041; }
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.4); display: flex; align-items: center; justify-content: center; z-index: 100; padding: 16px; }
.modal { background: white; border-radius: 8px; padding: 24px; width: 100%; max-width: 760px; max-height: 90vh; overflow-y: auto; }
.modal-titre { font-size: 16px; font-weight: 600; color: var(--color-text-primary); margin: 0 0 20px; }
.champ { display: flex; flex-direction: column; gap: 6px; margin-bottom: 14px; }
.champ label { font-size: 13px; font-weight: 500; color: #374151; }
.champ input { padding: 9px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 13px; color: var(--color-text-primary); outline: none; }
.champ input:focus { border-color: #1D9E75; }
.champ-grille { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
.bloc-choix { border: 1px solid #e5e7eb; border-radius: 8px; padding: 14px; margin-bottom: 14px; }
.bloc-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; margin-bottom: 12px; }
.bloc-head h4 { margin: 0; font-size: 14px; color: #111827; }
.bloc-head p, .apercu p { margin: 3px 0 0; color: #6b7280; font-size: 12px; }
.head-actions { display: flex; gap: 8px; }
.btn-lien { background: transparent; border: none; color: #0C447C; cursor: pointer; font-size: 12px; padding: 0; }
.checks { display: grid; gap: 8px; }
.role-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
.permission-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); max-height: 220px; overflow: auto; padding-right: 4px; }
.check-card, .check-line { display: flex; align-items: flex-start; gap: 8px; border: 1px solid #edf0f3; border-radius: 8px; padding: 9px; cursor: pointer; }
.check-card input, .check-line input { margin-top: 2px; accent-color: #1D9E75; }
.check-card strong { display: block; font-size: 13px; color: #111827; text-transform: capitalize; }
.check-card small { display: block; font-size: 11px; color: #6b7280; margin-top: 2px; }
.check-line span { font-size: 12px; color: #374151; text-transform: capitalize; overflow-wrap: anywhere; }
.apercu { background: #f9fafb; border: 1px solid #edf0f3; border-radius: 8px; padding: 12px; margin-bottom: 14px; }
.apercu strong { font-size: 13px; color: #111827; }
.chips { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 10px; }
.chip { background: white; border: 1px solid #dbe8e4; color: #085041; border-radius: 999px; padding: 4px 8px; font-size: 11px; text-transform: capitalize; }
.erreur-box { background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 10px; font-size: 13px; color: #dc2626; margin-bottom: 14px; }
.modal-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 8px; }
.btn-annuler { padding: 9px 18px; border: 1px solid #e5e7eb; border-radius: 8px; background: white; font-size: 13px; cursor: pointer; color: #6b7280; }
@media (max-width: 760px) {
  .page-header, .bloc-head, .modal-actions { align-items: stretch; flex-direction: column; }
  .champ-grille, .role-grid, .permission-grid { grid-template-columns: 1fr; }
  .stats-mini { display: grid; grid-template-columns: 1fr; }
  .stat-mini { min-width: 0; }
  .recherche-input { width: 100%; }
  .table,
  .table thead,
  .table tbody,
  .table tr,
  .table td {
    display: block;
    width: 100%;
  }
  .table thead { display: none; }
  .table tbody {
    display: flex;
    flex-direction: column;
    gap: 12px;
    padding: 12px;
    background: #f8fafc;
  }
  .table tr {
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    background: white;
    box-shadow: 0 8px 18px rgba(15, 23, 42, 0.05);
    overflow: hidden;
  }
  .table tr:hover td { background: white; }
  .table td {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    min-height: 46px;
    padding: 10px 12px;
    border-bottom: 1px solid #f3f4f6;
    text-align: right;
  }
  .table td::before {
    content: attr(data-label);
    color: #6b7280;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0;
    text-align: left;
    text-transform: uppercase;
  }
  .table td:first-child {
    align-items: flex-start;
    flex-direction: column;
    text-align: left;
  }
  .table td:first-child::before { display: none; }
  .table td:last-child { border-bottom: none; }
  .role-select { max-width: 190px; }
  .actions-cell { justify-content: flex-end; }
}
</style>
