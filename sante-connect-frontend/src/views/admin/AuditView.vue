<template>
  <section class="page"><header><div><h2>Audit et traçabilité</h2><p>Historique des actions enregistrées dans Mediflow.</p></div><button @click="load">Actualiser</button></header>
  <p v-if="loading">Chargement…</p><p v-else-if="logs.length === 0">Aucune action enregistrée.</p>
  <table v-else><thead><tr><th>Date</th><th>Utilisateur</th><th>Action</th><th>Élément</th><th>Adresse IP</th></tr></thead><tbody><tr v-for="log in logs" :key="log.id"><td>{{ date(log.created_at) }}</td><td>{{ log.user ? `${log.user.prenom} ${log.user.nom}` : 'Système' }}</td><td>{{ log.action }}</td><td>{{ log.table_cible || '—' }}</td><td>{{ log.ip_address || '—' }}</td></tr></tbody></table></section>
</template>
<script setup>
import { onMounted, ref } from 'vue'
import api from '../../api/client.js'
const logs = ref([]), loading = ref(true)
const date = value => value ? new Intl.DateTimeFormat('fr-FR', { dateStyle: 'short', timeStyle: 'short' }).format(new Date(value)) : '—'
async function load() { loading.value = true; try { const { data } = await api.get('/historiqueActions'); logs.value = data.data || data } finally { loading.value = false } }
onMounted(load)
</script>
<style scoped>.page { max-width:1100px; }.page header { display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:20px; }.page h2 { margin:0; }.page p { color:#64748b; }button { border:0; border-radius:8px; padding:9px 14px; background:#1D9E75; color:white; cursor:pointer; }table { width:100%; border-collapse:collapse; background:white; border:1px solid #e2e8f0; border-radius:10px; overflow:hidden; }th,td { padding:12px; border-bottom:1px solid #e2e8f0; text-align:left; font-size:13px; }th { color:#475569; background:#f8fafc; }td { color:#334155; }</style>
