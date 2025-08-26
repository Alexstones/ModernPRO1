<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('grade_rule_sets', function(Blueprint $t){
      $t->id();
      $t->string('nombre');                // p.ej. "Fútbol 2025 hombros"
      $t->json('reglas');                  // { "XS":0.92, "S":0.96, "M":1, "L":1.04, "XL":1.08 }
      $t->timestamps();
    });

    Schema::create('anchor_sets', function(Blueprint $t){
      $t->id();
      $t->string('nombre');                // "Playera v1 anclajes"
      $t->json('piezas');                  // [{pieza:"delantera", capa:"logo", modo:"escala|reubica|fijo", eje:"xy", anchor:{x:0.5,y:0.3}}]
      $t->timestamps();
    });

    // Relación opcional con productos/modelos o talles
    Schema::table('tallas', function(Blueprint $t){
      $t->foreignId('grade_rule_set_id')->nullable()->constrained()->nullOnDelete();
      $t->foreignId('anchor_set_id')->nullable()->constrained()->nullOnDelete();
      $t->json('presets')->nullable(); // sets de talles predefinidos aplicables
    });
  }

  public function down(): void {
    Schema::table('tallas', function(Blueprint $t){
      $t->dropConstrainedForeignId('grade_rule_set_id');
      $t->dropConstrainedForeignId('anchor_set_id');
      $t->dropColumn('presets');
    });
    Schema::dropIfExists('anchor_sets');
    Schema::dropIfExists('grade_rule_sets');
  }
};
