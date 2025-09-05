<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AddPasswordToUsersTable extends Migration
{
    public function up(): void
    {
        // 1) Crear la columna como NULLABLE para no romper con filas existentes
        Schema::table('users', function (Blueprint $t) {
            if (!Schema::hasColumn('users', 'password')) {
                // La colocamos después de username si existe, si no no pasa nada
                $t->string('password')->nullable()->after('username');
            }
        });

        // 2) Backfill: asigna un hash por defecto a quienes tengan password NULL
        // (para dev; luego puedes cambiar contraseñas desde tu app)
        DB::table('users')
            ->whereNull('password')
            ->update(['password' => Hash::make('admin')]);

        // 3) Marcarla NOT NULL en Postgres (sin DBAL)
        DB::statement('ALTER TABLE users ALTER COLUMN password SET NOT NULL');
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $t) {
            if (Schema::hasColumn('users', 'password')) {
                $t->dropColumn('password');
            }
        });
    }
}
