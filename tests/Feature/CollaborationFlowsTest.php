<?php

namespace Tests\Feature;

use App\Models\Alerte;
use App\Models\Demande;
use App\Models\Entreprise;
use App\Models\Historique_Status;
use App\Models\Notification;
use App\Models\Reponse;
use App\Models\Requete;
use App\Models\User;
use App\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CollaborationFlowsTest extends TestCase
{
    use RefreshDatabase;

    public function test_alert_is_broadcast_to_other_enterprises_and_can_be_acknowledged(): void
    {
        $sourceEntreprise = $this->createEntreprise('Source');
        $sourceUser = $this->createUser($sourceEntreprise, 'source@example.test');
        $targetEntreprise = $this->createEntreprise('Target', 'target@example.test');
        $targetUser = $this->createUser($targetEntreprise, 'target-user@example.test');

        // create and attach permissions
        $permCreateAlert = Permission::firstOrCreate(['nom' => 'creer_alertes']);
        $sourceUser->permissions()->attach($permCreateAlert->id);
        $permVoirAlert = Permission::firstOrCreate(['nom' => 'voir_alertes']);
        $targetUser->permissions()->attach($permVoirAlert->id);

        Sanctum::actingAs($sourceUser);

        $response = $this->postJson('/api/alertes', [
            'titre' => 'Rupture oxygene',
            'message' => 'Stock critique sur 24h.',
            'type' => 'urgence',
            'priorite' => 'critique',
        ]);

        $response->assertCreated();

        $alerte = Alerte::firstOrFail();

        $this->assertDatabaseHas('alerte_entreprise', [
            'alerte_id' => $alerte->id,
            'entreprise_id' => $targetEntreprise->id,
            'accuse_reception' => false,
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $targetUser->id,
            'type' => 'alerte',
            'titre' => 'Nouvelle alerte : '.$alerte->titre,
            'lien' => '/alertes/'.$alerte->id,
        ]);

        Sanctum::actingAs($targetUser);

        $ackResponse = $this->postJson('/api/alertes/'.$alerte->id.'/accuser');

        $ackResponse->assertOk();

        $this->assertDatabaseHas('alerte_entreprise', [
            'alerte_id' => $alerte->id,
            'entreprise_id' => $targetEntreprise->id,
            'accuse_reception' => true,
            'vue' => true,
        ]);
    }

    public function test_requete_response_notifies_owner_and_closing_creates_history(): void
    {
        $ownerEntreprise = $this->createEntreprise('Owner');
        $ownerUser = $this->createUser($ownerEntreprise, 'owner@example.test');
        $responderEntreprise = $this->createEntreprise('Responder', 'responder@example.test');
        $responderUser = $this->createUser($responderEntreprise, 'responder-user@example.test');

        // attach permissions for creating requetes and reponses
        $permCreerReq = Permission::firstOrCreate(['nom' => 'creer_requetes']);
        $ownerUser->permissions()->attach($permCreerReq->id);
        $permCreerResp = Permission::firstOrCreate(['nom' => 'creer_reponses']);
        $responderUser->permissions()->attach($permCreerResp->id);

        Sanctum::actingAs($ownerUser);

        $createResponse = $this->postJson('/api/requetes', [
            'titre' => 'Besoin reactifs',
            'description' => 'Recherche reactifs laboratoire.',
            'type' => 'ressource',
        ]);

        $createResponse->assertCreated();

        $requete = Requete::firstOrFail();

        Sanctum::actingAs($responderUser);

        $replyResponse = $this->postJson('/api/reponses', [
            'requete_id' => $requete->id,
            'contenu' => 'Nous pouvons fournir une partie du stock.',
        ]);

        $replyResponse->assertCreated();

        $this->assertDatabaseHas('reponses', [
            'requete_id' => $requete->id,
            'user_id' => $responderUser->id,
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $ownerUser->id,
            'type' => 'reponse',
            'lien' => '/requetes/'.$requete->id,
        ]);

        Sanctum::actingAs($ownerUser);

        $closeResponse = $this->postJson('/api/requetes/'.$requete->id.'/fermer');

        $closeResponse->assertOk();

        $this->assertDatabaseHas('requetes', [
            'id' => $requete->id,
            'statut' => 'fermee',
        ]);

        $this->assertDatabaseHas('historique_statuts', [
            'requete_id' => $requete->id,
            'user_id' => $ownerUser->id,
            'nouveau_statut' => 'fermee',
        ]);
    }

    public function test_response_requires_a_target_resource(): void
    {
        $entreprise = $this->createEntreprise('Solo');
        $user = $this->createUser($entreprise, 'solo@example.test');

        // ensure user can create responses
        $permCreerResp = Permission::firstOrCreate(['nom' => 'creer_reponses']);
        $user->permissions()->attach($permCreerResp->id);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/reponses', [
            'contenu' => 'Message sans cible',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Une reponse doit etre liee a une requete ou une demande.',
            ]);
    }

    private function createEntreprise(string $nom, string $email = null): Entreprise
    {
        return Entreprise::create([
            'nom' => $nom,
            'email' => $email ?? strtolower($nom).'@example.test',
            'telephone' => '70000000',
            'adresse' => 'Ouagadougou',
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
