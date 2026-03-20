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
        Schema::create('db_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code_data', 100);
            $table->string('code_event', 100);
            $table->string('code_gaya',100);
            $table->integer('jarak');
            $table->string('code_kategori', 100);
            $table->enum('gender', ['Putra', 'Putri']);
            $table->date('tanggal');
            $table->string('code_kejuaraan',100);
            $table->string('status_data',50)->nullable();
            $table->timestamps();            

            $table->index('code_data', 'idx_events_code_data');
            $table->index('code_gaya', 'idx_events_gaya');
            $table->index('code_kategori', 'idx_events_kategori');
            $table->index('code_kejuaraan', 'idx_events_kejuaraan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('db_events');
    }
};
