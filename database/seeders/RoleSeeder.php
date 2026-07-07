<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder {
    public function run() {
        $roles = [
            ['nom' => 'super_admin',  'description' => 'Administrateur de toute la plateforme'],
            ['nom' => 'admin',        'description' => 'Administrateur d\'une entreprise'],
            ['nom' => 'gestionnaire', 'description' => 'Gère les échanges et alertes'],
            ['nom' => 'operateur',    'description' => 'Consultation uniquement'],
        ];
        foreach ($roles as $role) {
            Role::firstOrCreate(['nom' => $role['nom']], $role);
        }
    }
}
