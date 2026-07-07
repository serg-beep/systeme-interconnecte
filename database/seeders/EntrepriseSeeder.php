<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Entreprise;

class EntrepriseSeeder extends Seeder {
    public function run() {
        $entreprises = [
            [
                'nom'         => 'Pharmacie Centrale de Ouaga',
                'type'        => 'pharmacie',
                'adresse'     => 'Avenue Kwame Nkrumah',
                'ville'       => 'Ouagadougou',
                'telephone'   => '+226 25 30 11 00',
                'email'       => 'pharmaciecentrale@sante.bf',
                'description' => 'Pharmacie centrale de Ouagadougou',
                'statut'      => 'actif',
            ],
            [
                'nom'         => 'CHU Yalgado Ouédraogo',
                'type'        => 'hopital',
                'adresse'     => 'Avenue de la Nation',
                'ville'       => 'Ouagadougou',
                'telephone'   => '+226 25 30 66 44',
                'email'       => 'chu.yalgado@sante.bf',
                'description' => 'Centre hospitalier universitaire de référence',
                'statut'      => 'actif',
            ],
            [
                'nom'         => 'Laboratoire BioAnalyse',
                'type'        => 'laboratoire',
                'adresse'     => 'Secteur 15',
                'ville'       => 'Ouagadougou',
                'telephone'   => '+226 25 36 12 34',
                'email'       => 'bioanalyse@sante.bf',
                'description' => 'Laboratoire d\'analyses médicales',
                'statut'      => 'actif',
            ],
            [
                'nom'         => 'Clinique Sandof',
                'type'        => 'clinique',
                'adresse'     => 'Secteur 8',
                'ville'       => 'Ouagadougou',
                'telephone'   => '+226 25 34 56 78',
                'email'       => 'clinique.sandof@sante.bf',
                'description' => 'Clinique privée pluridisciplinaire',
                'statut'      => 'actif',
            ],
            [
                'nom'         => 'Hôpital Régional de Bobo',
                'type'        => 'hopital',
                'adresse'     => 'Avenue de la Liberté',
                'ville'       => 'Bobo-Dioulasso',
                'telephone'   => '+226 20 97 00 11',
                'email'       => 'hr.bobo@sante.bf',
                'description' => 'Hôpital régional des Hauts-Bassins',
                'statut'      => 'actif',
            ],
        ];

        foreach ($entreprises as $data) {
            Entreprise::firstOrCreate(['email' => $data['email']], $data);
        }
    }
}
