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
        Schema::create('db_age_groups', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code_data', 100);
            $table->string('code_kelompok', 100);
            $table->string('nama_kelompok', 100);
            $table->integer('min_usia')->nullable();
            $table->integer('max_usia')->nullable();
            $table->timestamps();

            $table->index('code_data', 'idx_age_groups_code_data');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('db_age_groups');
    }
};
