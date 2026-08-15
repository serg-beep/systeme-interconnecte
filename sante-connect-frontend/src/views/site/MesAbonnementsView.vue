<template>
  <div class="abonnements-page">
    <div class="page-header">
      <h1 class="page-titre">Mes abonnements</h1>
      <p class="page-sous-titre">Les professionnels et établissements que vous suivez.</p>
    </div>

    <div v-if="loading" class="etat-vide">Chargement...</div>

    <template v-else>
      <section class="groupe" v-if="personnes.length">
        <h2 class="groupe-titre">Personnes ({{ personnes.length }})</h2>
        <div class="liste">
          <div v-for="a in personnes" :key="'user-'+a.id" class="carte">
            <router-link :to="`/profil-public/${a.followable.id}`" class="carte-profil">
              <div class="avatar">{{ a.followable.prenom?.charAt(0)?.toUpperCase() || '?' }}</div>
              <div>
                <div class="nom">{{ a.followable.prenom }} {{ a.followable.nom }}</div>
                <div class="souslabel">Voir le profil</div>
              </div>
            </router-link>
            <FollowButton type="user" :id="a.followable.id" :suivi="true" @change="retirer(a.id)" />
          </div>
        </div>
      </section>

      <section class="groupe" v-if="etablissements.length">
        <h2 class="groupe-titre">Établissements ({{ etablissements.length }})</h2>
        <div class="liste">
          <div v-for="a in etablissements" :key="'ent-'+a.id" class="carte">
            <div class="carte-profil">
              <div class="avatar avatar-ent">{{ a.followable.nom?.charAt(0)?.toUpperCase() || '?' }}</div>
              <div>
                <div class="nom">{{ a.followable.nom }}</div>
                <div class="souslabel">{{ a.followable.type }} · {{ a.followable.ville }}</div>
              </div>
            </div>
            <FollowButton type="entreprise" :id="a.followable.id" :suivi="true" @change="retirer(a.id)" />
          </div>
        </div>
      </section>

      <div v-if="!personnes.length && !etablissements.length" class="etat-vide">
        <p>Vous ne suivez encore personne.</p>
        <router-link to="/annuaire-public" class="btn-decouvrir">Découvrir l'annuaire</router-link>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '../../api/client.js'
import FollowButton from '../../components/site/FollowButton.vue'

const abonnements = ref([])
const loading      = ref(true)

const personnes = computed(() => abonnements.value.filter(a => a.followable_type === 'user' && a.followable))
const etablissements = computed(() => abonnements.value.filter(a => a.followable_type === 'entreprise' && a.followable))

async function charger() {
  loading.value = true
  try {
    const { data } = await api.get('/mes-abonnements')
    abonnements.value = data
  } finally {
    loading.value = false
  }
}

function retirer(abonnementId) {
  abonnements.value = abonnements.value.filter(a => a.id !== abonnementId)
}

onMounted(charger)
</script>

<style scoped>
.abonnements-page { max-width: 760px; margin: 0 auto; padding: 48px 24px 90px; }
.page-header { margin-bottom: 32px; }
.page-titre { font-size: 26px; font-weight: 900; color: #0f172a; margin: 0 0 8px; }
.page-sous-titre { font-size: 14.5px; color: #64748b; margin: 0; }

.groupe { margin-bottom: 36px; }
.groupe-titre { font-size: 15px; font-weight: 800; color: #0f172a; margin: 0 0 16px; text-transform: uppercase; letter-spacing: 0.03em; }

.liste { display: flex; flex-direction: column; gap: 12px; }
.carte {
  display: flex; align-items: center; justify-content: space-between; gap: 16px;
  background: white; border: 1px solid #e8edf4; border-radius: 14px; padding: 14px 18px;
  box-shadow: 0 2px 12px rgba(15,23,42,0.04);
}
.carte-profil { display: flex; align-items: center; gap: 12px; text-decoration: none; min-width: 0; }
.avatar {
  width: 42px; height: 42px; border-radius: 11px; flex-shrink: 0;
  background: linear-gradient(135deg, #1a6fc4, #0d9488); color: white;
  display: flex; align-items: center; justify-content: center; font-size: 15px; font-weight: 800;
}
.avatar-ent { background: linear-gradient(135deg, #0d9488, #1a6fc4); }
.nom { font-size: 14.5px; font-weight: 700; color: #0f172a; }
.souslabel { font-size: 12.5px; color: #94a3b8; text-transform: capitalize; }
.carte-profil:hover .nom { color: #1a6fc4; }

.etat-vide {
  text-align: center; padding: 60px 24px; color: #94a3b8; background: #f8fafc;
  border-radius: 16px; border: 1px solid #e8edf4;
}
.etat-vide p { margin: 0 0 16px; font-size: 14.5px; }
.btn-decouvrir {
  display: inline-flex; padding: 10px 24px; background: linear-gradient(135deg, #1a6fc4, #3b8fd8);
  border-radius: 10px; color: white; font-size: 13.5px; font-weight: 700; text-decoration: none;
}
</style>
