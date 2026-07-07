<?php

namespace Tests\Feature;

use App\Models\Annuaire;
use App\Models\Entreprise;
use App\Models\User;
use App\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AnnuaireFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_annuaire_only_returns_available_services(): void
    {
        $entreprise = $this->createEntreprise('Alpha', 'alpha@example.test');

        Annuaire::create([
            'entreprise_id' => $entreprise->id,
            'service' => 'Transport urgent',
            'description' => 'Disponible 24h/24',
            'disponible' => true,
        ]);

        Annuaire::create([
            'entreprise_id' => $entreprise->id,
            'service' => 'Service suspendu',
            'description' => 'Indisponible temporairement',
            'disponible' => false,
        ]);

        $response = $this->getJson('/api/annuaire');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['service' => 'Transport urgent'])
            ->assertJsonMissing(['service' => 'Service suspendu']);
    }

    public function test_authenticated_user_can_manage_only_own_annuaire_entries(): void
    {
        $ownerEntreprise = $this->createEntreprise('Owner', 'owner@example.test');
        $ownerUser = $this->createUser($ownerEntreprise, 'owner-user@example.test');
        $otherEntreprise = $this->createEntreprise('Other', 'other@example.test');
        $otherUser = $this->createUser($otherEntreprise, 'other-user@example.test');

        // Ensure permission exists and assign to owner only
        $perm = Permission::firstOrCreate(['nom' => 'gerer_annuaires']);
        $ownerUser->permissions()->attach($perm->id);

        Sanctum::actingAs($ownerUser);

        $createResponse = $this->postJson('/api/annuaire', [
            'service' => 'Imagerie mobile',
            'description' => 'Equipe mobile',
            'disponible' => true,
        ]);

        $createResponse->assertCreated()
            ->assertJsonFragment(['service' => 'Imagerie mobile']);

        $service = Annuaire::firstOrFail();

        $listResponse = $this->getJson('/api/annuaire/mes-services');

        $listResponse->assertOk()
            ->assertJsonCount(1)
            ->assertJsonFragment(['service' => 'Imagerie mobile']);

        Sanctum::actingAs($otherUser);

        $otherListResponse = $this->getJson('/api/annuaire/mes-services');
        $otherListResponse->assertOk()->assertJsonCount(0);
    }

    private function createEntreprise(string $nom, string $email): Entreprise
    {
        return Entreprise::create([
            'nom' => $nom,
            'email' => $email,
            'telephone' => '70000000',
            'adresse' => 'Centre ville',
            'ville' => 'Ouagadougou',
            'type' => 'autre',
            'description' => 'Entreprise de test',
            'statut' => 'actif',
        ]);
    }

    private function createUser(Entreprise $entreprise, string $email): User
    {
        return User::create([
            'entreprise_id' => $entreprise->id,
            'nom' => 'Test',
            'prenom' => 'User',
            'email' => $email,
            'password' => bcrypt('password'),
            'actif' => true,
        ]);
    }
}
