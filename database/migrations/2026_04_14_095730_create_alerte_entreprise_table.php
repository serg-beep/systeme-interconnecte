<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('alerte_entreprise', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alerte_id')->constrained('alertes')->onDelete('cascade');
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
