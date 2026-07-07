<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\{Requete, Entreprise, User};

class RequeteSeeder extends Seeder {
    public function run() {
        $labo  = Entreprise::where('type', 'laboratoire')->first();
        $user  = User::where('email', 'ibrahim@sante.bf')->first();

        $requetes = [
            [
                'entreprise_id' => $labo->id,
                'user_id'       => $user->id,
                'titre'         => 'Recherche réactifs pour analyses hématologiques',
                'description'   => 'Nous cherchons un fournisseur de réactifs pour automate hématologie. Qui peut nous aider ?',
                'type'          => 'ressource',
                'statut'        => 'ouverte',
            ],
            [
                'entreprise_id' => $labo->id,
                'user_id'       => $user->id,
                'titre'         => 'Protocole commun de partage de résultats',
                'description'   => 'Proposition de créer un protocole commun pour partager les résultats d\'analyses entre structures.',
                'type'          => 'collaboration',
                'statut'        => 'ouverte',
            ],
        ];

        foreach ($requetes as $data) {
            Requete::firstOrCreate(['titre' => $data['titre']], $data);
        }
    }
}
