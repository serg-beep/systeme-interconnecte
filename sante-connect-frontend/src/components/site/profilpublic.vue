<template>
  <div v-if="user">

    <!-- Carte profil -->
    <div class="bg-white rounded-xl shadow p-6 mb-6 flex items-center gap-4">
      <div class="w-16 h-16 rounded-full bg-blue-100 flex items-center justify-center text-2xl font-bold text-blue-600">
        {{ user.name.charAt(0).toUpperCase() }}
      </div>
      <div>
        <h1 class="text-xl font-bold text-gray-800">{{ user.name }}</h1>
        <p class="text-sm text-gray-400">{{ user.email }}</p>
      </div>
    </div>

    <!-- Publications -->
    <h2 class="text-lg font-semibold text-gray-700 mb-4">Publications</h2>

    <div
      v-for="pub in publications"
      :key="pub.id"
      class="bg-white rounded-xl shadow p-5 mb-4 hover:shadow-md transition"
    >
      <router-link :to="`/publication/${pub.id}`">
        <h3 class="font-semibold text-gray-800 hover:text-blue-600 mb-1">{{ pub.titre }}</h3>
        <p class="text-sm text-gray-500">{{ pub.extrait }}</p>
      </router-link>
      <p class="text-xs text-gray-400 mt-2">{{ formatDate(pub.created_at) }}</p>
    </div>

    <p v-if="publications.length === 0" class="text-gray-400 text-sm">
      Cet utilisateur n'a pas encore de publication.
    </p>

  </div>

  <div v-else class="text-center text-gray-400 py-10">Chargement...</div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'

const route        = useRoute()
const user         = ref(null)
const publications = ref([])

async function charger() {
  const res  = await fetch(`/api/site/profil/${route.params.id}`)
  const data = await res.json()
  user.value         = data.user
  publications.value = data.publications.data
}

function formatDate(date) {
  return new Date(date).toLocaleDateString('fr-FR', {
    day: 'numeric', month: 'long', year: 'numeric'
  })
}

onMounted(() => charger())
</script>
