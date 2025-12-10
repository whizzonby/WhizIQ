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
        Schema::create('blocked_clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // Business owner
            $table->foreignId('contact_id')->nullable()->constrained()->cascadeOnDelete(); // If linked to contact

            // Identifier fields for guest bookings
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('name')->nullable();

            // Violation details
            $table->enum('violation_type', [
                'no_show',
                'late_cancellation',
                'repeated_reschedule',
                'inappropriate_behavior',
                'payment_issue',
                'other'
            ]);
            $table->text('violation_details')->nullable();
            $table->timestamp('violation_date');

            // Block status
            $table->boolean('is_active')->default(true); // Can unblock by setting to false
            $table->timestamp('blocked_until')->nullable(); // For temporary blocks
            $table->text('resolution_notes')->nullable();
            $table->timestamp('resolved_at')->nullable();

            $table->timestamps();

            // Indexes for fast lookups
            $table->index(['user_id', 'is_active']);
            $table->index(['email', 'is_active']);
            $table->index(['phone', 'is_active']);
            $table->index(['contact_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blocked_clients');
    }
};
