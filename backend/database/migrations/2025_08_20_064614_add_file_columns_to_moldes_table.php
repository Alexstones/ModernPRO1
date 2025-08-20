<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Si ya existe una de las columnas, asumimos que todas se aplicaron
        if (Schema::hasColumn('moldes', 'camiseta_frente_path')) {
            return;
        }

        Schema::table('moldes', function (Blueprint $table) {
            $table->string('camiseta_frente_path')->nullable();
            $table->string('camiseta_frente_orig')->nullable();
            $table->string('camiseta_espalda_path')->nullable();
            $table->string('camiseta_espalda_orig')->nullable();
            $table->string('short_izq_path')->nullable();
            $table->string('short_izq_orig')->nullable();
            $table->string('short_der_path')->nullable();
            $table->string('short_der_orig')->nullable();
            $table->string('manga_izq_path')->nullable();
            $table->string('manga_izq_orig')->nullable();
            $table->string('manga_der_path')->nullable();
            $table->string('manga_der_orig')->nullable();
        });
    }

    public function down(): void
    {
        // Drop solo si existen (evita errores en rollbacks repetidos)
        Schema::table('moldes', function (Blueprint $table) {
            foreach ([
                'camiseta_frente_path','camiseta_frente_orig',
                'camiseta_espalda_path','camiseta_espalda_orig',
                'short_izq_path','short_izq_orig',
                'short_der_path','short_der_orig',
                'manga_izq_path','manga_izq_orig',
                'manga_der_path','manga_der_orig',
            ] as $col) {
                if (Schema::hasColumn('moldes', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
