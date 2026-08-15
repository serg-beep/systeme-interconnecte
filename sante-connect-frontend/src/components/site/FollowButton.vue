<template>
  <button
    class="follow-btn"
    :class="{ suivi }"
    :disabled="loading"
    @click="toggle"
  >
    {{ suivi ? 'Abonné' : 'Suivre' }}
  </button>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth.js'
import api from '../../api/client.js'

const props = defineProps({
  type:  { type: String, required: true },   // 'user' | 'entreprise'
  id:    { type: Number, required: true },
  suivi: { type: Boolean, default: false },
})
const emit = defineEmits(['change'])

const router  = useRouter()
const auth    = useAuthStore()
const suivi   = ref(props.suivi)
const loading = ref(false)

watch(() => props.suivi, v => suivi.value = v)

async function toggle() {
  if (!auth.isAuthenticated) {
    router.push({ name: 'login' })
    return
  }
  if (loading.value) return
  loading.value = true
  try {
    const { data } = await api.post('/abonnements', { followable_type: props.type, followable_id: props.id })
    suivi.value = data.suivi
    emit('change', { suivi: suivi.value, total: data.total })
  } catch {
    // silencieux
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.follow-btn {
  padding: 8px 18px; border-radius: 10px; border: none;
  background: linear-gradient(135deg, #1a6fc4, #3b8fd8); color: white;
  font-size: 13.5px; font-weight: 700; cursor: pointer; font-family: inherit;
  transition: all 0.15s; box-shadow: 0 3px 12px rgba(26,111,196,0.3);
}
.follow-btn:hover:not(:disabled) { transform: translateY(-1px); }
.follow-btn.suivi { background: #eef2f7; color: #475569; box-shadow: none; border: 1.5px solid #e2e8f0; }
.follow-btn:disabled { opacity: 0.6; cursor: default; }
</style>
