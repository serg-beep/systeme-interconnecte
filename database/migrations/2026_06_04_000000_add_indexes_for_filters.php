<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('alertes', function (Blueprint $table) {
            $table->index('type');
            $table->index('priorite');
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->index('type');
            $table->index('visibilite');
        });

        Schema::table('requetes', function (Blueprint $table) {
            $table->index('type');
            $table->index('statut');
        });

        Schema::table('demandes', function (Blueprint $table) {
            $table->index('type');
            $table->index('statut');
        });
    }

    public function down(): void {
        Schema::table('alertes', function (Blueprint $table) {
            $table->dropIndex(['type']);
            $table->dropIndex(['priorite']);
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->dropIndex(['type']);
            $table->dropIndex(['visibilite']);
        });

        Schema::table('requetes', function (Blueprint $table) {
            $table->dropIndex(['type']);
            $table->dropIndex(['statut']);
        });

        Schema::table('demandes', function (Blueprint $table) {
            $table->dropIndex(['type']);
            $table->dropIndex(['statut']);
        });
    }
};
