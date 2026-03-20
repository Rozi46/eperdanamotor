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
        Schema::create('db_results', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code_data', 100);
            $table->string('code_heatline', 100);
            $table->string('code_athlete', 100);
            $table->string('code_event', 100);
            $table->string('hasil', 12)->nullable();   // 00:59.32
            $table->string('foto', 120)->nullable();   // path foto
            $table->string('catatan', 5)->nullable();  // DNF= tidak finish  DSQ= diskualifikasi  NS=tidak start
            $table->unsignedSmallInteger('ranking')->nullable();            
            $table->unsignedSmallInteger('poin')->nullable();
            $table->enum('status_data', ['Proses','Finish'])->default('Proses');
            $table->timestamps();

            $table->index('code_data', 'idx_results_code_data');
            $table->index('code_event', 'idx_results_event');
            $table->index('code_heatline', 'idx_results_heatline');
            $table->index('code_athlete', 'idx_results_athlete');
            // query ranking per event
            $table->index(['code_event','ranking'], 'idx_results_event_rank');
            // hindari 1 atlet punya 2 hasil di 1 event
            $table->unique(['code_event','code_athlete'], 'uq_results_event_athlete');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('db_results');
    }
};
