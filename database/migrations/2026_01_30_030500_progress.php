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
        Schema::create('progresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->date('tanggal');
            $table->text('description')->comment('Laporan kegiatan harian PKL');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Prevent duplicate progress per member per day (opsional, bisa dihapus jika butuh multiple entry per hari)
            $table->unique(['member_id', 'tanggal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('progresses');
    }
};
