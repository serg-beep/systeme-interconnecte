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
            'voir_rapports','creer_rapports',
            'voir_audit_logs',
            'gerer_partenaires',
            'gerer_parametres',
            'voir_evaluations','creer_evaluations',
            'gerer_annuaires',
            'gerer_publications',
            'gerer_token_apis',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['nom' => $perm]);
        }

        // Assigner permissions aux rôles
        $superAdmin  = Role::where('nom', 'super_admin')->first();
        $admin       = Role::where('nom', 'admin')->first();
        $gestionnaire = Role::where('nom', 'gestionnaire')->first();
        $operateur   = Role::where('nom', 'operateur')->first();

        $toutesPermissions = Permission::pluck('id')->toArray();

        $superAdmin->permissions()->sync($toutesPermissions);

        $admin->permissions()->sync(
            Permission::whereIn('nom', [
                'voir_entreprises','gerer_entreprises','voir_users','gerer_users',
                'voir_alertes','creer_alertes',
                'voir_requetes','creer_requetes',
                'voir_demandes','creer_demandes',
                'voir_reponses','creer_reponses',
                'voir_messages','creer_messages',
                'voir_documents','gerer_documents',
                'voir_rapports','creer_rapports',
                'voir_audit_logs','gerer_partenaires',
                'voir_evaluations','creer_evaluations',
                'gerer_annuaires','gerer_publications','gerer_token_apis',
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
                'voir_evaluations','creer_evaluations',
                'gerer_annuaires','gerer_publications',
            ])->pluck('id')->toArray()
        );

        $operateur->permissions()->sync(
            Permission::whereIn('nom', [
                'voir_alertes','voir_requetes',
                'voir_demandes','voir_reponses',
                'voir_messages',
                'voir_documents','voir_evaluations',
            ])->pluck('id')->toArray()
        );
    }
}
