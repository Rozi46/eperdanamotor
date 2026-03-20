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
        Schema::create('db_registrations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code_data', 100);
            $table->string('code_champion', 100);
            $table->string('code_athlete', 100);
            $table->string('code_event', 100);
            $table->string('code_age_groups', 100);
            $table->enum('status', ['Pending','Verified','Rejected'])->default('Pending');
            $table->string('payment_status', 100)->default('not_required');
            $table->string('documents', 100)->nullable();
            $table->string('notes', 100)->nullable();
            $table->timestamps('submitted_at');
            $table->timestamps('verified_at');
            $table->string('code_user', 100);
            $table->timestamps();
            
            $table->index('code_data', 'idx_registrations_code_data');
            $table->index('code_champion', 'idx_registrations_champion');
            $table->index('code_athlete', 'idx_registrations_athlete');
            $table->index('code_event', 'idx_registrations_event');
            $table->index('code_age_groups', 'idx_registrations_agegroups');
            $table->index('code_user', 'idx_registrations_user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('db_registrations');
    }
};
