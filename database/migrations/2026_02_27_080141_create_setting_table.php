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
        Schema::create('db_setting', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('databasename', 100);
            $table->string('penyimpanan_excel', 100);
            $table->string('backup_database', 100);
            $table->string('backup_database_name', 100);
            $table->string('printer_kasir', 100);
            $table->string('report_dsn', 100);
            $table->string('manual_book', 100);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('db_setting');
    }
};
