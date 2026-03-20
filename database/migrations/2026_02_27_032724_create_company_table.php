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
        Schema::create('db_company', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code_data', 100);
            $table->string('nama_company', 100);
            $table->string('jenis', 100);
            $table->string('alamat')->nullable();
            $table->string('email', 50)->nullable();
            $table->string('keterangan', 50)->nullable();
            $table->string('foto', 120)->nullable();
            $table->timestamps();

            $table->index('code_data', 'idx_company_code_data');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('db_company');
    }
};
