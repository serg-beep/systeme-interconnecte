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
        Schema::table('actualites', function (Blueprint $table) {
            $table->text('url_externe')->nullable()->change();
            $table->text('url_source')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('actualites', function (Blueprint $table) {
            $table->string('url_externe')->nullable()->change();
            $table->string('url_source')->nullable()->change();
        });
    }
};
