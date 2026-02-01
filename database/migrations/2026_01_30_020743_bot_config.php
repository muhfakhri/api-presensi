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
        Schema::create('bot_configs', function (Blueprint $table) {
            $table->id();
            
            // WAHA Connection Settings
            $table->string('waha_api_url')->default('http://localhost:3000')->comment('WAHA API base URL');
            $table->string('waha_api_key')->nullable()->comment('WAHA API authentication key');
            $table->string('waha_session_name')->default('attendance_bot')->comment('WhatsApp session identifier');
            
            // Webhook Configuration
            $table->string('webhook_url')->nullable()->comment('Callback URL untuk WAHA events');
            $table->string('webhook_secret')->nullable()->comment('Secret untuk verify webhook signature');
            $table->json('webhook_events')->nullable()->comment('Array events: ["message", "session.status"]');
            
            // Reminder Schedule Settings
            $table->time('reminder_check_in_time')->default('08:00:00')->comment('Waktu kirim reminder check-in');
            $table->time('reminder_check_out_time')->default('17:00:00')->comment('Waktu kirim reminder check-out');
            $table->string('timezone', 50)->default('Asia/Jakarta')->comment('Timezone untuk scheduler');
            $table->boolean('reminder_enabled')->default(true)->comment('Master switch untuk reminder system');
            
            // Bot Behavior Settings
            $table->unsignedSmallInteger('typing_delay_ms')->default(1000)->comment('Typing simulation delay (ms)');
            $table->boolean('mark_messages_read')->default(true)->comment('Auto-mark incoming messages as read');
            $table->boolean('reject_calls')->default(false)->comment('Auto-reject incoming calls');
            
            // Message Templates
            $table->text('message_greeting')->nullable()->comment('Pesan sapaan awal bot');
            $table->text('message_remind_check_in')->nullable()->comment('Template reminder check-in');
            $table->text('message_remind_check_out')->nullable()->comment('Template reminder check-out');
            $table->text('message_success_check_in')->nullable()->comment('Konfirmasi sukses check-in');
            $table->text('message_success_check_out')->nullable()->comment('Konfirmasi sukses check-out');
            $table->text('message_already_checked_in')->nullable()->comment('Pesan sudah absen hari ini');
            $table->text('message_error')->nullable()->comment('Pesan error generic');
            
            $table->boolean('is_active')->default(true)->comment('Config aktif/nonaktif');
            $table->timestamps();
            
            // Note: This is a singleton table (should only have 1 row with id=1)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bot_configs');
    }
};
