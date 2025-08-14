<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('perfiles_fuente', function (Blueprint $table) {
            $table->id();
            $table->string('perfil');                         // nombre del perfil
            $table->foreignId('fuente_id')->constrained('fuentes')->onDelete('cascade');
            $table->integer('cont')->default(0);              // métrica (opcional)
            $table->string('letra_dir')->nullable();          // carpeta relativa en /public
            $table->string('contorno_dir')->nullable();       // carpeta relativa en /public
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('perfiles_fuente');
    }
};
