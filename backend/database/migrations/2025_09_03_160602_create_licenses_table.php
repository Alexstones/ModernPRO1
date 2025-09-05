<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLicensesTable extends Migration
{
    public function up(): void
    {
        Schema::create('licenses', function (Blueprint $t) {
            $t->id();
            $t->string('code');
            $t->string('device_id')->nullable(); // ligas al visitorId
            $t->timestamp('activated_at')->nullable();
            $t->date('expires_at')->nullable();
            $t->timestamps();

            $t->unique(['code', 'device_id'], 'licenses_code_device_unique');
            $t->index('device_id', 'licenses_device_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('licenses');
    }
}
