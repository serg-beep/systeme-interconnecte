<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\{Partenaire, Entreprise};

class PartenaireSeeder extends Seeder {
    public function run() {
        $pharmacie = Entreprise::where('type', 'pharmacie')->first();
        $hopital   = Entreprise::where('type', 'hopital')->first();
        $labo      = Entreprise::where('type', 'laboratoire')->first();
        $clinique  = Entreprise::where('type', 'clinique')->first();

        $partenariats = [
            ['entreprise_id' => $pharmacie->id, 'entreprise_partenaire_id' => $hopital->id,  'statut' => 'accepte', 'date_partenariat' => '2024-01-10'],
            ['entreprise_id' => $hopital->id,   'entreprise_partenaire_id' => $labo->id,     'statut' => 'accepte', 'date_partenariat' => '2024-02-15'],
            ['entreprise_id' => $clinique->id,  'entreprise_partenaire_id' => $labo->id,     'statut' => 'en_attente', 'date_partenariat' => null],
            ['entreprise_id' => $pharmacie->id, 'entreprise_partenaire_id' => $clinique->id, 'statut' => 'accepte', 'date_partenariat' => '2024-03-01'],
        ];

        foreach ($partenariats as $data) {
            Partenaire::firstOrCreate([
                'entreprise_id'           => $data['entreprise_id'],
                'entreprise_partenaire_id' => $data['entreprise_partenaire_id'],
            ], $data);
        }
    }
}
