<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('historique_statuts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('requete_id')->nullable()->constrained('requetes')->onDelete('cascade');
            $table->foreignId('demande_id')->nullable()->constrained('demandes')->onDelete('cascade');
            $table->foreignId('partenaire_id')->nullable()->constrained('partenaires')->onDelete('cascade');
            $table->string('ancien_statut');
            $table->string('nouveau_statut');
            $table->text('commentaire')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('historique_statuts');
    }
};
