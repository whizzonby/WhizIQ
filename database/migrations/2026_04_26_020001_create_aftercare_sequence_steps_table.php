<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aftercare_sequence_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aftercare_sequence_id')->constrained()->cascadeOnDelete();

            // Ordering & timing
            $table->unsignedSmallInteger('step_order')->default(0);
            $table->unsignedInteger('delay_days')->default(0);
            $table->unsignedInteger('delay_hours')->default(0);
            $table->unsignedInteger('delay_minutes')->default(0);

            // Channel flags
            $table->boolean('send_whatsapp')->default(false);
            $table->boolean('send_sms')->default(false);

            // Message content (shared across channels; can diverge later)
            $table->text('message_body');

            // Optional rebooking CTA link (appended to message)
            $table->boolean('include_rebooking_link')->default(false);

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['aftercare_sequence_id', 'step_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aftercare_sequence_steps');
    }
};
