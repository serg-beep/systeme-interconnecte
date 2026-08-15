<template>
  <div class="moderation">
    <div class="onglets">
      <button class="onglet" :class="{ active: onglet === 'commentaires' }" @click="changerOnglet('commentaires')">
        Commentaires
        <span v-if="compteurs.commentaires" class="onglet-badge">{{ compteurs.commentaires }}</span>
      </button>
      <button class="onglet" :class="{ active: onglet === 'avis' }" @click="changerOnglet('avis')">
        Avis
        <span v-if="compteurs.avis" class="onglet-badge">{{ compteurs.avis }}</span>
      </button>
    </div>

    <div class="filtres">
      <button
        v-for="s in statuts" :key="s.value"
        class="filtre-btn" :class="{ active: statutActif === s.value }"
        @click="changerStatut(s.value)"
      >{{ s.label }}</button>
    </div>

    <div v-if="loading" class="etat-vide">Chargement...</div>

    <div v-else-if="items.length === 0" class="etat-vide">
      Aucun élément {{ statutActif === 'en_attente' ? 'en attente' : `au statut "${statutActif}"` }}.
    </div>

    <div v-else class="liste">
      <!-- Commentaires -->
      <div v-for="item in items" :key="item.id" class="carte">
        <div class="carte-head">
          <div class="carte-auteur">
            <div class="avatar">{{ (item.nom_visiteur || item.user?.prenom || '?').charAt(0).toUpperCase() }}</div>
            <div>
              <div class="nom">{{ item.nom_visiteur || `${item.user?.prenom || ''} ${item.user?.nom || ''}` }}</div>
              <div class="contexte">
                {{ onglet === 'commentaires'
                  ? (item.annuaire ? `sur "${item.annuaire.service}"` : '')
                  : (item.annuaire ? `sur "${item.annuaire.service}"` : '') }}
              </div>
            </div>
          </div>
          <span class="statut-badge" :class="item.statut">{{ libelleStatut(item.statut) }}</span>
        </div>

        <div class="carte-etoiles" v-if="onglet === 'avis'">
          <svg v-for="n in 5" :key="n" width="14" height="14" viewBox="0 0 24 24" :fill="n <= item.note ? '#f59e0b' : '#e2e8f0'"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01z"/></svg>
        </div>

        <p class="carte-texte">{{ item.contenu || item.commentaire || '(sans commentaire)' }}</p>

        <div class="carte-actions">
          <button v-if="item.statut !== 'approuve'" class="btn-action btn-approuver" @click="agir(item, 'approuver')">
            Approuver
          </button>
          <button v-if="item.statut !== 'rejete'" class="btn-action btn-rejeter" @click="agir(item, 'rejeter')">
            Rejeter
          </button>
          <button class="btn-action btn-supprimer" @click="agir(item, 'supprimer')">
            Supprimer
          </button>
        </div>
      </div>
    </div>

    <div class="pagination" v-if="meta && meta.last_page > 1">
      <button :disabled="meta.current_page === 1" @click="charger(meta.current_page - 1)">← Précédent</button>
      <span>Page {{ meta.current_page }} / {{ meta.last_page }}</span>
      <button :disabled="meta.current_page === meta.last_page" @click="charger(meta.current_page + 1)">Suivant →</button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../../api/client.js'

const onglet      = ref('commentaires')
const statutActif = ref('approuve')
const items       = ref([])
const meta        = ref(null)
const loading     = ref(true)
const compteurs   = ref({ commentaires: 0, avis: 0 })

const statuts = [
  { value: 'en_attente', label: 'En attente' },
  { value: 'approuve',   label: 'Approuvés' },
  { value: 'rejete',     label: 'Rejetés' },
]

function libelleStatut(s) {
  return { en_attente: 'En attente', approuve: 'Approuvé', rejete: 'Rejeté' }[s] || s
}

let requeteCourante = 0

async function charger(page = 1) {
  const ongletDemande = onglet.value
  const idRequete = ++requeteCourante
  loading.value = true
  try {
    const endpoint = ongletDemande === 'commentaires' ? '/commentaires' : '/avis'
    const { data } = await api.get(endpoint, { params: { statut: statutActif.value, page } })
    if (idRequete !== requeteCourante) return // une requête plus récente a pris le relais
    items.value = data.data
    meta.value  = data
  } finally {
    if (idRequete === requeteCourante) loading.value = false
  }
}

async function chargerCompteurs() {
  // Tout est publié immédiatement (compte requis) : le badge indique le volume
  // total publié, pas une file d'attente à traiter.
  const [comm, avis] = await Promise.all([
    api.get('/commentaires', { params: { statut: 'approuve', per_page: 1 } }),
    api.get('/avis', { params: { statut: 'approuve', per_page: 1 } }),
  ])
  compteurs.value = { commentaires: comm.data.total || 0, avis: avis.data.total || 0 }
}

function changerOnglet(o) {
  onglet.value = o
  charger(1)
}

function changerStatut(s) {
  statutActif.value = s
  charger(1)
}

async function agir(item, action) {
  const endpoint = onglet.value === 'commentaires' ? 'commentaires' : 'avis'
  try {
    if (action === 'supprimer') {
      await api.delete(`/${endpoint}/${item.id}`)
    } else {
      await api.post(`/${endpoint}/${item.id}/${action}`)
    }
    items.value = items.value.filter(i => i.id !== item.id)
    chargerCompteurs()
  } catch {
    // silencieux : l'utilisateur voit simplement que l'action n'a pas d'effet
  }
}

onMounted(() => {
  charger()
  chargerCompteurs()
})
</script>

<style scoped>
.moderation { display: flex; flex-direction: column; gap: 18px; }

.onglets { display: flex; gap: 4px; border-bottom: 1px solid var(--color-border); }
.onglet {
  display: flex; align-items: center; gap: 8px;
  padding: 10px 18px; background: none; border: none; border-bottom: 2px solid transparent;
  font-size: 14px; font-weight: 600; color: var(--color-text-muted); cursor: pointer; font-family: inherit;
}
.onglet.active { color: var(--color-primary); border-bottom-color: var(--color-primary); }
.onglet-badge { background: var(--color-danger); color: white; font-size: 11px; font-weight: 700; padding: 1px 7px; border-radius: 999px; }

.filtres { display: flex; gap: 8px; }
.filtre-btn {
  padding: 6px 14px; border-radius: 999px; border: 1.5px solid var(--color-border);
  background: var(--color-surface); color: var(--color-text-secondary); font-size: 12.5px; font-weight: 600;
  cursor: pointer; font-family: inherit;
}
.filtre-btn.active { background: var(--color-primary); border-color: var(--color-primary); color: white; }

.etat-vide { text-align: center; color: var(--color-text-muted); padding: 48px; background: var(--color-surface); border-radius: 14px; border: 1px solid var(--color-border); }

.liste { display: flex; flex-direction: column; gap: 12px; }
.carte { background: var(--color-surface); border: 1px solid var(--color-border); border-radius: 14px; padding: 16px 18px; }
.carte-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 10px; }
.carte-auteur { display: flex; align-items: center; gap: 10px; }
.avatar { width: 34px; height: 34px; border-radius: 9px; flex-shrink: 0; background: linear-gradient(135deg, var(--color-primary), var(--color-accent)); color: white; display: flex; align-items: center; justify-content: center; font-size: 12.5px; font-weight: 800; }
.nom { font-size: 13.5px; font-weight: 700; color: var(--color-text-primary); }
.contexte { font-size: 12px; color: var(--color-text-muted); }
.statut-badge { font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 999px; white-space: nowrap; }
.statut-badge.en_attente { background: #fef3e2; color: #b45309; }
.statut-badge.approuve { background: #e6f7f5; color: #0d9488; }
.statut-badge.rejete { background: #fef2f2; color: #dc2626; }
.carte-etoiles { display: flex; gap: 2px; margin-bottom: 8px; }
.carte-texte { font-size: 13.5px; color: var(--color-text-secondary); line-height: 1.6; margin: 0 0 12px; }
.carte-actions { display: flex; gap: 8px; }
.btn-action { padding: 7px 14px; border-radius: 8px; border: 1.5px solid var(--color-border); background: var(--color-surface); font-size: 12.5px; font-weight: 700; cursor: pointer; font-family: inherit; }
.btn-approuver { color: #0d9488; border-color: #99e2d8; }
.btn-approuver:hover { background: #e6f7f5; }
.btn-rejeter { color: #b45309; border-color: #f3d19c; }
.btn-rejeter:hover { background: #fef3e2; }
.btn-supprimer { color: #dc2626; border-color: #fecaca; }
.btn-supprimer:hover { background: #fef2f2; }

.pagination { display: flex; align-items: center; justify-content: center; gap: 16px; font-size: 13px; color: var(--color-text-muted); }
.pagination button { padding: 6px 14px; border-radius: 8px; border: 1px solid var(--color-border); background: var(--color-surface); cursor: pointer; font-size: 12.5px; }
.pagination button:disabled { opacity: 0.4; cursor: not-allowed; }
</style>
