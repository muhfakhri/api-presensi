<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create ('members', function (Blueprint $table) {
            $table->id();
            $table->string('no_hp', 15)->unique()->comment('Format: +628xxxxxxxxxx (enforce di application layer)');
            $table->foreignId('office_id')->constrained()->cascadeOnDelete();
            $table->string('nama_lengkap');
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->string('asal_sekolah');
            $table->date('tanggal_mulai_magang');
            $table->date('tanggal_selesai_magang')->nullable();
            $table->boolean('status_aktif')->default(true);
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
        
        // Ensure tanggal_selesai >= tanggal_mulai
        DB::statement('ALTER TABLE members ADD CONSTRAINT chk_valid_magang_dates CHECK (tanggal_selesai_magang IS NULL OR tanggal_selesai_magang >= tanggal_mulai_magang)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
