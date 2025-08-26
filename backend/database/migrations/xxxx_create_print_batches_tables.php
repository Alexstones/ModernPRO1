<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('print_batches', function (Blueprint $t) {
            $t->id();

            // 👇 IMPORTANTE: tu users.id es UUID, así que el FK también debe ser UUID
            $t->foreignUuid('user_id')->nullable()
              ->constrained('users')->nullOnDelete();

            $t->string('name');
            $t->string('group_by')->default('none');        // none|TALLE|CATEGORIA
            $t->string('merge_mode')->default('zip');       // zip|single
            $t->json('mapping');                            // NOMBRE/NUMERO/TALLE...
            $t->json('imposition');                         // sheet, cols, rows, margin, etc.
            $t->string('naming_template')->default('{NOMBRE}_{NUMERO}_{TALLE}_{IDX:3}');
            $t->boolean('outline')->default(false);         // convertir texto a curvas
            $t->string('status')->default('queued');        // queued|processing|done|failed|partial
            $t->unsignedInteger('items_total')->default(0);
            $t->unsignedInteger('items_done')->default(0);
            $t->unsignedInteger('items_failed')->default(0);
            $t->string('artifact_path')->nullable();
            $t->text('error')->nullable();
            $t->timestamps();
        });

        Schema::create('print_batch_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('print_batch_id')->constrained('print_batches')->cascadeOnDelete();
            $t->unsignedInteger('index');                   // 1..n
            $t->json('payload');                            // {NOMBRE,NUMERO,TALLE,...}
            $t->string('status')->default('queued');        // queued|processing|done|failed
            $t->string('pdf_path')->nullable();
            $t->text('error')->nullable();
            $t->timestamps();

            $t->unique(['print_batch_id','index']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('print_batch_items');
        Schema::dropIfExists('print_batches');
    }
};

