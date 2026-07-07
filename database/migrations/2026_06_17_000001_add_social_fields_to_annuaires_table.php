<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('annuaires', function (Blueprint $table) {
            $table->string('categorie')->nullable()->after('description');
            $table->string('cover_image')->nullable()->after('categorie');
            $table->string('visit_url')->nullable()->after('cover_image');
            $table->json('tags')->nullable()->after('visit_url');
            $table->string('ville')->nullable()->after('tags');
            $table->enum('etat_publication', ['publie', 'brouillon', 'archive'])->default('publie')->after('ville');
            $table->unsignedInteger('vues')->default(0)->after('etat_publication');
        });
    }

    public function down(): void {
        Schema::table('annuaires', function (Blueprint $table) {
            $table->dropColumn(['categorie', 'cover_image', 'visit_url', 'tags', 'ville', 'etat_publication', 'vues']);
        });
    }
};
