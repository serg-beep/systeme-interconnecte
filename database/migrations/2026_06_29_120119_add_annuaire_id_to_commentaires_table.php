<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('commentaires', function (Blueprint $table) {
            // Rendre publication_id nullable pour supporter aussi les services
            $table->unsignedBigInteger('publication_id')->nullable()->change();
            // Ajouter annuaire_id pour les commentaires sur les services
            $table->foreignId('annuaire_id')->nullable()->constrained('annuaires')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('commentaires', function (Blueprint $table) {
            $table->dropConstrainedForeignId('annuaire_id');
            $table->unsignedBigInteger('publication_id')->nullable(false)->change();
        });
    }
};
