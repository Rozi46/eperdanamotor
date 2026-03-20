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
        Schema::create('db_clubs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code_data', 100);
            $table->string('nama_club', 100);
            $table->string('kota_asal', 100);
            $table->string('kontak', 20)->nullable();
            $table->string('logo', 120)->nullable();
            $table->timestamps();

            $table->index('code_data', 'idx_clubs_code_data');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('db_clubs');
    }
};
