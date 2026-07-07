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
        Schema::create('actualites', function (Blueprint $table) {
            $table->id();
            $table->string('titre');
            $table->text('resume');
            $table->longText('contenu')->nullable();
            $table->string('image')->nullable();
            $table->enum('categorie', ['burkina', 'afrique', 'monde'])->default('monde');
            $table->string('source');
            $table->string('url_source')->nullable();
            $table->string('url_externe')->nullable();
            $table->boolean('publie')->default(false);
            $table->boolean('auto_fetched')->default(false);
            $table->string('hash_dedup')->nullable()->unique();
            $table->timestamp('date_publication')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('actualites');
    }
};
