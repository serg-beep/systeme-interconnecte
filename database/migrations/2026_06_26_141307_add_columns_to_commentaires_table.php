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
            $table->foreignId('publication_id')->constrained()->cascadeOnDelete();
            $table->string('nom_visiteur');
            $table->string('email_visiteur')->nullable();
            $table->text('contenu');
            $table->enum('statut', ['en_attente', 'approuve', 'rejete'])->default('en_attente');
        });
    }

    public function down(): void
    {
        Schema::table('commentaires', function (Blueprint $table) {
            $table->dropConstrainedForeignId('publication_id');
            $table->dropColumn(['nom_visiteur', 'email_visiteur', 'contenu', 'statut']);
        });
    }
};
