<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('rapports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entreprise_id')->constrained('entreprises')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('titre');
            $table->enum('type', ['stock','activite','echange','statistique']);
            $table->date('periode_debut');
            $table->date('periode_fin');
            $table->text('contenu')->nullable();
            $table->string('fichier')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('rapports');
    }
};
