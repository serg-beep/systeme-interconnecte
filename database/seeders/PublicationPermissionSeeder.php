<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission; // ton modèle existant

class PublicationPermissionSeeder extends Seeder
{
    public function run()
    {
        $permissions = [
            ['name' => 'gerer_publications', 'description' => 'Créer, modifier, supprimer des publications'],
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm['name']], $perm);
        }
    }
}
