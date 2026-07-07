<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('partenaires', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entreprise_id')->constrained('entreprises')->onDelete('cascade');
            $table->foreignId('entreprise_partenaire_id')->constrained('entreprises')->onDelete('cascade');
            $table->enum('statut', ['en_attente','accepte','refuse'])->default('en_attente');
            $table->date('date_partenariat')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('partenaires');
    }
};
