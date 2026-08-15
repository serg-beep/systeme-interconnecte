<template>
  <div class="avis-bloc">
    <div class="avis-entete">
      <h3 class="avis-titre">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01z"/></svg>
        Avis
      </h3>
      <div class="avis-moyenne" v-if="moyenne">
        <span class="moyenne-val">{{ moyenne }}</span>
        <span class="moyenne-etoiles">
          <svg v-for="n in 5" :key="n" width="14" height="14" viewBox="0 0 24 24" :fill="n <= Math.round(moyenne) ? '#f59e0b' : '#e2e8f0'"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01z"/></svg>
        </span>
        <span class="moyenne-total">({{ liste.length }} avis)</span>
      </div>
    </div>

    <div v-if="liste.length > 0" class="avis-liste">
      <div v-for="a in liste" :key="a.id" class="avis-item">
        <div class="avis-avatar">{{ a.user?.prenom?.charAt(0)?.toUpperCase() || '?' }}</div>
        <div class="avis-content">
          <div class="avis-header">
            <span class="avis-nom">{{ a.user?.prenom }} {{ a.user?.nom }}</span>
            <span class="avis-etoiles">
              <svg v-for="n in 5" :key="n" width="11" height="11" viewBox="0 0 24 24" :fill="n <= a.note ? '#f59e0b' : '#e2e8f0'"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01z"/></svg>
            </span>
          </div>
          <p v-if="a.commentaire" class="avis-texte">{{ a.commentaire }}</p>
        </div>
      </div>
    </div>
    <p v-else class="avis-vide">Aucun avis pour l'instant.</p>

    <div class="avis-form">
      <p class="form-label">{{ auth.isAuthenticated ? 'Votre avis' : 'Un compte est nécessaire pour laisser un avis' }}</p>
      <div class="etoiles-input" :class="{ disabled: !auth.isAuthenticated }">
        <svg
          v-for="n in 5" :key="n" width="24" height="24" viewBox="0 0 24 24"
          :fill="n <= (survol || note) ? '#f59e0b' : '#e2e8f0'"
          @mouseenter="survol = n" @mouseleave="survol = 0" @click="choisir(n)"
        ><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01z"/></svg>
      </div>
      <textarea
        v-if="auth.isAuthenticated"
        v-model="commentaire"
        rows="3"
        placeholder="Votre commentaire (optionnel)..."
      />
      <div v-if="message" class="avis-message" :class="{ erreur: erreur }">{{ message }}</div>
      <button v-if="auth.isAuthenticated" class="btn-envoyer" :disabled="envoi || !note" @click="envoyer">
        {{ envoi ? 'Envoi...' : 'Envoyer mon avis' }}
      </button>
      <div v-else class="avis-connexion-actions">
        <router-link to="/register-particulier" class="btn-envoyer btn-lien">Créer un compte</router-link>
        <router-link to="/login" class="lien-connexion">Déjà inscrit ? Se connecter</router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useAuthStore } from '../../stores/auth.js'
import api from '../../api/client.js'

const props = defineProps({
  annuaireId: { type: Number, required: true },
  avis:       { type: Array, default: () => [] },
  moyenneInitiale: { type: [Number, null], default: null },
})

const auth = useAuthStore()
const liste = ref([...(props.avis || [])])
const moyenne = ref(props.moyenneInitiale)

const note        = ref(0)
const survol      = ref(0)
const commentaire = ref('')
const envoi       = ref(false)
const message     = ref('')
const erreur      = ref(false)

function choisir(n) {
  if (!auth.isAuthenticated) return
  note.value = n
}

async function envoyer() {
  if (!note.value) return
  envoi.value = true
  erreur.value = false
  message.value = ''
  try {
    const { data } = await api.post(`/annuaire/${props.annuaireId}/avis`, {
      note: note.value,
      commentaire: commentaire.value || null,
    })
    const avisPublie = { ...data.avis, user: { id: auth.user.id, prenom: auth.user.prenom, nom: auth.user.nom } }
    const indexExistant = liste.value.findIndex(a => a.user_id === auth.user.id)
    if (indexExistant !== -1) {
      liste.value[indexExistant] = avisPublie
    } else {
      liste.value.unshift(avisPublie)
    }
    moyenne.value = Math.round((liste.value.reduce((s, a) => s + a.note, 0) / liste.value.length) * 10) / 10

    message.value = 'Merci pour votre avis !'
    note.value = 0
    commentaire.value = ''
  } catch (e) {
    erreur.value = true
    message.value = e.response?.data?.message || 'Une erreur est survenue.'
  } finally {
    envoi.value = false
  }
}
</script>

<style scoped>
.avis-bloc { }
.avis-entete { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; margin-bottom: 18px; }
.avis-titre { display: flex; align-items: center; gap: 8px; font-size: 16px; font-weight: 800; color: #0f172a; margin: 0; }
.avis-titre svg { color: #f59e0b; }
.avis-moyenne { display: flex; align-items: center; gap: 6px; }
.moyenne-val { font-size: 16px; font-weight: 800; color: #0f172a; }
.moyenne-etoiles { display: flex; gap: 1px; }
.moyenne-total { font-size: 12px; color: #94a3b8; }

.avis-liste { display: flex; flex-direction: column; gap: 12px; margin-bottom: 22px; }
.avis-item { display: flex; gap: 12px; }
.avis-avatar { width: 34px; height: 34px; border-radius: 9px; flex-shrink: 0; background: linear-gradient(135deg, #e8edf4, #dbeafe); color: #1a6fc4; display: flex; align-items: center; justify-content: center; font-size: 12.5px; font-weight: 800; }
.avis-content { flex: 1; background: #f8fafc; border: 1px solid #e8edf4; border-radius: 12px; padding: 10px 14px; }
.avis-header { display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 4px; }
.avis-nom { font-size: 12.5px; font-weight: 700; color: #0f172a; }
.avis-etoiles { display: flex; gap: 1px; }
.avis-texte { font-size: 13px; color: #374151; margin: 0; line-height: 1.55; }
.avis-vide { color: #94a3b8; font-size: 13.5px; margin: 0 0 20px; }

.avis-form { background: #f8fafc; border: 1px solid #e8edf4; border-radius: 14px; padding: 18px 20px; }
.form-label { font-size: 13px; font-weight: 700; color: #374151; margin: 0 0 10px; }
.etoiles-input { display: flex; gap: 4px; margin-bottom: 12px; }
.etoiles-input svg { cursor: pointer; transition: transform 0.1s; }
.etoiles-input:not(.disabled) svg:hover { transform: scale(1.15); }
.etoiles-input.disabled svg { cursor: default; }
.avis-form textarea {
  width: 100%; box-sizing: border-box; padding: 10px 12px; border: 1.5px solid #e2e8f0; border-radius: 9px;
  font-size: 13px; font-family: inherit; color: #0f172a; outline: none; resize: vertical; margin-bottom: 12px;
}
.avis-form textarea:focus { border-color: #1a6fc4; }
.avis-message { font-size: 12.5px; color: #15803d; margin-bottom: 10px; }
.avis-message.erreur { color: #dc2626; }
.btn-envoyer {
  display: inline-flex; padding: 9px 20px; background: linear-gradient(135deg, #1a6fc4, #3b8fd8);
  border: none; border-radius: 10px; color: white; font-size: 13.5px; font-weight: 700;
  cursor: pointer; font-family: inherit; text-decoration: none;
}
.btn-envoyer:disabled { opacity: 0.5; cursor: not-allowed; }
.avis-connexion-actions { display: flex; align-items: center; gap: 14px; flex-wrap: wrap; }
.lien-connexion { font-size: 13px; color: #1a6fc4; text-decoration: none; font-weight: 600; }
.lien-connexion:hover { text-decoration: underline; }
</style>
