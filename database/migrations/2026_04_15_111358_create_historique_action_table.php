<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('entreprise_id')->nullable()->constrained('entreprises')->onDelete('set null');
            $table->enum('action', ['creation','modification','suppression','connexion','export']);
            $table->string('table_cible');
            $table->unsignedBigInteger('enregistrement_id')->nullable();
            $table->json('anciennes_valeurs')->nullable();
            $table->json('nouvelles_valeurs')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('audit_logs');
    }
};
