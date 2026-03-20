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
        Schema::create('db_level_admin', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code_data', 100);
            $table->string('level_name', 100);
            $table->string('data_menu', 100);
            $table->string('access_rights', 100)->nullable(); 
            $table->timestamps();

            $table->index('code_data', 'idx_leveladmin_code_data');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('db_level_admin');
    }
};
