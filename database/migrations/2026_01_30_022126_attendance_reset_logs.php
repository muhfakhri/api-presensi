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
        Schema::create('attendance_reset_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reset_by')->constrained('users')->cascadeOnDelete();
            
            // Old values before reset
            $table->enum('old_status', ['hadir', 'izin', 'sakit', 'alpha']);
            $table->time('old_check_in')->nullable();
            $table->time('old_check_out')->nullable();
            
            // New values after reset
            $table->enum('new_status', ['hadir', 'izin', 'sakit', 'alpha']);
            $table->time('new_check_in')->nullable();
            $table->time('new_check_out')->nullable();
            
            $table->text('reason'); // Why reset was needed
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_reset_logs');
    }
};
