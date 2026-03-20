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
        Schema::create('db_championships', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code_data', 100);
            $table->string('nama_kejuaraan', 100);
            $table->string('lokasi');
            $table->unsignedSmallInteger('jumlah_line')->nullable();  
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->timestamps();

            $table->index('code_data', 'idx_championships_code_data');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('db_championships');
    }
};
