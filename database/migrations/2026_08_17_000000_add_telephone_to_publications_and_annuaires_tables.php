<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('publications', function (Blueprint $table) {
            $table->string('telephone', 30)->nullable()->after('contenu');
        });

        Schema::table('annuaires', function (Blueprint $table) {
            $table->string('telephone', 30)->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('publications', function (Blueprint $table) {
            $table->dropColumn('telephone');
        });

        Schema::table('annuaires', function (Blueprint $table) {
            $table->dropColumn('telephone');
        });
    }
};
