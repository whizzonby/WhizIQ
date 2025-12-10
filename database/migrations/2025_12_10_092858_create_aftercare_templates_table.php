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
        Schema::create('aftercare_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Template Info
            $table->string('name');
            $table->text('description')->nullable();

            // Message Content
            $table->string('email_subject')->nullable();
            $table->text('email_body')->nullable();
            $table->text('sms_message')->nullable();
            $table->text('whatsapp_message')->nullable();

            // Delivery Channels
            $table->boolean('send_via_email')->default(true);
            $table->boolean('send_via_sms')->default(false);
            $table->boolean('send_via_whatsapp')->default(false);

            // Timing Settings
            $table->integer('send_after_minutes')->default(0); // Send immediately after completion by default
            $table->integer('send_after_hours')->default(0);
            $table->integer('send_after_days')->default(0);

            // Template Variables Support
            $table->json('available_variables')->nullable();

            // Additional Features
            $table->boolean('include_rebooking_link')->default(true);
            $table->string('rebooking_cta_text')->default('Book Your Next Appointment');
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aftercare_templates');
    }
};
