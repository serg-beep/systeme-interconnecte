<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Créer les permissions
        $permissions = [
            // Gestion des utilisateurs
            ['nom' => 'voir_utilisateurs', 'description' => 'Voir les utilisateurs', 'domaine' => 'utilisateurs'],
            ['nom' => 'creer_utilisateurs', 'description' => 'Créer des utilisateurs', 'domaine' => 'utilisateurs'],
            ['nom' => 'modifier_utilisateurs', 'description' => 'Modifier les utilisateurs', 'domaine' => 'utilisateurs'],
            ['nom' => 'supprimer_utilisateurs', 'description' => 'Supprimer les utilisateurs', 'domaine' => 'utilisateurs'],

            // Gestion des entreprises
            ['nom' => 'voir_entreprises', 'description' => 'Voir les entreprises', 'domaine' => 'entreprises'],
            ['nom' => 'creer_entreprises', 'description' => 'Créer des entreprises', 'domaine' => 'entreprises'],
            ['nom' => 'modifier_entreprises', 'description' => 'Modifier les entreprises', 'domaine' => 'entreprises'],
            ['nom' => 'supprimer_entreprises', 'description' => 'Supprimer les entreprises', 'domaine' => 'entreprises'],

            // Gestion des rôles et permissions
            ['nom' => 'voir_roles', 'description' => 'Voir les rôles', 'domaine' => 'roles_permissions'],
            ['nom' => 'creer_roles', 'description' => 'Créer des rôles', 'domaine' => 'roles_permissions'],
            ['nom' => 'modifier_roles', 'description' => 'Modifier les rôles', 'domaine' => 'roles_permissions'],
            ['nom' => 'supprimer_roles', 'description' => 'Supprimer les rôles', 'domaine' => 'roles_permissions'],

            // Gestion des alertes
            ['nom' => 'voir_alertes', 'description' => 'Voir les alertes', 'domaine' => 'alertes'],
            ['nom' => 'creer_alertes', 'description' => 'Créer des alertes', 'domaine' => 'alertes'],
            ['nom' => 'modifier_alertes', 'description' => 'Modifier les alertes', 'domaine' => 'alertes'],
            ['nom' => 'supprimer_alertes', 'description' => 'Supprimer les alertes', 'domaine' => 'alertes'],

            // Gestion des requêtes
            ['nom' => 'voir_requetes', 'description' => 'Voir les requêtes', 'domaine' => 'requetes'],
            ['nom' => 'creer_requetes', 'description' => 'Créer des requêtes', 'domaine' => 'requetes'],
            ['nom' => 'modifier_requetes', 'description' => 'Modifier les requêtes', 'domaine' => 'requetes'],
            ['nom' => 'supprimer_requetes', 'description' => 'Supprimer les requêtes', 'domaine' => 'requetes'],

            // Gestion des demandes
            ['nom' => 'voir_demandes', 'description' => 'Voir les demandes', 'domaine' => 'demandes'],
            ['nom' => 'creer_demandes', 'description' => 'Créer des demandes', 'domaine' => 'demandes'],
            ['nom' => 'modifier_demandes', 'description' => 'Modifier les demandes', 'domaine' => 'demandes'],
            ['nom' => 'supprimer_demandes', 'description' => 'Supprimer les demandes', 'domaine' => 'demandes'],

            // Gestion des documents
            ['nom' => 'voir_documents', 'description' => 'Voir les documents', 'domaine' => 'documents'],
            ['nom' => 'creer_documents', 'description' => 'Créer des documents', 'domaine' => 'documents'],
            ['nom' => 'modifier_documents', 'description' => 'Modifier les documents', 'domaine' => 'documents'],
            ['nom' => 'supprimer_documents', 'description' => 'Supprimer les documents', 'domaine' => 'documents'],

            // Gestion des conversations
            ['nom' => 'voir_conversations', 'description' => 'Voir les conversations', 'domaine' => 'conversations'],
            ['nom' => 'creer_conversations', 'description' => 'Créer des conversations', 'domaine' => 'conversations'],
            ['nom' => 'modifier_conversations', 'description' => 'Modifier les conversations', 'domaine' => 'conversations'],

            // Gestion des partenaires
            ['nom' => 'voir_partenaires', 'description' => 'Voir les partenaires', 'domaine' => 'partenaires'],
            ['nom' => 'creer_partenaires', 'description' => 'Créer des partenaires', 'domaine' => 'partenaires'],
            ['nom' => 'modifier_partenaires', 'description' => 'Modifier les partenaires', 'domaine' => 'partenaires'],
            ['nom' => 'supprimer_partenaires', 'description' => 'Supprimer les partenaires', 'domaine' => 'partenaires'],

            // Gestion de l'annuaire
            ['nom' => 'voir_annuaire', 'description' => 'Consulter l\'annuaire', 'domaine' => 'annuaire'],

            // Paramètres
            ['nom' => 'voir_parametres', 'description' => 'Voir les paramètres', 'domaine' => 'parametres'],
            ['nom' => 'modifier_parametres_entreprise', 'description' => 'Modifier les paramètres de l\'entreprise', 'domaine' => 'parametres'],
            ['nom' => 'modifier_parametres_systeme', 'description' => 'Modifier les paramètres du système', 'domaine' => 'parametres'],
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['nom' => $perm['nom']], $perm);
        }

        // Créer les rôles
        $superAdmin = Role::firstOrCreate(
            ['nom' => 'super_admin'],
            ['description' => 'Super administrateur avec accès complet au système']
        );

        $admin = Role::firstOrCreate(
            ['nom' => 'admin'],
            ['description' => 'Administrateur avec accès complet sauf gestion entreprises et système']
        );

        $gestionnaire = Role::firstOrCreate(
            ['nom' => 'gestionnaire'],
            ['description' => 'Gestionnaire avec accès limité']
        );

        $operateur = Role::firstOrCreate(
            ['nom' => 'operateur'],
            ['description' => 'Opérateur avec accès en lecture seule']
        );

        // Assigner les permissions aux rôles
        
        // Super Admin — Toutes les permissions
        $superAdmin->permissions()->sync(
            Permission::all()->pluck('id')->toArray()
        );

        // Admin — Tous sauf gestion entreprises et paramètres système
        $adminPerms = Permission::whereNotIn('nom', [
            'creer_entreprises',
            'modifier_entreprises',
            'supprimer_entreprises',
            'modifier_parametres_systeme',
        ])->pluck('id')->toArray();
        $admin->permissions()->sync($adminPerms);

        // Gestionnaire — Limité
        $gestionnairePerms = Permission::whereIn('nom', [
            'voir_utilisateurs',
            'voir_roles',
            'voir_alertes',
            'creer_alertes',
            'modifier_alertes',
            'supprimer_alertes',
            'voir_requetes',
            'creer_requetes',
            'modifier_requetes',
            'supprimer_requetes',
            'voir_demandes',
            'creer_demandes',
            'modifier_demandes',
            'supprimer_demandes',
            'voir_documents',
            'creer_documents',
            'modifier_documents',
            'supprimer_documents',
            'voir_conversations',
            'creer_conversations',
            'modifier_conversations',
            'voir_partenaires',
            'creer_partenaires',
            'modifier_partenaires',
            'supprimer_partenaires',
            'voir_annuaire',
            'voir_parametres',
        ])->pluck('id')->toArray();
        $gestionnaire->permissions()->sync($gestionnairePerms);

        // Opérateur — Lecture seule
        $operateurPerms = Permission::whereIn('nom', [
            'voir_alertes',
            'voir_requetes',
            'voir_demandes',
            'voir_annuaire',
            'voir_conversations',
            'voir_documents',
        ])->pluck('id')->toArray();
        $operateur->permissions()->sync($operateurPerms);
    }
}
