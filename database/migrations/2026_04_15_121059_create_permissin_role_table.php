<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('permission_role', function (Blueprint $table) {
            $table->id();
            // La FK vers roles est ajoutée par une migration ultérieure
            // (create_role_table [pluriel] s'exécute après celle-ci).
            $table->foreignId('role_id');
            $table->foreignId('permission_id')->constrained('permissions')->onDelete('cascade');
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('permission_role');
    }
};
