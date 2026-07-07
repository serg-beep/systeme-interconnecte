<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entreprise_id')->constrained('entreprises')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('titre');
            $table->string('fichier');
            $table->enum('type', ['rapport','certificat','ordonnance','analyse','autre']);
            $table->string('taille')->nullable();
            $table->enum('visibilite', ['prive','partenaires','public'])->default('prive');
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('documents');
    }
};
