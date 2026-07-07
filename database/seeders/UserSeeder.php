<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Entreprise;
use App\Models\Role;

class UserSeeder extends Seeder {
    public function run(): void {
        $pharmacie = Entreprise::where('type', 'pharmacie')->first();
        $hopital   = Entreprise::where('type', 'hopital')->first();
        $labo      = Entreprise::where('type', 'laboratoire')->first();
        $clinique  = Entreprise::where('type', 'clinique')->first();

        $superAdmin   = Role::where('nom', 'super_admin')->first();
        $admin        = Role::where('nom', 'admin')->first();
        $gestionnaire = Role::where('nom', 'gestionnaire')->first();
        $operateur    = Role::where('nom', 'operateur')->first();

        $users = [
            [
                'data' => [
                    'entreprise_id' => $pharmacie?->id,
                    'nom'           => 'Ouédraogo',
                    'prenom'        => 'Moussa',
                    'email'         => 'moussa@sante.bf',
                    'password'      => Hash::make('password'),
                    'telephone'     => '+226 70 11 22 33',
                    'poste'         => 'Directeur',
                    'actif'         => true,
                ],
                'role' => $superAdmin,
            ],
            [
                'data' => [
                    'entreprise_id' => $hopital?->id,
                    'nom'           => 'Sawadogo',
                    'prenom'        => 'Aïcha',
                    'email'         => 'aicha@sante.bf',
                    'password'      => Hash::make('password'),
                    'telephone'     => '+226 70 44 55 66',
                    'poste'         => 'Administratrice',
                    'actif'         => true,
                ],
                'role' => $admin,
            ],
            [
                'data' => [
                    'entreprise_id' => $labo?->id,
                    'nom'           => 'Compaoré',
                    'prenom'        => 'Ibrahim',
                    'email'         => 'ibrahim@sante.bf',
                    'password'      => Hash::make('password'),
                    'telephone'     => '+226 76 77 88 99',
                    'poste'         => 'Gestionnaire',
                    'actif'         => true,
                ],
                'role' => $gestionnaire,
            ],
            [
                'data' => [
                    'entreprise_id' => $clinique?->id,
                    'nom'           => 'Traoré',
                    'prenom'        => 'Fatimata',
                    'email'         => 'fatimata@sante.bf',
                    'password'      => Hash::make('password'),
                    'telephone'     => '+226 65 12 34 56',
                    'poste'         => 'Opératrice',
                    'actif'         => true,
                ],
                'role' => $operateur,
            ],
        ];

        foreach ($users as $item) {
            $user = User::firstOrCreate(
                ['email' => $item['data']['email']],
                $item['data']
            );
            if ($item['role']) {
                $user->roles()->sync([$item['role']->id]);
            }
        }
    }
}
