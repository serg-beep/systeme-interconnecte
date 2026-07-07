<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entreprise_id')->constrained('entreprises')->onDelete('cascade');
            $table->string('email_invite');
            $table->string('token')->unique();
            $table->enum('statut', ['en_attente','acceptee','expiree'])->default('en_attente');
            $table->dateTime('date_expiration');
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('invitations');
    }
};
