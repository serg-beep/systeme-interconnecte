<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\{Alerte, Entreprise, User};

class AlerteSeeder extends Seeder {
    public function run() {
        $pharmacie = Entreprise::where('type', 'pharmacie')->first();
        $hopital   = Entreprise::where('type', 'hopital')->first();
        $user1     = User::where('email', 'moussa@sante.bf')->first();
        $user2     = User::where('email', 'aicha@sante.bf')->first();

        $alertes = [
            [
                'entreprise_id' => $pharmacie->id,
                'user_id'       => $user1->id,
                'titre'         => 'Rupture amoxicilline 500mg',
                'message'       => 'Nous sommes en rupture totale d\'amoxicilline 500mg. Besoin urgent d\'approvisionnement.',
                'type'          => 'rupture_stock',
                'priorite'      => 'critique',
            ],
            [
                'entreprise_id' => $hopital->id,
                'user_id'       => $user2->id,
                'titre'         => 'Urgence sang O+',
                'message'       => 'Besoin urgent de poches de sang groupe O+ pour chirurgie d\'urgence ce soir.',
                'type'          => 'urgence',
                'priorite'      => 'critique',
            ],
            [
                'entreprise_id' => $hopital->id,
                'user_id'       => $user2->id,
                'titre'         => 'Maintenance système vendredi',
                'message'       => 'Le système sera en maintenance vendredi de 22h à 2h du matin.',
                'type'          => 'information',
                'priorite'      => 'normale',
            ],
        ];

        $autresEntreprises = Entreprise::where('id', '!=', $pharmacie->id)->pluck('id');

        foreach ($alertes as $data) {
            $alerte = Alerte::firstOrCreate(['titre' => $data['titre']], $data);
            $autres = Entreprise::where('id', '!=', $data['entreprise_id'])->pluck('id');
            $alerte->entreprisesDestinaires()->syncWithoutDetaching(
                $autres->mapWithKeys(fn($id) => [$id => ['vue' => false, 'accuse_reception' => false]])->toArray()
            );
        }
    }
}
