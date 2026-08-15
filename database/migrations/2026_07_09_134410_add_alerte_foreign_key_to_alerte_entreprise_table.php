<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('alerte_entreprise', function (Blueprint $table) {
            $table->foreign('alerte_id')->references('id')->on('alertes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alerte_entreprise', function (Blueprint $table) {
            $table->dropForeign(['alerte_id']);
        });
    }
};
