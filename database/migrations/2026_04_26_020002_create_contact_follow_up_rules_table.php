<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_follow_up_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();

            // Either a single template OR a sequence — only one should be set
            $table->foreignId('aftercare_template_id')->nullable()->constrained('aftercare_templates')->nullOnDelete();
            $table->foreignId('aftercare_sequence_id')->nullable()->constrained('aftercare_sequences')->nullOnDelete();

            // Channel preference override ('whatsapp', 'sms', or null = use default)
            $table->string('preferred_channel', 20)->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['user_id', 'contact_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_follow_up_rules');
    }
};
