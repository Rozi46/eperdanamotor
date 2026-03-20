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
        Schema::create('db_heat_lines', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code_data', 100);
            $table->string('code_heat', 100);
            $table->string('code_athlete', 100);
            $table->unsignedSmallInteger('line_number')->nullable(); 
            $table->string('best_time', 12)->nullable();
            $table->string('hasil', 12)->nullable();
            $table->integer('ranking')->nullable();
            $table->timestamps();

            $table->index('code_data', 'idx_heatlines_code_data');
            $table->index('code_heat', 'idx_heatlines_heat');
            $table->index('code_athlete', 'idx_heatlines_athlete');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('db_heat_lines');
    }
};
