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
        Schema::create('db_swimming_styles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code_data', 100);
            $table->string('nama_gaya', 100);
            $table->string('istilah', 100)->nullable();
            $table->timestamps();

            $table->index('code_data', 'idx_swimmingstyles_code_data');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('db_swimming_styles');
    }
};
