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
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Owner contact details for self-notifications
            $table->string('owner_phone')->nullable();
            $table->enum('preferred_channel', ['sms', 'whatsapp', 'both'])->default('sms');

            // Daily digest
            $table->boolean('digest_enabled')->default(true);
            $table->time('digest_time')->default('08:00');

            // Owner instant alerts
            $table->boolean('alert_payment_received')->default(true);
            $table->boolean('alert_overdue_invoice')->default(true);
            $table->boolean('alert_stale_deals')->default(true);
            $table->boolean('alert_follow_up_due')->default(true);
            $table->boolean('alert_new_booking')->default(true);
            $table->boolean('alert_task_due')->default(false);

            // Client-facing automation
            $table->boolean('client_appointment_reminders')->default(true);
            $table->boolean('client_invoice_reminders')->default(true);
            $table->boolean('client_aftercare_enabled')->default(true);
            $table->boolean('client_booking_confirmation')->default(true);

            $table->timestamps();
            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
    }
};
