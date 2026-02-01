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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();

            $table->time('check_in_time')->nullable();
            $table->time('check_out_time')->nullable();

            $table->enum('status', ['hadir', 'izin', 'sakit', 'alpha'])->default('alpha');

            $table->timestamps();
            $table->softDeletes();

            // Prevent duplicate attendance per member per day
            $table->unique(['member_id', 'tanggal']);
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
