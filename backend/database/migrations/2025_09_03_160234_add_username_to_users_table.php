<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddUsernameToUsersTable extends Migration
{
    public function up(): void
    {
        // 1) Agregar columna como NULLABLE (no UNIQUE todavía)
        Schema::table('users', function (Blueprint $t) {
            if (!Schema::hasColumn('users', 'username')) {
                $t->string('username')->nullable()->after('id');
            }
        });

        // 2) Backfill seguro (sin asumir que exista 'name')
        $hasEmail = Schema::hasColumn('users', 'email');

        $rows = $hasEmail
            ? DB::table('users')->select('id', 'email')->orderBy('id')->get()
            : DB::table('users')->select('id')->orderBy('id')->get();

        $taken = [];
        foreach ($rows as $r) {
            // Base: parte local del email si existe; si no, user{id}
            $base = null;
            if ($hasEmail && !empty($r->email) && strpos($r->email, '@') !== false) {
                $base = substr($r->email, 0, strpos($r->email, '@'));
            }
            if (!$base) {
                $base = 'user' . $r->id;
            }

            // Garantiza unicidad local en este backfill
            $u = $base;
            $i = 1;
            while (in_array($u, $taken, true)) {
                $u = $base . '_' . $i++;
            }
            $taken[] = $u;

            DB::table('users')->where('id', $r->id)->update(['username' => $u]);
        }

        // 3) Índice UNIQUE
        Schema::table('users', function (Blueprint $t) {
            $t->unique('username', 'users_username_unique');
        });

        // 4) NOT NULL (Postgres) sin DBAL
        DB::statement('ALTER TABLE users ALTER COLUMN username SET NOT NULL');
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $t) {
            // El índice puede no existir si nunca llegó a crearse: atrápalo
            try { $t->dropUnique('users_username_unique'); } catch (\Throwable $e) {}
            if (Schema::hasColumn('users', 'username')) {
                $t->dropColumn('username');
            }
        });
    }
}
