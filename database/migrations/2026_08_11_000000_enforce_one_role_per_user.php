<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // Conserve le rôle le plus ancien si des données historiques en contiennent plusieurs.
        DB::table('role_user')->orderBy('user_id')->orderBy('id')->get()
            ->groupBy('user_id')
            ->each(function ($roles) {
                $roles->skip(1)->each(fn ($role) => DB::table('role_user')->where('id', $role->id)->delete());
            });

        Schema::table('role_user', function (Blueprint $table) {
            $table->unique('user_id', 'role_user_user_id_unique');
        });
    }

    public function down(): void {
        Schema::table('role_user', function (Blueprint $table) {
            $table->dropUnique('role_user_user_id_unique');
        });
    }
};
