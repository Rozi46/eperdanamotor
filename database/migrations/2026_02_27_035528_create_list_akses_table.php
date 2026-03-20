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
        Schema::create('db_list_akses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedSmallInteger('no_urut');
            $table->string('nama_menu', 100);
            $table->string('nama_akses', 100);
            $table->string('menu_index', 100)->nullable(); 
            $table->enum('menu', ['Yes','No']); 
            $table->enum('submenu', ['Yes','No']); 
            $table->enum('action', ['Yes','No']); 
            $table->enum('subaction', ['Yes','No']); 
            $table->string('icon_menu', 100);
            $table->enum('status_data', ['Aktif','Tidak Aktif'])->default('Aktif');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('db_list_akses');
    }
};
