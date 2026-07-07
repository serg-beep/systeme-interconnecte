<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('token_apis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entreprise_id')->constrained('entreprises')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('token')->unique();
            $table->string('nom');
            $table->dateTime('derniere_utilisation')->nullable();
            $table->dateTime('expire_le')->nullable();
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('token_apis');
    }
};
