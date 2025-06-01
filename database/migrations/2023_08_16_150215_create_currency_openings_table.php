<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('currency_openings', function (Blueprint $table) {
            $table->id();
            $table->integer('currency_id')->default('0')->comment('币种id');
            $table->timestamp('mon_begin')->nullable();
            $table->timestamp('mon_end')->nullable();
            $table->timestamp('tue_begin')->nullable();
            $table->timestamp('tue_end')->nullable();
            $table->timestamp('wed_begin')->nullable();
            $table->timestamp('wed_end')->nullable();
            $table->timestamp('thu_begin')->nullable();
            $table->timestamp('thu_end')->nullable();
            $table->timestamp('fin_begin')->nullable();
            $table->timestamp('fin_end')->nullable();
            $table->timestamp('sat_begin')->nullable();
            $table->timestamp('sat_end')->nullable();
            $table->timestamp('sun_begin')->nullable();
            $table->timestamp('sun_end')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currency_openings');
    }
};
