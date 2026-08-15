<?php

namespace Tests\Feature;

use App\Models\Abonnement;
use App\Models\Annuaire;
use App\Models\AvisService;
use App\Models\Commentaire;
use App\Models\Entreprise;
use App\Models\Like;
use App\Models\Notification;
use App\Models\Permission;
use App\Models\Publication;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReseauSocialTest extends TestCase
{
    use RefreshDatabase;

    // ── 2.0 — Comptes particuliers ──────────────────────────

    public function test_particulier_can_register_without_entreprise(): void
    {
        Role::firstOrCreate(['nom' => 'membre']);

        $response = $this->postJson('/api/auth/register-particulier', [
            'nom' => 'Sawadogo',
            'prenom' => 'Awa',
            'email' => 'awa@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertCreated()
            ->assertJsonPath('user.entreprise_id', null);

        $this->assertDatabaseHas('users', [
            'email' => 'awa@example.test',
            'entreprise_id' => null,
        ]);

        $user = User::where('email', 'awa@example.test')->firstOrFail();
        $this->assertTrue($user->hasRole('membre'));
    }

    public function test_register_particulier_rejects_duplicate_email(): void
    {
        Role::firstOrCreate(['nom' => 'membre']);
        $this->createParticulier('deja@example.test');

        $response = $this->postJson('/api/auth/register-particulier', [
            'nom' => 'Test',
            'prenom' => 'Doublon',
            'email' => 'deja@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422);
    }

    // ── 2.1 — Likes ──────────────────────────────────────────

    public function test_authenticated_user_can_like_and_unlike_a_publication(): void
    {
        $auteur = $this->createParticulier('auteur@example.test');
        $publication = $this->createPublication($auteur);
        $liker = $this->createParticulier('liker@example.test');

        Sanctum::actingAs($liker);

        $likeResponse = $this->postJson('/api/likes', [
            'likeable_type' => 'publication',
            'likeable_id' => $publication->id,
        ]);

        $likeResponse->assertOk()->assertJson(['liked' => true, 'total' => 1]);
        $this->assertDatabaseHas('likes', [
            'user_id' => $liker->id,
            'likeable_id' => $publication->id,
            'likeable_type' => 'publication',
        ]);

        $unlikeResponse = $this->postJson('/api/likes', [
            'likeable_type' => 'publication',
            'likeable_id' => $publication->id,
        ]);

        $unlikeResponse->assertOk()->assertJson(['liked' => false, 'total' => 0]);
        $this->assertDatabaseMissing('likes', [
            'user_id' => $liker->id,
            'likeable_id' => $publication->id,
            'likeable_type' => 'publication',
        ]);
    }

    public function test_liking_requires_authentication(): void
    {
        $auteur = $this->createParticulier('auteur2@example.test');
        $publication = $this->createPublication($auteur);

        $response = $this->postJson('/api/likes', [
            'likeable_type' => 'publication',
            'likeable_id' => $publication->id,
        ]);

        $response->assertStatus(401);
    }

    // ── 2.2 — Abonnements ────────────────────────────────────

    public function test_authenticated_user_can_follow_and_unfollow_another_user(): void
    {
        $auteur = $this->createParticulier('suivi@example.test');
        $follower = $this->createParticulier('follower@example.test');

        Sanctum::actingAs($follower);

        $followResponse = $this->postJson('/api/abonnements', [
            'followable_type' => 'user',
            'followable_id' => $auteur->id,
        ]);

        $followResponse->assertOk()->assertJson(['suivi' => true, 'total' => 1]);
        $this->assertDatabaseHas('abonnements', [
            'follower_id' => $follower->id,
            'followable_id' => $auteur->id,
            'followable_type' => 'user',
        ]);

        $unfollowResponse = $this->postJson('/api/abonnements', [
            'followable_type' => 'user',
            'followable_id' => $auteur->id,
        ]);

        $unfollowResponse->assertOk()->assertJson(['suivi' => false, 'total' => 0]);
    }

    public function test_user_cannot_follow_themselves(): void
    {
        $user = $this->createParticulier('solo-follow@example.test');

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/abonnements', [
            'followable_type' => 'user',
            'followable_id' => $user->id,
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseMissing('abonnements', [
            'follower_id' => $user->id,
            'followable_id' => $user->id,
            'followable_type' => 'user',
        ]);
    }

    // ── 2.3 — Avis publics sur les services ─────────────────

    public function test_authenticated_user_can_submit_avis_on_service(): void
    {
        $entreprise = $this->createEntreprise('CliniqueTest');
        $service = $this->createAnnuaire($entreprise);
        $client = $this->createParticulier('avis-user@example.test');

        Sanctum::actingAs($client);

        $response = $this->postJson("/api/annuaire/{$service->id}/avis", [
            'note' => 4,
            'commentaire' => 'Bon accueil.',
        ]);

        $response->assertCreated();

        $this->assertDatabaseHas('avis_services', [
            'user_id' => $client->id,
            'annuaire_id' => $service->id,
            'note' => 4,
            'statut' => 'approuve',
        ]);
    }

    public function test_submitting_second_avis_updates_existing_one_instead_of_duplicating(): void
    {
        $entreprise = $this->createEntreprise('CliniqueBis');
        $service = $this->createAnnuaire($entreprise);
        $client = $this->createParticulier('avis-repeat@example.test');

        Sanctum::actingAs($client);

        $this->postJson("/api/annuaire/{$service->id}/avis", ['note' => 2])->assertCreated();
        $this->postJson("/api/annuaire/{$service->id}/avis", ['note' => 5])->assertCreated();

        $this->assertEquals(1, AvisService::where('user_id', $client->id)->where('annuaire_id', $service->id)->count());
        $this->assertDatabaseHas('avis_services', [
            'user_id' => $client->id,
            'annuaire_id' => $service->id,
            'note' => 5,
        ]);
    }

    public function test_avis_requires_authentication(): void
    {
        $entreprise = $this->createEntreprise('CliniqueAnonyme');
        $service = $this->createAnnuaire($entreprise);

        $response = $this->postJson("/api/annuaire/{$service->id}/avis", ['note' => 3]);

        $response->assertStatus(401);
    }

    // ── Commentaires : compte requis ────────────────────────

    public function test_commenting_requires_authentication(): void
    {
        $auteur = $this->createParticulier('pub-owner@example.test');
        $publication = $this->createPublication($auteur);

        $response = $this->postJson('/api/site/commentaires', [
            'publication_id' => $publication->id,
            'contenu' => 'Commentaire anonyme',
        ]);

        $response->assertStatus(401);
        $this->assertDatabaseMissing('commentaires', ['publication_id' => $publication->id]);
    }

    public function test_authenticated_user_can_comment_and_it_is_attributed_to_their_account(): void
    {
        $auteur = $this->createParticulier('pub-owner2@example.test');
        $publication = $this->createPublication($auteur);
        $commentateur = $this->createParticulier('commentateur@example.test');

        Sanctum::actingAs($commentateur);

        $response = $this->postJson('/api/site/commentaires', [
            'publication_id' => $publication->id,
            'contenu' => 'Tres utile, merci.',
        ]);

        $response->assertCreated();

        $this->assertDatabaseHas('commentaires', [
            'publication_id' => $publication->id,
            'user_id' => $commentateur->id,
            'nom_visiteur' => $commentateur->prenom.' '.$commentateur->nom,
            'statut' => 'approuve',
        ]);
    }

    public function test_comment_is_immediately_visible_on_public_publication(): void
    {
        $auteur = $this->createParticulier('pub-owner3@example.test');
        $publication = $this->createPublication($auteur);
        $commentateur = $this->createParticulier('commentateur2@example.test');

        Sanctum::actingAs($commentateur);
        $this->postJson('/api/site/commentaires', [
            'publication_id' => $publication->id,
            'contenu' => 'Publie immediatement, compte requis',
        ])->assertCreated();

        $response = $this->getJson("/api/site/publications/{$publication->id}");

        $response->assertOk()
            ->assertJsonCount(1, 'commentaires_approuves')
            ->assertJsonFragment(['contenu' => 'Publie immediatement, compte requis']);
    }

    // ── 2.5 — Notifications sociales ────────────────────────

    public function test_liking_a_publication_notifies_its_owner(): void
    {
        $auteur = $this->createParticulier('notif-owner@example.test');
        $publication = $this->createPublication($auteur);
        $liker = $this->createParticulier('notif-liker@example.test');

        Sanctum::actingAs($liker);
        $this->postJson('/api/likes', [
            'likeable_type' => 'publication',
            'likeable_id' => $publication->id,
        ])->assertOk();

        $this->assertDatabaseHas('notifications', [
            'user_id' => $auteur->id,
            'type' => 'like',
        ]);
    }

    public function test_following_a_user_notifies_them(): void
    {
        $auteur = $this->createParticulier('notif-followed@example.test');
        $follower = $this->createParticulier('notif-follower@example.test');

        Sanctum::actingAs($follower);
        $this->postJson('/api/abonnements', [
            'followable_type' => 'user',
            'followable_id' => $auteur->id,
        ])->assertOk();

        $this->assertDatabaseHas('notifications', [
            'user_id' => $auteur->id,
            'type' => 'nouvel_abonne',
        ]);
    }

    public function test_commenting_notifies_the_publication_owner(): void
    {
        $auteur = $this->createParticulier('notif-pub-owner@example.test');
        $publication = $this->createPublication($auteur);
        $commentateur = $this->createParticulier('notif-commentateur@example.test');

        Sanctum::actingAs($commentateur);
        $this->postJson('/api/site/commentaires', [
            'publication_id' => $publication->id,
            'contenu' => 'Bravo !',
        ])->assertCreated();

        $this->assertDatabaseHas('notifications', [
            'user_id' => $auteur->id,
            'type' => 'commentaire',
        ]);
    }

    public function test_liking_own_publication_does_not_self_notify(): void
    {
        $auteur = $this->createParticulier('notif-self@example.test');
        $publication = $this->createPublication($auteur);

        Sanctum::actingAs($auteur);
        $this->postJson('/api/likes', [
            'likeable_type' => 'publication',
            'likeable_id' => $publication->id,
        ])->assertOk();

        $this->assertDatabaseMissing('notifications', ['user_id' => $auteur->id, 'type' => 'like']);
    }

    // ── 3.1 — Modération ─────────────────────────────────────

    public function test_moderation_endpoints_require_permission(): void
    {
        $user = $this->createParticulier('non-moderateur@example.test');
        Sanctum::actingAs($user);

        $this->getJson('/api/commentaires')->assertStatus(403);
        $this->getJson('/api/avis')->assertStatus(403);
    }

    public function test_moderator_can_list_avis(): void
    {
        $entreprise = $this->createEntreprise('ModAvisEntreprise');
        $moderateur = $this->createUser($entreprise, 'moderateur-avis@example.test');
        $moderateur->permissions()->attach(Permission::firstOrCreate(['nom' => 'gerer_publications'])->id);

        $service = $this->createAnnuaire($entreprise);
        $client = $this->createParticulier('avis-list-user@example.test');
        Sanctum::actingAs($client);
        $this->postJson("/api/annuaire/{$service->id}/avis", ['note' => 3])->assertCreated();

        Sanctum::actingAs($moderateur);
        $this->getJson('/api/avis')->assertOk();
    }

    public function test_comment_is_auto_approved_and_moderator_can_still_remove_it_after_the_fact(): void
    {
        $entreprise = $this->createEntreprise('ModEntreprise');
        $moderateur = $this->createUser($entreprise, 'moderateur@example.test');
        $moderateur->permissions()->attach(Permission::firstOrCreate(['nom' => 'gerer_publications'])->id);

        $auteur = $this->createParticulier('mod-pub-owner@example.test');
        $publication = $this->createPublication($auteur);
        $commentateur = $this->createParticulier('mod-commentateur@example.test');

        Sanctum::actingAs($commentateur);
        $this->postJson('/api/site/commentaires', [
            'publication_id' => $publication->id,
            'contenu' => 'Contenu inapproprie',
        ])->assertCreated();

        $commentaire = Commentaire::firstOrFail();

        // Publié immédiatement, sans intervention du modérateur.
        $this->assertSame('approuve', $commentaire->statut);

        // Le modérateur peut malgré tout le retirer a posteriori.
        Sanctum::actingAs($moderateur);
        $this->postJson("/api/commentaires/{$commentaire->id}/rejeter")->assertOk();

        $this->assertDatabaseHas('commentaires', [
            'id' => $commentaire->id,
            'statut' => 'rejete',
        ]);
    }

    // ── 3.2 — Badge vérifié ───────────────────────────────────

    public function test_verifying_a_user_requires_super_admin_role(): void
    {
        $entreprise = $this->createEntreprise('AdminEntreprise');
        $admin = $this->createUser($entreprise, 'admin-simple@example.test');
        $cible = $this->createParticulier('a-verifier@example.test');

        Sanctum::actingAs($admin);
        $this->postJson("/api/users/{$cible->id}/verifier")->assertStatus(403);

        $superAdmin = $this->createUser($entreprise, 'super@example.test');
        $superAdmin->roles()->attach(Role::firstOrCreate(['nom' => 'super_admin'])->id);

        Sanctum::actingAs($superAdmin);
        $this->postJson("/api/users/{$cible->id}/verifier")->assertOk();

        $this->assertDatabaseHas('users', ['id' => $cible->id, 'verifie' => true]);
    }

    // ── 3.3 — Fil personnalisé ────────────────────────────────

    public function test_personalized_feed_only_returns_publications_from_followed_authors(): void
    {
        $suivi = $this->createParticulier('suivi-feed@example.test');
        $nonSuivi = $this->createParticulier('non-suivi-feed@example.test');
        $publicationSuivie = $this->createPublication($suivi, 'Publication suivie');
        $this->createPublication($nonSuivi, 'Publication non suivie');

        $viewer = $this->createParticulier('viewer-feed@example.test');
        Abonnement::create(['follower_id' => $viewer->id, 'followable_id' => $suivi->id, 'followable_type' => 'user']);

        Sanctum::actingAs($viewer);

        $response = $this->getJson('/api/site/publications?scope=abonnements');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['titre' => 'Publication suivie'])
            ->assertJsonMissing(['titre' => 'Publication non suivie']);
    }

    // ── Helpers ───────────────────────────────────────────────

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

    private function createParticulier(string $email): User
    {
        $role = Role::firstOrCreate(['nom' => 'membre']);

        $user = User::create([
            'entreprise_id' => null,
            'nom' => 'Particulier',
            'prenom' => 'Test',
            'email' => $email,
            'password' => bcrypt('password'),
            'actif' => true,
        ]);

        $user->roles()->attach($role->id);

        return $user;
    }

    private function createPublication(User $user, string $titre = 'Publication de test'): Publication
    {
        return Publication::create([
            'user_id' => $user->id,
            'titre' => $titre,
            'contenu' => 'Contenu de test suffisamment long.',
            'statut' => 'publie',
            'visible_site' => true,
        ]);
    }

    private function createAnnuaire(Entreprise $entreprise, string $service = 'Service de test'): Annuaire
    {
        return Annuaire::create([
            'entreprise_id' => $entreprise->id,
            'service' => $service,
            'description' => 'Description de test',
            'disponible' => true,
            'etat_publication' => 'publie',
        ]);
    }
}
