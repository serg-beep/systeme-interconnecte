<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // requetes: filtre WHERE statut='ouverte' ORDER BY created_at DESC
        Schema::table('requetes', function (Blueprint $table) {
            $table->index(['statut', 'created_at'], 'requetes_statut_date_idx');
        });

        // demandes: filtre par cible_id ou source_id ORDER BY created_at DESC
        Schema::table('demandes', function (Blueprint $table) {
            $table->index(['entreprise_cible_id',  'created_at'], 'demandes_cible_date_idx');
            $table->index(['entreprise_source_id', 'created_at'], 'demandes_source_date_idx');
        });

        // documents: filtre par entreprise_id ou visibilite ORDER BY created_at DESC
        Schema::table('documents', function (Blueprint $table) {
            $table->index(['entreprise_id', 'created_at'], 'documents_ent_date_idx');
            $table->index(['visibilite',    'created_at'], 'documents_vis_date_idx');
        });
    }

    public function down(): void
    {
        Schema::table('requetes',  fn ($t) => $t->dropIndex('requetes_statut_date_idx'));
        Schema::table('demandes',  fn ($t) => $t->dropIndex('demandes_cible_date_idx'));
        Schema::table('demandes',  fn ($t) => $t->dropIndex('demandes_source_date_idx'));
        Schema::table('documents', fn ($t) => $t->dropIndex('documents_ent_date_idx'));
        Schema::table('documents', fn ($t) => $t->dropIndex('documents_vis_date_idx'));
    }
};
