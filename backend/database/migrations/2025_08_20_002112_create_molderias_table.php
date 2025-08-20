<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('molderias', function (Blueprint $table) {
            $table->id();
            $table->string('coleccion', 120);
            $table->text('nota_general')->nullable();
            $table->json('tallas')->nullable();
            $table->unsignedInteger('total_piezas')->default(0);
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('molderias');
    }
};
