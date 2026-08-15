<template>
  <button
    class="like-btn"
    :class="{ liked }"
    :disabled="loading"
    :title="auth.isAuthenticated ? (liked ? 'Retirer le like' : 'Aimer') : 'Connectez-vous pour aimer'"
    @click="toggle"
  >
    <svg width="16" height="16" viewBox="0 0 24 24" :fill="liked ? 'currentColor' : 'none'" stroke="currentColor" stroke-width="2">
      <path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.8 1-1a5.5 5.5 0 0 0 0-7.6z"/>
    </svg>
    <span>{{ count }}</span>
  </button>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth.js'
import api from '../../api/client.js'

const props = defineProps({
  type:  { type: String, required: true },   // 'annuaire'
  id:    { type: Number, required: true },
  liked: { type: Boolean, default: false },
  count: { type: Number, default: 0 },
})
const emit = defineEmits(['change'])

const router  = useRouter()
const auth    = useAuthStore()
const liked   = ref(props.liked)
const count   = ref(props.count)
const loading = ref(false)

watch(() => props.liked, v => liked.value = v)
watch(() => props.count, v => count.value = v)

async function toggle() {
  if (!auth.isAuthenticated) {
    router.push({ name: 'login' })
    return
  }
  if (loading.value) return
  loading.value = true
  try {
    const { data } = await api.post('/likes', { likeable_type: props.type, likeable_id: props.id })
    liked.value = data.liked
    count.value = data.total
    emit('change', { liked: liked.value, count: count.value })
  } catch {
    // silencieux : un like raté n'est pas bloquant pour l'utilisateur
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.like-btn {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 7px 14px; border-radius: 999px; border: 1.5px solid #e2e8f0;
  background: white; color: #64748b; font-size: 13px; font-weight: 700;
  cursor: pointer; transition: all 0.15s; font-family: inherit;
}
.like-btn:hover:not(:disabled) { border-color: #f43f5e; color: #f43f5e; }
.like-btn.liked { background: #fff1f2; border-color: #fecdd3; color: #e11d48; }
.like-btn:disabled { opacity: 0.6; cursor: default; }
</style>
