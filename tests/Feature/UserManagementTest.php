<?php

namespace Tests\Feature;

use App\Models\Entreprise;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_gestionnaire_can_create_user(): void
    {
        $entreprise = Entreprise::create([
            'nom' => 'Clinique Test',
            'email' => 'clinique@example.test',
            'telephone' => '70000000',
            'adresse' => 'Ouaga',
            'ville' => 'Ouagadougou',
            'type' => 'clinique',
            'description' => 'Entreprise de test',
            'statut' => 'actif',
        ]);

        $role = Role::firstOrCreate(
            ['nom' => 'gestionnaire'],
            ['description' => 'Gestionnaire']
        );

        $manager = User::create([
            'entreprise_id' => $entreprise->id,
            'nom' => 'Manager',
            'prenom' => 'Test',
            'email' => 'manager@example.test',
            'password' => bcrypt('password123'),
            'actif' => true,
        ]);
        $manager->roles()->attach($role->id);

        Sanctum::actingAs($manager);

        $response = $this->postJson('/api/users', [
            'nom' => 'Nouveau',
            'prenom' => 'User',
            'email' => 'newuser@example.test',
            'password' => 'password123',
            'telephone' => '70000001',
            'poste' => 'Infirmier',
            'role_id' => $role->id,
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('users', ['email' => 'newuser@example.test']);
    }
}
