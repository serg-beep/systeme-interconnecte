<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\{Permission, Role};

class PermissionSeeder extends Seeder {
    public function run() {
        $permissions = [
            'voir_entreprises','gerer_entreprises',
            'voir_users','gerer_users',
            'voir_alertes','creer_alertes',
            'voir_requetes','creer_requetes',
            'voir_demandes','creer_demandes',
            'voir_reponses','creer_reponses',
            'voir_messages','creer_messages',
            'voir_documents','gerer_documents',
            'consulter_audit_tracabilite',
            'gerer_partenaires',
            'gerer_annuaires',
            'gerer_publications',
            'gerer_moderations','gerer_actualites',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['nom' => $perm]);
        }

        // Assigner permissions aux rôles
        $superAdmin  = Role::where('nom', 'super_admin')->first();
        $admin       = Role::where('nom', 'admin')->first();
        $gestionnaire = Role::where('nom', 'gestionnaire')->first();
        $operateur   = Role::where('nom', 'operateur')->first();

        // Note : super_admin passe outre toute vérification de permission
        // (voir CheckPermission::handle). La liste ci-dessous est donc
        // purement indicative/affichage, pas une restriction d'accès réelle.
        $superAdmin->permissions()->sync(
            Permission::whereIn('nom', [
                'voir_entreprises','gerer_entreprises',
                'voir_users','gerer_users',
                'gerer_moderations',
                'consulter_audit_tracabilite',
            ])->pluck('id')->toArray()
        );

        $admin->permissions()->sync(
            Permission::whereIn('nom', [
                'voir_entreprises','gerer_entreprises','voir_users','gerer_users',
                'voir_alertes','creer_alertes',
                'voir_requetes','creer_requetes',
                'voir_demandes','creer_demandes',
                'voir_reponses','creer_reponses',
                'voir_messages','creer_messages',
                'voir_documents','gerer_documents',
                'consulter_audit_tracabilite','gerer_partenaires',
                'gerer_annuaires','gerer_publications',
                'gerer_moderations','gerer_actualites',
            ])->pluck('id')->toArray()
        );

        $gestionnaire->permissions()->sync(
            Permission::whereIn('nom', [
                'voir_alertes','creer_alertes',
                'voir_requetes','creer_requetes',
                'voir_demandes','creer_demandes',
                'voir_reponses','creer_reponses',
                'voir_messages','creer_messages',
                'voir_documents','gerer_documents',
                'gerer_annuaires','gerer_publications',
            ])->pluck('id')->toArray()
        );

        $operateur->permissions()->sync(
            Permission::whereIn('nom', [
                'voir_alertes','voir_requetes',
                'voir_demandes','voir_reponses',
                'voir_messages',
                'voir_documents',
            ])->pluck('id')->toArray()
        );
    }
}
