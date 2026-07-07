<template>
  <div>
    <h1 class="text-2xl font-bold mb-6 text-gray-800">Publications récentes</h1>

    <!-- Chargement -->
    <div v-if="loading" class="text-center text-gray-400 py-10">
      Chargement...
    </div>

    <!-- Liste publications -->
    <div v-else>
      <div
        v-for="pub in publications"
        :key="pub.id"
        class="bg-white rounded-xl shadow p-5 mb-4 hover:shadow-md transition"
      >
        <!-- Auteur -->
        <div class="flex items-center gap-3 mb-3">
          <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center font-bold text-blue-600">
            {{ pub.user.name.charAt(0).toUpperCase() }}
          </div>
          <div>
            <router-link
              :to="`/profil/${pub.user.id}`"
              class="font-semibold text-gray-800 hover:text-blue-600"
            >
              {{ pub.user.name }}
            </router-link>
            <p class="text-xs text-gray-400">{{ formatDate(pub.created_at) }}</p>
          </div>
        </div>

        <!-- Contenu -->
        <router-link :to="`/publication/${pub.id}`">
          <h2 class="text-lg font-semibold text-gray-800 mb-1 hover:text-blue-600">
            {{ pub.titre }}
          </h2>
          <p class="text-gray-600 text-sm">{{ pub.extrait }}</p>
          <img
            v-if="pub.image_url"
            :src="pub.image_url"
            class="mt-3 rounded-lg w-full object-cover max-h-64"
          />
        </router-link>

        <!-- Footer -->
        <div class="mt-3 text-xs text-gray-400">
          Lire la suite →
        </div>
      </div>

      <!-- Pagination -->
      <div class="flex justify-between mt-6" v-if="meta">
        <button
          @click="charger(meta.current_page - 1)"
          :disabled="meta.current_page === 1"
          class="px-4 py-2 bg-white border rounded disabled:opacity-40"
        >
          ← Précédent
        </button>
        <span class="text-sm text-gray-500 self-center">
          Page {{ meta.current_page }} / {{ meta.last_page }}
        </span>
        <button
          @click="charger(meta.current_page + 1)"
          :disabled="meta.current_page === meta.last_page"
          class="px-4 py-2 bg-white border rounded disabled:opacity-40"
        >
          Suivant →
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const publications = ref([])
const meta         = ref(null)
const loading      = ref(true)

async function charger(page = 1) {
  loading.value = true
  const res = await fetch(`/api/site/publications?page=${page}`)
  const data = await res.json()
  publications.value = data.data
  meta.value         = data
  loading.value      = false
}

function formatDate(date) {
  return new Date(date).toLocaleDateString('fr-FR', {
    day: 'numeric', month: 'long', year: 'numeric'
  })
}

onMounted(() => charger())
</script>
