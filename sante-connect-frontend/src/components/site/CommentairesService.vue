<template>
  <div class="commentaires">
    <h3 class="comm-titre">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
      {{ liste.length }} commentaire{{ liste.length > 1 ? 's' : '' }}
    </h3>

    <div v-if="liste.length > 0" class="comm-liste">
      <div v-for="c in liste" :key="c.id" class="comm-item">
        <div class="comm-avatar">{{ c.nom_visiteur?.charAt(0)?.toUpperCase() || '?' }}</div>
        <div class="comm-content">
          <div class="comm-header">
            <span class="comm-nom">{{ c.nom_visiteur }}</span>
            <span class="comm-date">{{ formatDate(c.created_at) }}</span>
          </div>
          <p class="comm-texte">{{ c.contenu }}</p>
        </div>
      </div>
    </div>

    <div v-else class="comm-vide">
      <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
      <p>Soyez le premier à commenter ce service.</p>
    </div>

    <div class="comm-form">
      <h4 class="form-titre">Laisser un commentaire</h4>
      <div class="form-grid">
        <div class="champ">
          <label>Nom complet <span class="req">*</span></label>
          <input v-model="form.nom_visiteur" type="text" placeholder="Votre nom" />
        </div>
        <div class="champ">
          <label>Email <span class="opt">(optionnel)</span></label>
          <input v-model="form.email_visiteur" type="email" placeholder="votre@email.com" />
        </div>
        <div class="champ full">
          <label>Commentaire <span class="req">*</span></label>
          <textarea v-model="form.contenu" rows="4" placeholder="Votre avis sur ce service..." />
        </div>
      </div>

      <div v-if="erreur" class="alerte danger">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        {{ erreur }}
      </div>

      <button @click="soumettre" :disabled="envoi" class="btn-soumettre">
        <svg v-if="!envoi" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
        <svg v-else class="spin" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
        {{ envoi ? 'Envoi...' : 'Envoyer le commentaire' }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { API_BASE } from '../../api/client.js'

const props = defineProps({
  serviceId:    Number,
  commentaires: Array,
})

const liste  = ref([...(props.commentaires || [])])
const erreur = ref('')
const envoi  = ref(false)
const form   = ref({ nom_visiteur: '', email_visiteur: '', contenu: '' })

async function soumettre() {
  erreur.value = ''
  if (!form.value.nom_visiteur.trim() || !form.value.contenu.trim()) {
    erreur.value = 'Le nom et le commentaire sont obligatoires.'
    return
  }
  envoi.value = true
  try {
    const res = await fetch(`${API_BASE}/site/commentaires`, {
      method:  'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body:    JSON.stringify({ ...form.value, annuaire_id: props.serviceId }),
    })
    if (!res.ok) throw new Error()
    const data = await res.json()
    liste.value.push(data.commentaire)
    form.value = { nom_visiteur: '', email_visiteur: '', contenu: '' }
  } catch {
    erreur.value = 'Une erreur est survenue, réessaie.'
  } finally {
    envoi.value = false
  }
}

function formatDate(d) {
  if (!d) return ''
  return new Date(d).toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' })
}
</script>

<style scoped>
.commentaires { }
.comm-titre { display: flex; align-items: center; gap: 9px; font-size: 17px; font-weight: 800; color: #0f172a; margin: 0 0 20px; }
.comm-liste { display: flex; flex-direction: column; gap: 14px; margin-bottom: 28px; }
.comm-item { display: flex; gap: 12px; }
.comm-avatar { width: 36px; height: 36px; border-radius: 10px; flex-shrink: 0; background: linear-gradient(135deg, #e8edf4, #dbeafe); color: #1a6fc4; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 800; }
.comm-content { flex: 1; background: #f8fafc; border: 1px solid #e8edf4; border-radius: 12px; padding: 12px 16px; }
.comm-header { display: flex; align-items: center; gap: 10px; margin-bottom: 6px; }
.comm-nom  { font-size: 13px; font-weight: 700; color: #0f172a; }
.comm-date { font-size: 11.5px; color: #94a3b8; }
.comm-texte { font-size: 14px; color: #374151; line-height: 1.6; margin: 0; }
.comm-vide { display: flex; flex-direction: column; align-items: center; gap: 8px; padding: 28px; text-align: center; color: #94a3b8; background: #f8fafc; border-radius: 12px; margin-bottom: 24px; }
.comm-vide p { margin: 0; font-size: 13.5px; }
.comm-form { background: #f8fafc; border: 1px solid #e8edf4; border-radius: 14px; padding: 22px; }
.form-titre { font-size: 15px; font-weight: 700; color: #0f172a; margin: 0 0 16px; }
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px; }
.champ { display: flex; flex-direction: column; gap: 5px; }
.champ.full { grid-column: 1 / -1; }
.champ label { font-size: 12.5px; font-weight: 600; color: #374151; }
.req { color: #ef4444; }
.opt { font-weight: 400; color: #94a3b8; }
.champ input, .champ textarea { padding: 9px 12px; border: 1.5px solid #e2e8f0; border-radius: 9px; font-size: 13px; color: #0f172a; outline: none; font-family: inherit; background: white; transition: border-color 0.15s; }
.champ input:focus, .champ textarea:focus { border-color: #1a6fc4; }
.champ input::placeholder, .champ textarea::placeholder { color: #94a3b8; }
.alerte { display: flex; align-items: center; gap: 8px; padding: 11px 14px; border-radius: 9px; font-size: 13px; margin-bottom: 12px; }
.danger { background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; }
.btn-soumettre { display: inline-flex; align-items: center; gap: 8px; padding: 11px 22px; background: linear-gradient(135deg, #1a6fc4, #3b8fd8); border: none; border-radius: 10px; color: white; font-size: 14px; font-weight: 700; cursor: pointer; font-family: inherit; transition: all 0.15s; box-shadow: 0 3px 12px rgba(26,111,196,0.35); }
.btn-soumettre:hover:not(:disabled) { transform: translateY(-1px); }
.btn-soumettre:disabled { opacity: 0.65; cursor: not-allowed; }
@keyframes spin { to { transform: rotate(360deg); } }
.spin { animation: spin 0.9s linear infinite; }
@media (max-width: 540px) { .form-grid { grid-template-columns: 1fr; } .champ.full { grid-column: 1; } }
</style>
