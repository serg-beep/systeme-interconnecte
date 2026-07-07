<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('alertes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entreprise_id')->constrained('entreprises')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('titre');
            $table->text('message');
            $table->enum('type', ['rupture_stock','urgence','information','rappel']);
            $table->enum('priorite', ['faible','normale','haute','critique'])->default('normale');
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('alertes');
    }
};
