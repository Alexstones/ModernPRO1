<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // elimina la tabla si existe, para recrearla correctamente
        Schema::dropIfExists('tallas');

        Schema::create('tallas', function (Blueprint $table) {
            $table->id();
            $table->string('categoria');      // CAMISETAS | MANGAS | SHORT
            $table->string('talle');          // S, M, 0, 2, etc.
            $table->decimal('ancho', 8, 2);
            $table->decimal('alto', 8, 2);
            $table->timestamps();

            $table->unique(['categoria', 'talle']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tallas');
    }
};
