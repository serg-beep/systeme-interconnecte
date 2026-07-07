<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            if (!Schema::hasColumn('documents', 'disque')) {
                $table->string('disque')->default('public')->after('fichier');
            }

            $table->index(['entreprise_id', 'visibilite']);
            $table->index(['type', 'created_at']);
        });

        Schema::table('alertes', function (Blueprint $table) {
            $table->index(['entreprise_id', 'priorite']);
            $table->index(['type', 'created_at']);
        });

        Schema::table('demandes', function (Blueprint $table) {
            $table->index(['entreprise_source_id', 'statut']);
            $table->index(['entreprise_cible_id', 'statut']);
            $table->index(['type', 'created_at']);
        });

        Schema::table('requetes', function (Blueprint $table) {
            $table->index(['statut', 'type']);
            $table->index(['entreprise_id', 'created_at']);
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->index(['conversation_id', 'created_at']);
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->index(['user_id', 'lu']);
            $table->index(['user_id', 'created_at']);
        });

        Schema::table('partenaires', function (Blueprint $table) {
            $table->index(['entreprise_id', 'statut']);
            $table->index(['entreprise_partenaire_id', 'statut']);
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropIndex(['entreprise_id', 'visibilite']);
            $table->dropIndex(['type', 'created_at']);
            $table->dropColumn('disque');
        });

        Schema::table('alertes', function (Blueprint $table) {
            $table->dropIndex(['entreprise_id', 'priorite']);
            $table->dropIndex(['type', 'created_at']);
        });

        Schema::table('demandes', function (Blueprint $table) {
            $table->dropIndex(['entreprise_source_id', 'statut']);
            $table->dropIndex(['entreprise_cible_id', 'statut']);
            $table->dropIndex(['type', 'created_at']);
        });

        Schema::table('requetes', function (Blueprint $table) {
            $table->dropIndex(['statut', 'type']);
            $table->dropIndex(['entreprise_id', 'created_at']);
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex(['conversation_id', 'created_at']);
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'lu']);
            $table->dropIndex(['user_id', 'created_at']);
        });

        Schema::table('partenaires', function (Blueprint $table) {
            $table->dropIndex(['entreprise_id', 'statut']);
            $table->dropIndex(['entreprise_partenaire_id', 'statut']);
        });
    }
};
