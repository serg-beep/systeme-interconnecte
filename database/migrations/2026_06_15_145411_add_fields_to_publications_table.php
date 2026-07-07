<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('publications', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('titre')->nullable();
            $table->longText('contenu')->nullable();
            $table->string('image')->nullable();
            $table->string('statut')->default('publie');
            $table->boolean('visible_site')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('publications', function (Blueprint $table) {
            $table->dropColumn([
                'titre',
                'contenu',
                'image',
                'statut',
                'visible_site'
            ]);

            $table->dropConstrainedForeignId('user_id');
        });
    }
};
