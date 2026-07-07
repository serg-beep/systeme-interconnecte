<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\{Annuaire, Entreprise};

class AnnuaireSeeder extends Seeder {
    public function run() {
        $pharmacie = Entreprise::where('type', 'pharmacie')->first();
        $hopital   = Entreprise::where('type', 'hopital')->first();
        $labo      = Entreprise::where('type', 'laboratoire')->first();
        $clinique  = Entreprise::where('type', 'clinique')->first();

        $services = [
            ['entreprise_id' => $pharmacie->id, 'service' => 'Vente médicaments génériques',      'description' => 'Large gamme de médicaments génériques disponibles',   'disponible' => true],
            ['entreprise_id' => $pharmacie->id, 'service' => 'Préparations magistrales',          'description' => 'Préparation sur ordonnance médicale',                  'disponible' => true],
            ['entreprise_id' => $hopital->id,   'service' => 'Chirurgie générale',                'description' => 'Interventions chirurgicales programmées et urgentes',  'disponible' => true],
            ['entreprise_id' => $hopital->id,   'service' => 'Maternité et pédiatrie',            'description' => 'Soins mère-enfant 24h/24',                            'disponible' => true],
            ['entreprise_id' => $hopital->id,   'service' => 'Banque de sang',                   'description' => 'Collecte et distribution de produits sanguins',        'disponible' => true],
            ['entreprise_id' => $labo->id,      'service' => 'Analyses biologiques rapides 24h', 'description' => 'Résultats disponibles en moins de 4 heures',          'disponible' => true],
            ['entreprise_id' => $labo->id,      'service' => 'Sérologie et virologie',           'description' => 'Dépistage VIH, hépatites, paludisme et autres',       'disponible' => true],
            ['entreprise_id' => $clinique->id,  'service' => 'Consultation spécialisée',         'description' => 'Cardiologie, dermatologie, ophtalmologie',             'disponible' => true],
            ['entreprise_id' => $clinique->id,  'service' => 'Imagerie médicale',                'description' => 'Échographie, radiographie',                           'disponible' => false],
        ];

        foreach ($services as $data) {
            Annuaire::create($data);
        }
    }
}
