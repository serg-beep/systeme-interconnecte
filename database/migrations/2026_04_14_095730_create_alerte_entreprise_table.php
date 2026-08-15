<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('alerte_entreprise', function (Blueprint $table) {
            $table->id();
            // La FK vers alertes est ajoutée par une migration ultérieure
            // (create_alertes_table s'exécute après celle-ci).
            $table->foreignId('alerte_id');
            $table->foreignId('entreprise_id')->constrained('entreprises')->onDelete('cascade');
            $table->boolean('vue')->default(false);
            $table->dateTime('date_vue')->nullable();
            $table->boolean('accuse_reception')->default(false);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('alerte_entreprise');
    }
};
