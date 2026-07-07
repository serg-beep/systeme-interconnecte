<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder {
    public function run() {
        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
            EntrepriseSeeder::class,
            UserSeeder::class,
            PartenaireSeeder::class,
            AlerteSeeder::class,
            RequeteSeeder::class,
            DemandeSeeder::class,
            ConversationSeeder::class,
            AnnuaireSeeder::class,
            PublicationSeeder::class,
        ]);
    }
}
