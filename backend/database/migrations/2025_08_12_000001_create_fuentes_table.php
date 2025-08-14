<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('fuentes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');          // nombre legible
            $table->string('archivo');         // ruta relativa en disk "public"
            $table->string('mime')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('fuentes');
    }
};
