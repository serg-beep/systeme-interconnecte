<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('alerte_entreprise', function (Blueprint $table) {
            // Index composite pour la recherche "alertes visibles par entreprise"
            // Couvre: WHERE entreprise_id = ? (orWhereIn subquery) et WHERE alerte_id IN (...)
            $table->index(['entreprise_id', 'alerte_id'], 'ae_entreprise_alerte_idx');
        });
    }

    public function down(): void
    {
        Schema::table('alerte_entreprise', function (Blueprint $table) {
            $table->dropIndex('ae_entreprise_alerte_idx');
        });
    }
};
