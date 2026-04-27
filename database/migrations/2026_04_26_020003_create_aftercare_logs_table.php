<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aftercare_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained()->nullOnDelete();

            // Source: single template or sequence step
            $table->foreignId('aftercare_template_id')->nullable()->constrained('aftercare_templates')->nullOnDelete();
            $table->foreignId('aftercare_sequence_step_id')->nullable()->constrained('aftercare_sequence_steps')->nullOnDelete();

            $table->string('channel', 20); // 'whatsapp', 'sms', 'email'
            $table->string('status', 20)->default('pending'); // pending, sent, failed, skipped
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->text('error_message')->nullable();

            $table->timestamps();

            $table->index(['appointment_id', 'status']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aftercare_logs');
    }
};
