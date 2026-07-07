<template>
  <div class="conv-layout">

    <!-- Liste conversations -->
    <div class="conv-liste">
      <div class="conv-liste-header">
        <h2 class="titre">Conversations</h2>
        <button class="btn-primary" @click="showModal = true">+</button>
      </div>
      <div v-if="chargement" class="vide">Chargement...</div>
      <div v-else-if="conversations.length === 0" class="vide">Aucune conversation</div>
      <template v-else>
        <div v-for="c in conversations" :key="c.id"
          class="conv-item" :class="{ active: convActive?.id === c.id }"
          @click="ouvrirConv(c)">
          <div class="conv-avatar">{{ c.sujet?.[0] || 'C' }}</div>
          <div class="conv-info">
            <div class="conv-sujet">{{ c.sujet || 'Conversation' }}</div>
            <div class="conv-dernier">{{ c.dernier_message?.contenu?.substring(0, 40) || 'Aucun message' }}</div>
          </div>
          <span class="conv-badge" :class="c.type">{{ c.type }}</span>
        </div>
        <UiPagination :meta="meta" @change="changerPage" />
      </template>
    </div>

    <!-- Zone messages -->
    <div class="conv-messages">
      <div v-if="!convActive" class="conv-vide">
        <p>Selectionne une conversation</p>
      </div>
      <template v-else>
        <div class="messages-header">
          <div class="messages-titre">{{ convActive.sujet || 'Conversation' }}</div>
          <div class="messages-membres">
            {{ convActive.entreprises?.map(e => e.nom).join(', ') }}
          </div>
        </div>

        <div class="messages-liste" ref="messagesRef">
          <div v-if="chargementMessages" class="vide">Chargement...</div>
          <div v-else-if="erreurMessages" class="vide erreur">{{ erreurMessages }}</div>
          <div v-else-if="messages.length === 0" class="vide">Aucun message</div>
          <div v-else>
            <div v-for="m in messages" :key="m.id"
              class="message" :class="{ moi: estMoi(m) }">
              <div class="msg-avatar">{{ m.user?.prenom?.[0] }}</div>
              <div class="msg-bulle">
                <div class="msg-auteur">{{ m.user?.prenom }} {{ m.user?.nom }}</div>
                <div class="msg-texte">{{ m.contenu }}</div>
              </div>
            </div>
          </div>
        </div>

        <div class="message-input">
          <input v-model="nouveauMessage" type="text"
            placeholder="Ecrire un message..."
            @keyup.enter="envoyer" />
          <button class="btn-envoyer" @click="envoyer">Envoyer</button>
        </div>
      </template>
    </div>

    <!-- Modal nouvelle conversation -->
    <div v-if="showModal" class="modal-overlay" @click.self="showModal = false">
      <div class="modal">
        <h3 class="modal-titre">Nouvelle conversation</h3>
        <div class="champ">
          <label>Sujet</label>
          <input v-model="form.sujet" type="text" placeholder="Ex: Discussion medicaments" />
        </div>
        <div class="champ">
          <label>Type</label>
          <select v-model="form.type">
            <option value="prive">Privee (2 entreprises)</option>
            <option value="groupe">Groupe (plusieurs)</option>
          </select>
        </div>
        <div class="champ">
          <label>Entreprises a inviter</label>
          <div class="entreprises-liste">
            <label v-for="e in autresEntreprises" :key="e.id" class="entreprise-check">
              <input type="checkbox" :value="e.id" v-model="form.entreprises" />
              {{ e.nom }}
            </label>
          </div>
        </div>
        <div class="modal-actions">
          <button class="btn-annuler" @click="showModal = false">Annuler</button>
          <button class="btn-primary" @click="creer" :disabled="envoi">
            {{ envoi ? 'Creation...' : 'Creer' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, onMounted, nextTick } from 'vue'
import { useAuthStore } from '../../stores/auth.js'
import api from '../../api/client.js'
import { useReferenceData } from '../../composables/useReferenceData.js'
import UiPagination from '../../components/ui/UiPagination.vue'

const auth    = useAuthStore()
const { entreprises, loadEntreprises } = useReferenceData()
const chargement = ref(true)
const chargementMessages = ref(false)
const conversations = ref([])
const meta    = ref(null)
const page    = ref(1)
const messages  = ref([])
const messagesCache = new Map()
const convActive = ref(null)
const nouveauMessage = ref('')
const erreurMessages = ref('')
const showModal = ref(false)
const envoi   = ref(false)
const messagesRef = ref(null)

const form = ref({ sujet: '', type: 'prive', entreprises: [] })

const autresEntreprises = computed(() =>
    entreprises.value.filter(e => e.id !== auth.entreprise?.id)
)

function estMoi(m) { return m.user_id === auth.user?.id }

async function chargerConversations() {
    const { data } = await api.get('/conversations', { params: { paginate: 1, page: page.value, per_page: 20 } })
    conversations.value = data.data ?? data
    meta.value = data.data ? data : null
}

async function charger() {
    chargement.value = conversations.value.length === 0
    try {
        await Promise.all([chargerConversations(), loadEntreprises()])
    } catch (e) { window.dispatchEvent(new CustomEvent('app-error', { detail: e?.response?.data?.message || 'Une erreur est survenue' })) }
    finally { chargement.value = false }
}

function changerPage(p) {
    page.value = p
    chargerConversations()
}

async function ouvrirConv(conv) {
    convActive.value = conv
    erreurMessages.value = ''
    if (messagesCache.has(conv.id)) {
        messages.value = messagesCache.get(conv.id)
        await nextTick()
        if (messagesRef.value) messagesRef.value.scrollTop = messagesRef.value.scrollHeight
        return
    }

    chargementMessages.value = true
    try {
        const { data } = await api.get(`/conversations/${conv.id}/messages`)
        messages.value = Array.isArray(data.data) ? data.data : data
        messagesCache.set(conv.id, messages.value)
        await nextTick()
        if (messagesRef.value) messagesRef.value.scrollTop = messagesRef.value.scrollHeight
    } catch (e) {
        erreurMessages.value = e.response?.data?.message || 'Impossible de charger les messages'
        window.dispatchEvent(new CustomEvent('app-error', { detail: e?.response?.data?.message || 'Une erreur est survenue' }))
    }
    finally { chargementMessages.value = false }
}

async function envoyer() {
    const contenu = nouveauMessage.value.trim()
    if (!contenu || !convActive.value) return
    try {
        const { data } = await api.post(`/conversations/${convActive.value.id}/messages`, { contenu })
        messages.value.push(data)
        messagesCache.set(convActive.value.id, messages.value)
        convActive.value.dernier_message = data
        const index = conversations.value.findIndex(c => c.id === convActive.value.id)
        if (index > 0) {
            const [conversation] = conversations.value.splice(index, 1)
            conversations.value.unshift(conversation)
        }
        nouveauMessage.value = ''
        await nextTick()
        if (messagesRef.value) messagesRef.value.scrollTop = messagesRef.value.scrollHeight
    } catch (e) { window.dispatchEvent(new CustomEvent('app-error', { detail: e?.response?.data?.message || 'Une erreur est survenue' })) }
}

async function creer() {
    if (!form.value.entreprises.length) return
    envoi.value = true
    try {
        await api.post('/conversations', form.value)
        page.value = 1
        await chargerConversations()
        showModal.value = false
        form.value = { sujet: '', type: 'prive', entreprises: [] }
    } catch (e) { window.dispatchEvent(new CustomEvent('app-error', { detail: e?.response?.data?.message || 'Une erreur est survenue' })) }
    finally { envoi.value = false }
}

onMounted(charger)
</script>

<style scoped>
.conv-layout { display: flex; gap: 16px; height: calc(100vh - 130px); }
.conv-liste { width: 280px; background: white; border-radius: 12px; border: 1px solid #e5e7eb; display: flex; flex-direction: column; flex-shrink: 0; overflow: hidden; }
.conv-liste-header { display: flex; justify-content: space-between; align-items: center; padding: 16px; border-bottom: 1px solid #f3f4f6; }
.titre { font-size: 15px; font-weight: 600; color: #1a1a2e; margin: 0; }
.btn-primary { background: #1D9E75; color: white; border: none; border-radius: 6px; width: 28px; height: 28px; font-size: 18px; cursor: pointer; display: flex; align-items: center; justify-content: center; }
.vide { text-align: center; padding: 24px; color: #9ca3af; font-size: 13px; }
.vide.erreur { color: #dc2626; }
.conv-item { display: flex; align-items: center; gap: 10px; padding: 12px 16px; cursor: pointer; border-bottom: 1px solid #f9fafb; transition: background 0.15s; }
.conv-item:hover, .conv-item.active { background: #f0fdf4; }
.conv-avatar { width: 36px; height: 36px; border-radius: 50%; background: #1D9E75; color: white; display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 600; flex-shrink: 0; }
.conv-sujet  { font-size: 13px; font-weight: 500; color: #1a1a2e; }
.conv-dernier { font-size: 12px; color: #9ca3af; margin-top: 2px; }
.conv-badge { font-size: 10px; padding: 2px 7px; border-radius: 8px; margin-left: auto; flex-shrink: 0; }
.conv-badge.prive  { background: #E6F1FB; color: #0C447C; }
.conv-badge.groupe { background: #EEEDFE; color: #3C3489; }
.conv-messages { flex: 1; background: white; border-radius: 12px; border: 1px solid #e5e7eb; display: flex; flex-direction: column; overflow: hidden; }
.conv-vide { flex: 1; display: flex; align-items: center; justify-content: center; color: #9ca3af; font-size: 13px; }
.messages-header { padding: 14px 20px; border-bottom: 1px solid #f3f4f6; }
.messages-titre { font-size: 14px; font-weight: 600; color: #1a1a2e; }
.messages-membres { font-size: 12px; color: #9ca3af; margin-top: 2px; }
.messages-liste { flex: 1; overflow-y: auto; padding: 16px; display: flex; flex-direction: column; gap: 12px; }
.message { display: flex; gap: 10px; }
.message.moi { flex-direction: row-reverse; }
.msg-avatar { width: 32px; height: 32px; border-radius: 50%; background: #1D9E75; color: white; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 600; flex-shrink: 0; }
.msg-bulle { max-width: 70%; }
.msg-auteur { font-size: 11px; color: #9ca3af; margin-bottom: 4px; }
.message.moi .msg-auteur { text-align: right; }
.msg-texte { background: #e5e7eb; border-radius: 16px 16px 16px 4px; padding: 10px 14px; font-size: 13px; color: #1f2937; line-height: 1.45; box-shadow: 0 4px 10px rgba(15, 23, 42, 0.06); }
.message.moi .msg-texte { background: #1D9E75; color: white; border-radius: 16px 16px 4px 16px; }
.message-input { display: flex; align-items: center; gap: 8px; padding: 12px 16px; border-top: 1px solid #f3f4f6; background: white; flex-shrink: 0; }
.message-input input { flex: 1; min-width: 0; height: 38px; padding: 0 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 13px; outline: none; }
.message-input input:focus { border-color: #1D9E75; }
.btn-envoyer { height: 38px; background: #1D9E75; color: white; border: none; border-radius: 8px; padding: 0 16px; font-size: 13px; cursor: pointer; flex-shrink: 0; }
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.4); display: flex; align-items: center; justify-content: center; z-index: 100; }
.modal { background: white; border-radius: 16px; padding: 28px; width: 100%; max-width: 440px; }
.modal-titre { font-size: 16px; font-weight: 600; color: #1a1a2e; margin: 0 0 20px; }
.champ { display: flex; flex-direction: column; gap: 6px; margin-bottom: 14px; }
.champ label { font-size: 13px; font-weight: 500; color: #374151; }
.champ input, .champ select { padding: 9px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 13px; color: #1a1a2e; outline: none; }
.entreprises-liste { display: flex; flex-direction: column; gap: 8px; max-height: 160px; overflow-y: auto; border: 1px solid #d1d5db; border-radius: 8px; padding: 10px; }
.entreprise-check { display: flex; align-items: center; gap: 8px; font-size: 13px; color: #374151; cursor: pointer; }
.modal-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 8px; }
.btn-annuler { padding: 9px 18px; border: 1px solid #e5e7eb; border-radius: 8px; background: white; font-size: 13px; cursor: pointer; color: #6b7280; }
</style>
