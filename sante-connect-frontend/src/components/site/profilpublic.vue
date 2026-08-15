<template>
  <div class="profil-page">
    <div v-if="user" class="profil-card">
      <div class="profil-avatar">
        {{ (user.prenom?.charAt(0) || user.nom?.charAt(0) || '?').toUpperCase() }}
      </div>
      <h1 class="profil-nom">{{ user.prenom }} {{ user.nom }}</h1>
      <p class="profil-email">{{ user.email }}</p>
    </div>

    <div v-else class="profil-loading">Chargement...</div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { API_BASE } from '../../api/client.js'

const route = useRoute()
const user  = ref(null)

async function charger() {
  const res  = await fetch(`${API_BASE}/site/profil/${route.params.id}`)
  const data = await res.json()
  user.value = data.user
}

onMounted(() => charger())
</script>

<style scoped>
.profil-page {
  min-height: 60vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 60px 24px;
}

.profil-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  gap: 14px;
  background: white;
  border: 1px solid #e8edf4;
  border-radius: 22px;
  padding: 48px 40px;
  box-shadow: 0 12px 40px rgba(15, 23, 42, 0.08);
  max-width: 380px;
  width: 100%;
}

.profil-avatar {
  width: 84px;
  height: 84px;
  border-radius: 50%;
  background: linear-gradient(135deg, #1a6fc4, #0d9488);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 32px;
  font-weight: 800;
  box-shadow: 0 8px 22px rgba(26, 111, 196, 0.35);
}

.profil-nom {
  font-size: 22px;
  font-weight: 800;
  color: #0f172a;
  margin: 4px 0 0;
}

.profil-email {
  font-size: 14px;
  color: #64748b;
  margin: 0;
}

.profil-loading {
  color: #94a3b8;
  font-size: 14px;
}
</style>
