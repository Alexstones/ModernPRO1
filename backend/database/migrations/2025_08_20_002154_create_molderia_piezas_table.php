<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('molderia_piezas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('molderia_id')->constrained('molderias')->cascadeOnDelete();
            $table->string('nombre_original');
            $table->string('nombre_pieza');
            $table->string('prenda')->nullable();
            $table->string('genero')->default('Unisex');
            $table->string('lado')->default('Centro');
            $table->string('talla')->nullable();
            $table->unsignedInteger('cantidad')->default(1);
            $table->text('notas')->nullable();
            $table->unsignedInteger('orden')->default(0);
            $table->string('mime', 100)->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->string('path'); // relativo a storage/app/public
            $table->timestamps();

            $table->index(['molderia_id', 'orden']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('molderia_piezas');
    }
};
