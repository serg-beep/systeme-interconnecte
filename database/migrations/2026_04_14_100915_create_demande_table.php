<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('demandes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entreprise_source_id')->constrained('entreprises')->onDelete('cascade');
            $table->foreignId('entreprise_cible_id')->constrained('entreprises')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('titre');
            $table->text('description')->nullable();
            $table->enum('type', ['stock','service','partenariat','donnees']);
            $table->enum('statut', ['en_attente','acceptee','refusee','annulee'])->default('en_attente');
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('demandes');
    }
};
