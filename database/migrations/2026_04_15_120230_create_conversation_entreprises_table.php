<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('conversation_entreprise', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('conversations')->onDelete('cascade');
            $table->foreignId('entreprise_id')->constrained('entreprises')->onDelete('cascade');
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('conversation_entreprise');
    }
};
