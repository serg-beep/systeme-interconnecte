<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EntrepriseController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\AlerteController;
use App\Http\Controllers\RequeteController;
use App\Http\Controllers\DemandeController;
use App\Http\Controllers\ReponseController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\PartenaireController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\HistoriqueActionController;
use App\Http\Controllers\RapportController;
use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\AnnuaireController;
use App\Http\Controllers\Cles_Acces_ApiController;
use App\Http\Controllers\RealtimeController;
use App\Http\Controllers\PublicationController;
use App\Http\Controllers\CommentaireController;
use App\Http\Controllers\ActualiteController;

// ═══════════════════════════════════════════
// ROUTES PUBLIQUES (sans connexion)
// ═══════════════════════════════════════════

Route::prefix('auth')->group(function () {
    Route::post('register',   [AuthController::class, 'register'])->middleware('throttle:register');
    Route::post('login',      [AuthController::class, 'login'])->middleware('throttle:login');
    Route::post('invitation', [InvitationController::class, 'accepter']);
});

// Annuaire public
Route::get('annuaire',              [AnnuaireController::class, 'indexPublic']);
Route::get('annuaire/categories',   [AnnuaireController::class, 'categories']);
// Doit être déclarée avant 'annuaire/{id}' pour ne pas être capturée par le paramètre {id}
Route::get('annuaire/mes-services', [AnnuaireController::class, 'index'])->middleware(['auth:sanctum', 'active.token']);
Route::get('annuaire/{id}',         [AnnuaireController::class, 'showPublic']);

// ✅ Site web public
Route::prefix('site')->group(function () {
    Route::get('stats',               [PublicationController::class, 'siteStats']);
    Route::get('publications',        [PublicationController::class, 'indexPublic']);
    Route::get('publications/{id}',   [PublicationController::class, 'showPublic']);
    Route::get('profil/{id}',         [PublicationController::class, 'profilPublic']);
    Route::post('commentaires',       [CommentaireController::class, 'store']);
    Route::get('actualites',          [ActualiteController::class, 'indexPublic']);
    Route::get('actualites/{id}',     [ActualiteController::class, 'showPublic']);
});

// ═══════════════════════════════════════════
// ROUTES PROTÉGÉES (token requis)
// ═══════════════════════════════════════════

Route::middleware(['auth:sanctum', 'active.token'])->group(function () {

    Route::get('dashboard', [DashboardController::class, 'index']);
    Route::get('stream/realtime', [RealtimeController::class, 'stream'])->middleware('throttle:api');

    // ── Auth ──────────────────────────────
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me',      [AuthController::class, 'me']);
    });

    // ── Entreprises ───────────────────────
    Route::get('entreprises', [EntrepriseController::class, 'index'])->middleware('permission:voir_entreprises');
    Route::post('entreprises', [EntrepriseController::class, 'store'])->middleware('permission:gerer_entreprises');
    Route::get('entreprises/{entreprise}', [EntrepriseController::class, 'show'])->middleware('permission:voir_entreprises');
    Route::put('entreprises/{entreprise}', [EntrepriseController::class, 'update'])->middleware('permission:gerer_entreprises');
    Route::delete('entreprises/{entreprise}', [EntrepriseController::class, 'destroy'])->middleware('permission:gerer_entreprises');

    Route::get('users',              [UserController::class, 'index'])->middleware('permission:voir_users');
    Route::get('users/stats',        [UserController::class, 'stats'])->middleware('permission:voir_users');
    Route::get('users/{id}',         [UserController::class, 'show'])->middleware('permission:voir_users');
    Route::post('users',             [UserController::class, 'store'])->middleware('permission:gerer_users');
    Route::put('users/{id}',         [UserController::class, 'update'])->middleware('permission:gerer_users');
    Route::delete('users/{id}',      [UserController::class, 'destroy'])->middleware('permission:gerer_users');
    Route::post('users/{id}/roles',  [UserController::class, 'assignRole'])->middleware('permission:gerer_users');
    Route::post('users/{id}/permissions', [UserController::class, 'assignPermissions'])->middleware('permission:gerer_users');
    Route::post('users/{id}/activer',[UserController::class, 'toggleActif'])->middleware('permission:gerer_users');

    // ── Rôles et Permissions ──────────────
    Route::apiResource('roles',       RoleController::class)->middleware('role:admin,super_admin');
    Route::apiResource('permissions', PermissionController::class)->middleware('role:super_admin');
    Route::post('roles/{id}/permissions', [RoleController::class, 'assignPermissions'])->middleware('role:super_admin');

    // ── Partenaires ───────────────────────
    Route::apiResource('partenaires', PartenaireController::class)->middleware('permission:gerer_partenaires');
    Route::post('partenaires/{id}/repondre',  [PartenaireController::class, 'repondre'])->middleware('permission:gerer_partenaires');
    Route::get('historique/partenaire/{id}',  [PartenaireController::class, 'historique'])->middleware('permission:gerer_partenaires');

    // ── Alertes ───────────────────────────
    Route::get('alertes',                  [AlerteController::class, 'index'])->middleware('permission:voir_alertes');
    Route::post('alertes',                 [AlerteController::class, 'store'])->middleware('permission:creer_alertes');
    Route::get('alertes/{id}',             [AlerteController::class, 'show'])->middleware('permission:voir_alertes');
    Route::put('alertes/{id}',             [AlerteController::class, 'update'])->middleware('permission:creer_alertes');
    Route::delete('alertes/{id}',          [AlerteController::class, 'destroy'])->middleware('permission:creer_alertes');
    Route::post('alertes/{id}/accuser',    [AlerteController::class, 'accuser'])->middleware('permission:voir_alertes');
    Route::get('alertes/{id}/destinaires', [AlerteController::class, 'destinaires'])->middleware('permission:creer_alertes');

    // ── Requêtes ──────────────────────────
    Route::get('requetes',             [RequeteController::class, 'index'])->middleware('permission:voir_requetes');
    Route::post('requetes',            [RequeteController::class, 'store'])->middleware('permission:creer_requetes');
    Route::get('requetes/{id}',        [RequeteController::class, 'show'])->middleware('permission:voir_requetes');
    Route::put('requetes/{id}',        [RequeteController::class, 'update'])->middleware('permission:creer_requetes');
    Route::delete('requetes/{id}',     [RequeteController::class, 'destroy'])->middleware('permission:creer_requetes');
    Route::post('requetes/{id}/fermer',  [RequeteController::class, 'fermer'])->middleware('permission:creer_requetes');
    Route::post('requetes/{id}/expirer', [RequeteController::class, 'expirer'])->middleware('permission:creer_requetes');
    Route::get('historique/requete/{id}',[RequeteController::class, 'historique'])->middleware('permission:voir_requetes');

    // ── Demandes ──────────────────────────
    Route::get('demandes',              [DemandeController::class, 'index'])->middleware('permission:voir_demandes');
    Route::post('demandes',             [DemandeController::class, 'store'])->middleware('permission:creer_demandes');
    Route::get('demandes/{id}',         [DemandeController::class, 'show'])->middleware('permission:voir_demandes');
    Route::put('demandes/{id}',         [DemandeController::class, 'update'])->middleware('permission:creer_demandes');
    Route::delete('demandes/{id}',      [DemandeController::class, 'destroy'])->middleware('permission:creer_demandes');
    Route::post('demandes/{id}/repondre', [DemandeController::class, 'repondre'])->middleware('permission:creer_demandes');
    Route::post('demandes/{id}/annuler',  [DemandeController::class, 'annuler'])->middleware('permission:creer_demandes');
    Route::get('historique/demande/{id}', [DemandeController::class, 'historique'])->middleware('permission:voir_demandes');

    // ── Réponses ──────────────────────────
    Route::post('reponses',               [ReponseController::class, 'store'])->middleware('permission:creer_reponses');
    Route::get('reponses/{id}',           [ReponseController::class, 'show'])->middleware('permission:voir_reponses');
    Route::post('reponses/{id}/lue',      [ReponseController::class, 'marquerLue'])->middleware('permission:voir_reponses');
    Route::post('reponses/{id}/archiver', [ReponseController::class, 'archiver'])->middleware('permission:voir_reponses');

    // ── Conversations ─────────────────────
    Route::get('conversations',                      [ConversationController::class, 'index'])->middleware('permission:voir_messages');
    Route::post('conversations',                     [ConversationController::class, 'store'])->middleware('permission:creer_messages');
    Route::get('conversations/{id}',                 [ConversationController::class, 'show'])->middleware('permission:voir_messages');
    Route::put('conversations/{id}',                 [ConversationController::class, 'update'])->middleware('permission:creer_messages');
    Route::delete('conversations/{id}',              [ConversationController::class, 'destroy'])->middleware('permission:creer_messages');
    Route::get('conversations/{id}/messages',        [ConversationController::class, 'messages'])->middleware('permission:voir_messages');
    Route::post('conversations/{id}/messages',       [ConversationController::class, 'envoyerMessage'])->middleware(['permission:creer_messages', 'throttle:messages']);
    Route::post('conversations/{id}/membres',        [ConversationController::class, 'ajouterMembre'])->middleware('permission:creer_messages');
    Route::delete('conversations/{id}/membres/{eid}',[ConversationController::class, 'retirerMembre'])->middleware('permission:creer_messages');

    // ── Notifications ─────────────────────
    Route::get('notifications/non-lues',     [NotificationController::class, 'nonLues']);
    Route::post('notifications/toutes-lues', [NotificationController::class, 'toutMarquerLues']);
    Route::get('notifications',              [NotificationController::class, 'index']);
    Route::post('notifications/{id}/lue',    [NotificationController::class, 'marquerLue']);
    Route::delete('notifications/{id}',      [NotificationController::class, 'destroy']);

    // ── Documents ─────────────────────────
    Route::get('documents/partages',         [DocumentController::class, 'partages'])->middleware('permission:voir_documents');
    Route::get('documents',                  [DocumentController::class, 'index'])->middleware('permission:voir_documents');
    Route::post('documents',                 [DocumentController::class, 'store'])->middleware(['permission:gerer_documents', 'throttle:upload']);
    Route::get('documents/{id}',             [DocumentController::class, 'show'])->middleware('permission:voir_documents');
    Route::put('documents/{id}',             [DocumentController::class, 'update'])->middleware('permission:gerer_documents');
    Route::delete('documents/{id}',          [DocumentController::class, 'destroy'])->middleware('permission:gerer_documents');
    Route::get('documents/{id}/telecharger', [DocumentController::class, 'telecharger'])->middleware('permission:voir_documents');

    // ── Invitations ───────────────────────
    Route::apiResource('invitations', InvitationController::class)->middleware('permission:gerer_users');
    Route::post('invitations/{id}/renvoyer', [InvitationController::class, 'renvoyer'])->middleware('permission:gerer_users');

    // ── Historique Actions ────────────────
    Route::get('historiqueActions/entreprise', [HistoriqueActionController::class, 'parEntreprise'])->middleware('permission:voir_audit_logs');
    Route::get('historiqueActions/user/{id}',  [HistoriqueActionController::class, 'parUser'])->middleware('permission:voir_audit_logs');
    Route::get('historiqueActions',            [HistoriqueActionController::class, 'index'])->middleware('permission:voir_audit_logs');

    // ── Rapports ──────────────────────────
    Route::get('rapports', [RapportController::class, 'index'])->middleware('permission:voir_rapports');
    Route::post('rapports', [RapportController::class, 'store'])->middleware('permission:creer_rapports');
    Route::get('rapports/{rapport}', [RapportController::class, 'show'])->middleware('permission:voir_rapports');
    Route::put('rapports/{rapport}', [RapportController::class, 'update'])->middleware('permission:creer_rapports');
    Route::delete('rapports/{rapport}', [RapportController::class, 'destroy'])->middleware('permission:creer_rapports');
    Route::get('rapports/{id}/telecharger', [RapportController::class, 'telecharger'])->middleware('permission:voir_rapports');

    // ── Evaluations ───────────────────────
    Route::get('evaluations/entreprise/{id}', [EvaluationController::class, 'parEntreprise'])->middleware('permission:voir_evaluations');
    Route::get('evaluations', [EvaluationController::class, 'index'])->middleware('permission:voir_evaluations');
    Route::post('evaluations', [EvaluationController::class, 'store'])->middleware('permission:creer_evaluations');
    Route::get('evaluations/{evaluation}', [EvaluationController::class, 'show'])->middleware('permission:voir_evaluations');
    Route::put('evaluations/{evaluation}', [EvaluationController::class, 'update'])->middleware('permission:creer_evaluations');
    Route::delete('evaluations/{evaluation}', [EvaluationController::class, 'destroy'])->middleware('permission:creer_evaluations');

    // ── Annuaire (annuaire/mes-services déclarée plus haut, hors groupe, voir ligne ~42) ──
    Route::post('annuaire',                                [AnnuaireController::class, 'store'])->middleware('permission:gerer_annuaires');
    Route::put('annuaire/{id}',                            [AnnuaireController::class, 'update'])->middleware('permission:gerer_annuaires');
    Route::delete('annuaire/{id}',                         [AnnuaireController::class, 'destroy'])->middleware('permission:gerer_annuaires');
    Route::post('annuaire/{id}/disponibilite',             [AnnuaireController::class, 'toggleDisponible'])->middleware('permission:gerer_annuaires');
    Route::post('annuaire/{id}/image',                     [AnnuaireController::class, 'uploadImage'])->middleware('permission:gerer_annuaires');

    // ── Clés d'accès API ──────────────────
    Route::apiResource('cles-acces', Cles_Acces_ApiController::class)->middleware('permission:gerer_token_apis');
    Route::post('cles-acces/{id}/desactiver', [Cles_Acces_ApiController::class, 'desactiver'])->middleware('permission:gerer_token_apis');
    Route::post('cles-acces/{id}/regenerer',  [Cles_Acces_ApiController::class, 'regenerer'])->middleware('permission:gerer_token_apis');

    // ✅ Publications (logiciel)
    Route::get('publications',         [PublicationController::class, 'index']);
    Route::post('publications',        [PublicationController::class, 'store'])->middleware('permission:gerer_publications');
    Route::put('publications/{id}',    [PublicationController::class, 'update'])->middleware('permission:gerer_publications');
    Route::delete('publications/{id}', [PublicationController::class, 'destroy'])->middleware('permission:gerer_publications');

    // ✅ Commentaires modération (logiciel)
    Route::get('commentaires',                 [CommentaireController::class, 'index'])->middleware('permission:gerer_publications');
    Route::post('commentaires/{id}/approuver', [CommentaireController::class, 'approuver'])->middleware('permission:gerer_publications');
    Route::post('commentaires/{id}/rejeter',   [CommentaireController::class, 'rejeter'])->middleware('permission:gerer_publications');
    Route::delete('commentaires/{id}',         [CommentaireController::class, 'destroy'])->middleware('permission:gerer_publications');

    // ── Actualités (admin) ────────────────────
    Route::middleware('role:admin,super_admin')->group(function () {
        Route::get('actualites',                  [ActualiteController::class, 'index']);
        Route::post('actualites/fetch',           [ActualiteController::class, 'fetchRss']);
        Route::post('actualites',                 [ActualiteController::class, 'store']);
        Route::post('actualites/{id}',            [ActualiteController::class, 'update']);
        Route::delete('actualites/{id}',          [ActualiteController::class, 'destroy']);
        Route::post('actualites/{id}/toggle',     [ActualiteController::class, 'togglePublie']);
    });

});
