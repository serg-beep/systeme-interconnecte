<?php

namespace Database\Seeders;

use App\Models\Publication;
use App\Models\User;
use Illuminate\Database\Seeder;

class PublicationSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        if ($users->isEmpty()) return;

        $publications = [
            [
                'titre'            => 'Campagne de vaccination contre la méningite — Résultats 2026',
                'type_publication' => 'Actualité',
                'contenu'          => "Dans le cadre de la campagne nationale de vaccination contre la méningite, notre établissement a vacciné plus de 2 500 personnes en une semaine.\n\nLa méningite bactérienne est une maladie grave qui peut être mortelle en l'absence de traitement rapide. La vaccination reste le moyen le plus efficace de prévention.\n\nNous encourageons toutes les familles à se rendre dans les centres de santé agréés pour se faire vacciner. La vaccination est gratuite pour les enfants de moins de 5 ans.",
                'statut'           => 'publie',
                'visible_site'     => true,
            ],
            [
                'titre'            => 'Nouveau service de téléconsultation médicale disponible',
                'type_publication' => 'Offre de service',
                'contenu'          => "Nous avons le plaisir d'annoncer le lancement de notre service de téléconsultation médicale.\n\nCe service permet aux patients de consulter un médecin à distance via une application mobile sécurisée. Disponible 7j/7 de 8h à 20h.\n\nTarifs : 2 000 FCFA la consultation.\nPrise en charge : ordonnances électroniques valables dans toutes les pharmacies partenaires.\n\nContactez-nous pour plus d'informations.",
                'statut'           => 'publie',
                'visible_site'     => true,
            ],
            [
                'titre'            => 'Journée portes ouvertes — Dépistage gratuit du diabète',
                'type_publication' => 'Événement',
                'contenu'          => "À l'occasion de la Journée Mondiale du Diabète, notre clinique organise une journée de dépistage gratuit.\n\nDate : 14 novembre 2026\nLieu : Notre établissement, Ouagadougou\nHoraires : 8h00 - 17h00\n\nServices proposés :\n- Glycémie à jeun\n- Mesure de la pression artérielle\n- Consultation avec un diabétologue\n- Conseils nutritionnels\n\nVenez nombreux ! Aucun rendez-vous nécessaire.",
                'statut'           => 'publie',
                'visible_site'     => true,
            ],
            [
                'titre'            => 'Formation en premiers secours — Inscription ouverte',
                'type_publication' => 'Formation',
                'contenu'          => "Notre centre de santé propose une formation aux gestes de premiers secours ouverte à tous.\n\nObjectifs :\n- Apprendre à réagir face à une urgence\n- Maîtriser le massage cardiaque et la défibrillation\n- Gérer les hémorragies et les fractures\n\nDurée : 2 jours (16 heures)\nCoût : 25 000 FCFA\nCertification : attestation de formation délivrée à l'issue\n\nProchaine session : 20-21 juillet 2026. Places limitées à 20 participants.",
                'statut'           => 'publie',
                'visible_site'     => true,
            ],
            [
                'titre'            => 'Résultats de notre audit qualité 2025 — Excellence certifiée',
                'type_publication' => 'Rapport',
                'contenu'          => "Nous avons le plaisir de vous informer que notre établissement a obtenu la certification qualité ISO 9001 suite à notre audit annuel.\n\nPoints forts relevés par les auditeurs :\n- Taux de satisfaction patients : 94%\n- Délai moyen de prise en charge aux urgences : 12 minutes\n- Zéro infection nosocomiale sur l'année 2025\n- Personnel formé et qualifié à 100%\n\nNous remercions l'ensemble de notre personnel pour leur engagement quotidien au service des patients.",
                'statut'           => 'publie',
                'visible_site'     => true,
            ],
            [
                'titre'            => 'Nouveaux équipements d\'imagerie médicale — Scanner de dernière génération',
                'type_publication' => 'Annonce',
                'contenu'          => "Notre établissement vient de se doter d'un scanner de dernière génération, le GE Revolution CT.\n\nCet équipement permet :\n- Des examens plus rapides (30 secondes pour un scanner thoracique)\n- Une qualité d'image supérieure\n- Une dose de rayonnement réduite de 40%\n- Des reconstructions 3D haute définition\n\nCet investissement de 450 millions FCFA témoigne de notre engagement pour offrir les meilleurs soins à nos patients.\n\nPrise de rendez-vous : Appelez le +226 XX XX XX XX ou venez directement à notre accueil.",
                'statut'           => 'publie',
                'visible_site'     => true,
            ],
            [
                'titre'            => 'Programme de suivi maternel — Consultations prénatales gratuites',
                'type_publication' => 'Offre de service',
                'contenu'          => "Dans le cadre de notre programme de santé maternelle, nous offrons des consultations prénatales gratuites pour les femmes enceintes à faibles revenus.\n\nCe programme inclut :\n- 8 consultations prénatales gratuites\n- Échographies obstétricales\n- Bilan biologique complet\n- Supplémentation en fer et acide folique\n- Suivi nutritionnel\n\nConditions : présentation d'un justificatif de ressources.\nInscriptions : du lundi au vendredi de 8h à 15h.",
                'statut'           => 'publie',
                'visible_site'     => true,
            ],
            [
                'titre'            => 'Lutte contre le paludisme — Distribution de moustiquaires imprégnées',
                'type_publication' => 'Actualité',
                'contenu'          => "En partenariat avec le Ministère de la Santé du Burkina Faso, nous participons à la campagne de distribution de moustiquaires imprégnées d'insecticide.\n\nLe paludisme reste la première cause de mortalité infantile au Burkina Faso. L'utilisation de moustiquaires imprégnées réduit de 50% le risque d'infection.\n\nDistribution gratuite pour :\n- Les enfants de moins de 5 ans\n- Les femmes enceintes\n\nApportez votre carnet de santé. Distribution les mardis et jeudis de 8h à 12h.",
                'statut'           => 'publie',
                'visible_site'     => true,
            ],
        ];

        foreach ($publications as $i => $data) {
            $user = $users[$i % $users->count()];
            Publication::create(array_merge($data, ['user_id' => $user->id]));
        }
    }
}
