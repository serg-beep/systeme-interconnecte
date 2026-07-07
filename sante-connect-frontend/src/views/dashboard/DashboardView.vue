<template>
  <div class="dashboard">
    <div class="bienvenue">
      <div class="bienvenue-texte">
        <h1 class="bienvenue-titre">Bonjour, {{ auth.user?.prenom || 'Utilisateur' }}</h1>
        <p class="bienvenue-sous">{{ auth.entreprise?.nom }} - {{ dateAujourdHui }}</p>
      </div>
      <UiBadge :label="auth.entreprise?.type" tone="primary" class="bienvenue-badge" />
    </div>

    <div class="stats-grille">
      <StatCard
        :value="alertesCritiques"
        label="Alertes critiques"
        :description="`${totalAlertes} alertes au total`"
        tone="danger"
      >
        <template #icon>
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
        </template>
      </StatCard>

      <StatCard
        :value="demandesEnAttente"
        label="Demandes en attente"
        description="A traiter"
        tone="warning"
      >
        <template #icon>
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        </template>
      </StatCard>

      <StatCard
        :value="requetesOuvertes"
        label="Requetes ouvertes"
        description="En attente de reponse"
        tone="accent"
      >
        <template #icon>
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
        </template>
      </StatCard>

      <StatCard
        :value="partenairesActifs"
        label="Partenaires actifs"
        :description="`${partenairesAttente} en attente`"
        tone="primary"
      >
        <template #icon>
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </template>
      </StatCard>
    </div>

    <div class="grille-principale">
      <div class="col-gauche">
        <SectionCard title="Alertes urgentes" tone="danger">
          <template #icon>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/></svg>
          </template>
          <template #action>
            <RouterLink to="/alertes" class="voir-tout">Voir tout</RouterLink>
          </template>

          <MiniLoader v-if="chargement" />
          <EmptyState v-else-if="alertesApercu.length === 0" message="Aucune alerte urgente" />
          <div v-else class="item-list">
            <div v-for="alerte in alertesApercu" :key="alerte.id" class="list-item">
              <UiBadge :label="alerte.priorite" :tone="badgePriorite(alerte.priorite)" />
              <div class="list-item__body">
                <div class="list-item__title">{{ alerte.titre }}</div>
                <div class="list-item__meta">{{ alerte.entreprise?.nom || 'Entreprise non renseignee' }}</div>
              </div>
              <RouterLink to="/alertes" class="item-action danger">Voir</RouterLink>
            </div>
          </div>
        </SectionCard>

        <SectionCard title="Demandes a traiter" tone="warning">
          <template #icon>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/></svg>
          </template>
          <template #action>
            <RouterLink to="/demandes" class="voir-tout">Voir tout</RouterLink>
          </template>

          <MiniLoader v-if="chargement" />
          <EmptyState v-else-if="demandesApercu.length === 0" message="Aucune demande en attente" />
          <div v-else class="item-list">
            <div v-for="demande in demandesApercu" :key="demande.id" class="list-item">
              <UiBadge :label="demande.type" tone="warning" />
              <div class="list-item__body">
                <div class="list-item__title">{{ demande.titre }}</div>
                <div class="list-item__meta">De : {{ demande.entreprise_source?.nom || 'Entreprise non renseignee' }}</div>
              </div>
              <RouterLink to="/demandes" class="item-action primary">Traiter</RouterLink>
            </div>
          </div>
        </SectionCard>
      </div>

      <div class="col-droite">
        <SectionCard title="Requetes ouvertes" tone="accent">
          <template #icon>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
          </template>
          <template #action>
            <RouterLink to="/requetes" class="voir-tout">Voir tout</RouterLink>
          </template>

          <MiniLoader v-if="chargement" />
          <EmptyState v-else-if="requetesApercu.length === 0" message="Aucune requete ouverte" />
          <div v-else class="compact-list">
            <div v-for="requete in requetesApercu" :key="requete.id" class="compact-item">
              <UiBadge :label="requete.type" tone="accent" />
              <div class="list-item__body">
                <div class="list-item__title">{{ requete.titre }}</div>
                <div class="list-item__meta">{{ requete.entreprise?.nom || 'Entreprise non renseignee' }}</div>
              </div>
            </div>
          </div>
        </SectionCard>

        <SectionCard title="Mes partenaires" tone="primary">
          <template #icon>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
          </template>
          <template #action>
            <RouterLink to="/partenaires" class="voir-tout">Voir tout</RouterLink>
          </template>

          <MiniLoader v-if="chargement" />
          <EmptyState v-else-if="partenairesApercu.length === 0" icon="+" message="Aucun partenaire">
            <RouterLink to="/partenaires" class="lien-add">Ajouter</RouterLink>
          </EmptyState>
          <div v-else class="partenaires-grille">
            <div v-for="partenaire in partenairesApercu" :key="partenaire.id" class="partenaire-chip">
              <div class="chip-avatar">{{ initialePartenaire(partenaire) }}</div>
              <div class="chip-info">
                <div class="chip-nom">{{ autreEnt(partenaire).nom }}</div>
                <div class="chip-type">{{ autreEnt(partenaire).type }}</div>
              </div>
            </div>
          </div>
        </SectionCard>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, defineComponent, h, onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import api from '../../api/client.js'
import EmptyState from '../../components/ui/EmptyState.vue'
import SectionCard from '../../components/ui/SectionCard.vue'
import StatCard from '../../components/ui/StatCard.vue'
import UiBadge from '../../components/ui/UiBadge.vue'
import { useAuthStore } from '../../stores/auth.js'

const MiniLoader = defineComponent({
    name: 'MiniLoader',
    setup() {
        return () => h('div', { class: 'mini-loader' }, [
            h('div', { class: 'loader-bar' }),
        ])
    },
})

const auth = useAuthStore()
const chargement = ref(true)
const stats = ref({
    alertes_critiques: 0,
    total_alertes: 0,
    demandes_en_attente: 0,
    requetes_ouvertes: 0,
    partenaires_actifs: 0,
    partenaires_attente: 0,
})
const alertesApercu = ref([])
const demandesApercu = ref([])
const requetesApercu = ref([])
const partenairesApercu = ref([])

const dateAujourdHui = new Date().toLocaleDateString('fr-FR', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric',
})

const alertesCritiques = computed(() => stats.value.alertes_critiques)
const totalAlertes = computed(() => stats.value.total_alertes)
const demandesEnAttente = computed(() => stats.value.demandes_en_attente)
const requetesOuvertes = computed(() => stats.value.requetes_ouvertes)
const partenairesActifs = computed(() => stats.value.partenaires_actifs)
const partenairesAttente = computed(() => stats.value.partenaires_attente)

function autreEnt(partenaire) {
    const monId = auth.entreprise?.id
    return partenaire.entreprise_id === monId
        ? (partenaire.entreprise_partenaire || {})
        : (partenaire.entreprise || {})
}

function initialePartenaire(partenaire) {
    return autreEnt(partenaire).nom?.[0]?.toUpperCase() || '?'
}

function badgePriorite(priorite) {
    return priorite === 'critique' ? 'danger' : 'warning'
}

async function charger() {
    chargement.value = true
    try {
        const { data } = await api.get('/dashboard')
        stats.value = data.stats || stats.value
        alertesApercu.value = data.alertes_urgentes || []
        demandesApercu.value = data.demandes_recues || []
        requetesApercu.value = data.requetes_ouvertes || []
        partenairesApercu.value = data.partenaires_acceptes || []
    } catch (e) {
        window.dispatchEvent(new CustomEvent('app-error', { detail: e?.response?.data?.message || 'Une erreur est survenue' }))
    } finally {
        chargement.value = false
    }
}

onMounted(charger)
</script>

<style scoped>
.dashboard { display: flex; flex-direction: column; gap: 20px; width: 100%; max-width: none; }
.bienvenue { display: flex; justify-content: space-between; align-items: center; background: linear-gradient(135deg, #0f766e 0%, #155e75 58%, #1e3a8a 100%); border-radius: 18px; padding: 26px 30px; box-shadow: var(--shadow-sm); position: relative; overflow: hidden; }
.bienvenue::after { content: ''; position: absolute; inset: auto -70px -110px auto; width: 260px; height: 260px; background: rgba(255,255,255,0.12); border-radius: 50%; }
.bienvenue-texte { position: relative; z-index: 1; min-width: 0; }
.bienvenue-titre { font-size: 22px; font-weight: 700; color: white; margin: 0 0 4px; }
.bienvenue-sous { font-size: 13px; color: rgba(255,255,255,0.8); margin: 0; text-transform: capitalize; }
.bienvenue-badge { position: relative; z-index: 1; border: 1px solid rgba(255,255,255,0.3); background: rgba(255,255,255,0.2); color: white; }
.stats-grille { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; }
.grille-principale { display: grid; grid-template-columns: minmax(0, 1.9fr) minmax(0, 1fr); gap: 20px; }
.col-gauche, .col-droite { display: flex; flex-direction: column; gap: 16px; }
.voir-tout { color: var(--color-primary); font-size: 12px; font-weight: 700; text-decoration: none; white-space: nowrap; }
.voir-tout:hover { text-decoration: underline; }
.mini-loader { height: 4px; background: var(--color-surface-muted); border-radius: 999px; overflow: hidden; }
.loader-bar { height: 100%; width: 40%; background: var(--color-primary); border-radius: 999px; animation: loading 1s infinite; }
@keyframes loading { 0% { margin-left: -40%; } 100% { margin-left: 100%; } }
.item-list { display: flex; flex-direction: column; }
.list-item { display: flex; align-items: center; gap: 10px; min-height: 48px; padding: 9px 0; border-bottom: 1px solid var(--color-surface-muted); }
.list-item:last-child { border-bottom: none; }
.list-item__body { flex: 1; min-width: 0; }
.list-item__title { color: var(--color-text-primary); font-size: 13px; font-weight: 600; line-height: 1.3; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.list-item__meta { color: var(--color-text-muted); font-size: 11px; line-height: 1.35; margin-top: 2px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.item-action { flex-shrink: 0; border-radius: 7px; font-size: 11px; font-weight: 700; padding: 4px 10px; text-decoration: none; }
.item-action.danger { background: var(--color-danger-soft); color: #991b1b; }
.item-action.primary { background: var(--color-primary); color: white; }
.compact-list { display: flex; flex-direction: column; gap: 6px; }
.compact-item { display: flex; align-items: center; gap: 10px; min-height: 46px; padding: 8px; background: var(--color-surface-muted); border-radius: 10px; }
.partenaires-grille { display: flex; flex-direction: column; gap: 6px; }
.partenaire-chip { display: flex; align-items: center; gap: 10px; min-height: 48px; padding: 8px 10px; background: var(--color-surface-muted); border-radius: 10px; }
.chip-avatar { width: 32px; height: 32px; border-radius: 10px; background: linear-gradient(135deg, var(--color-primary), var(--color-accent)); color: white; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; flex-shrink: 0; }
.chip-info { min-width: 0; }
.chip-nom { color: var(--color-text-primary); font-size: 13px; font-weight: 600; line-height: 1.3; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.chip-type { color: var(--color-text-muted); font-size: 11px; line-height: 1.35; text-transform: capitalize; }
.lien-add { color: var(--color-primary); font-weight: 700; text-decoration: none; }
.lien-add:hover { text-decoration: underline; }

@media (max-width: 1180px) {
  .stats-grille { grid-template-columns: repeat(2, minmax(0, 1fr)); }
  .grille-principale { grid-template-columns: 1fr; }
}

@media (max-width: 680px) {
  .dashboard { gap: 16px; }
  .bienvenue {
    align-items: flex-start;
    flex-direction: column;
    gap: 14px;
    padding: 22px;
  }
  .bienvenue::after {
    width: 190px;
    height: 190px;
    right: -88px;
    bottom: -96px;
  }
  .bienvenue-titre { font-size: 19px; }
  .stats-grille { grid-template-columns: 1fr; }
  .list-item,
  .compact-item {
    align-items: flex-start;
  }
}
</style>
