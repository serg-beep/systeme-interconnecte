<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('annuaires', function (Blueprint $table) {
            $table->index(['etat_publication', 'disponible', 'created_at']);
            $table->index('categorie');
            $table->index('vues');
        });

        Schema::table('actualites', function (Blueprint $table) {
            $table->index(['publie', 'date_publication']);
            $table->index('categorie');
        });

        Schema::table('commentaires', function (Blueprint $table) {
            $table->index('statut');
        });
    }

    public function down(): void
    {
        Schema::table('annuaires', function (Blueprint $table) {
            $table->dropIndex(['etat_publication', 'disponible', 'created_at']);
            $table->dropIndex(['categorie']);
            $table->dropIndex(['vues']);
        });

        Schema::table('actualites', function (Blueprint $table) {
            $table->dropIndex(['publie', 'date_publication']);
            $table->dropIndex(['categorie']);
        });

        Schema::table('commentaires', function (Blueprint $table) {
            $table->dropIndex(['statut']);
        });
    }
};
