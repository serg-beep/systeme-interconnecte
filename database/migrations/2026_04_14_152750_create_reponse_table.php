<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('reponses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('entreprise_id')->constrained('entreprises')->onDelete('cascade');
            // La FK vers requetes est ajoutée par une migration ultérieure
            // (create_requetes_table s'exécute après celle-ci).
            $table->foreignId('requete_id')->nullable();
            $table->foreignId('demande_id')->nullable()->constrained('demandes')->onDelete('cascade');
            $table->text('contenu');
            $table->string('piece_jointe')->nullable();
            $table->enum('statut', ['envoyee','lue','archivee'])->default('envoyee');
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('reponses');
    }
};
