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
        Schema::create('db_users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code_data', 100);
            $table->string('full_name', 100);
            $table->string('email', 100)->unique();
            $table->string('password');
            $table->string('phone_number', 20);
            $table->string('level', 100);
            $table->string('image')->default('no_img');
            $table->string('status_data', 20);
            $table->text('key_token');
            $table->string('tipe_user', 100);
            $table->string('tipe_login', 100);
            $table->string('code_company', 100);
            $table->timestamps();            

            $table->index('code_data', 'idx_users_code_data');
            $table->index('level', 'idx_users_level');
            $table->index('code_company', 'idx_users_code_company');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('db_users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
