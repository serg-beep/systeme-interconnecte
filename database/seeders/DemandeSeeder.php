<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\{Demande, Entreprise, User};

class DemandeSeeder extends Seeder {
    public function run() {
        $pharmacie = Entreprise::where('type', 'pharmacie')->first();
        $hopital   = Entreprise::where('type', 'hopital')->first();
        $labo      = Entreprise::where('type', 'laboratoire')->first();
        $user1     = User::where('email', 'moussa@sante.bf')->first();
        $user2     = User::where('email', 'aicha@sante.bf')->first();

        $demandes = [
            [
                'entreprise_source_id' => $hopital->id,
                'entreprise_cible_id'  => $pharmacie->id,
                'user_id'              => $user2->id,
                'titre'                => 'Demande paracétamol 500mg x 1000',
                'description'          => 'Besoin urgent de 1000 comprimés paracétamol 500mg pour nos services.',
                'type'                 => 'stock',
                'statut'               => 'en_attente',
            ],
            [
                'entreprise_source_id' => $hopital->id,
                'entreprise_cible_id'  => $labo->id,
                'user_id'              => $user2->id,
                'titre'                => 'Analyses pour 30 patients hospitalisés',
                'description'          => 'Demande de prise en charge des analyses biologiques pour 30 patients.',
                'type'                 => 'service',
                'statut'               => 'acceptee',
            ],
            [
                'entreprise_source_id' => $pharmacie->id,
                'entreprise_cible_id'  => $hopital->id,
                'user_id'              => $user1->id,
                'titre'                => 'Partenariat approvisionnement mensuel',
                'description'          => 'Proposition de partenariat pour approvisionnement régulier en médicaments.',
                'type'                 => 'partenariat',
                'statut'               => 'en_attente',
            ],
        ];

        foreach ($demandes as $data) {
            Demande::firstOrCreate(['titre' => $data['titre']], $data);
        }
    }
}
