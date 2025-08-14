<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('moldes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('camiseta_izq')->nullable();
            $table->string('camiseta_der')->nullable();
            $table->string('short_izq')->nullable();
            $table->string('short_der')->nullable();
            $table->string('manga_izq')->nullable();
            $table->string('manga_der')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('moldes');
    }
};
