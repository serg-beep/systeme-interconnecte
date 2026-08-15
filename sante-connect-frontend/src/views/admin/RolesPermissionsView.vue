<template>
  <section class="page">
    <header><div><h2>Rôles et permissions</h2><p>Définissez les droits accordés par chaque rôle.</p></div><button @click="ouvrir()">Nouveau rôle</button></header>
    <p v-if="loading">Chargement…</p>
    <div v-else class="roles">
      <article v-for="role in roles" :key="role.id" class="role-card">
        <div><h3>{{ label(role.nom) }}</h3><p>{{ role.description || 'Aucune description' }}</p></div>
        <button class="secondary" @click="ouvrir(role)">Modifier</button>
        <small>{{ role.permissions?.length || 0 }} permission(s)</small>
      </article>
    </div>
    <div v-if="modal" class="overlay" @click.self="modal = false"><form class="modal" @submit.prevent="save">
      <h3>{{ editing ? 'Modifier le rôle' : 'Créer un rôle' }}</h3>
      <label>Nom<input v-model="form.nom" required placeholder="ex. responsable" /></label>
      <label>Description<input v-model="form.description" /></label>
      <fieldset><legend>Permissions</legend><label v-for="permission in permissions" :key="permission.id" class="check"><input v-model="form.permissions" type="checkbox" :value="permission.id" />{{ label(permission.nom) }}</label></fieldset>
      <p v-if="error" class="error">{{ error }}</p><footer><button type="button" class="secondary" @click="modal = false">Annuler</button><button :disabled="saving">Enregistrer</button></footer>
    </form></div>
  </section>
</template>
<script setup>
import { onMounted, ref } from 'vue'
import api from '../../api/client.js'
const roles = ref([]), permissions = ref([]), loading = ref(true), modal = ref(false), editing = ref(null), saving = ref(false), error = ref('')
const form = ref({ nom: '', description: '', permissions: [] })
const label = value => (value || '').replaceAll('_', ' ')
async function load() { loading.value = true; try { const [r, p] = await Promise.all([api.get('/roles'), api.get('/permissions')]); roles.value = r.data.data || r.data; permissions.value = p.data.data || p.data } finally { loading.value = false } }
function ouvrir(role = null) { editing.value = role; form.value = role ? { nom: role.nom, description: role.description || '', permissions: (role.permissions || []).map(p => p.id) } : { nom: '', description: '', permissions: [] }; error.value = ''; modal.value = true }
async function save() { saving.value = true; error.value = ''; try { const request = editing.value ? api.put(`/roles/${editing.value.id}`, form.value) : api.post('/roles', form.value); await request; modal.value = false; await load() } catch (e) { error.value = e.response?.data?.message || 'Enregistrement impossible' } finally { saving.value = false } }
onMounted(load)
</script>
<style scoped>
.page { max-width: 1000px; }.page header, footer { display:flex; justify-content:space-between; align-items:center; gap:12px; }.page h2,.page h3 { margin:0; }.page p { color:#64748b; }button { border:0; border-radius:8px; padding:9px 14px; background:#1D9E75; color:white; cursor:pointer; }.secondary { background:white; color:#334155; border:1px solid #cbd5e1; }.roles { display:grid; grid-template-columns:repeat(auto-fit,minmax(250px,1fr)); gap:14px; margin-top:20px; }.role-card { background:white; border:1px solid #e2e8f0; border-radius:12px; padding:16px; display:grid; gap:12px; }.role-card small { color:#0f766e; }.overlay { position:fixed; inset:0; display:grid; place-items:center; background:#0007; z-index:100; padding:16px; }.modal { background:white; border-radius:12px; padding:22px; width:min(720px,100%); max-height:90vh; overflow:auto; display:grid; gap:14px; }.modal label { display:grid; gap:5px; color:#334155; }.modal input { border:1px solid #cbd5e1; border-radius:7px; padding:9px; }fieldset { border:1px solid #e2e8f0; border-radius:8px; display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:8px; }.check { display:flex !important; align-items:center; gap:7px; text-transform:capitalize; }.error { color:#b91c1c; }
</style>
