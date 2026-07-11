<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_payment_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('invoice_client_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained()->nullOnDelete();
            $table->string('provider', 50)->default('stripe');
            $table->string('provider_attempt_id')->nullable();
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->string('status', 30)->default('failed');
            $table->text('failure_message')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('recovery_reminder_sent_at')->nullable();
            $table->timestamp('recovered_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status', 'failed_at']);
            $table->index(['client_invoice_id', 'status']);
            $table->unique(['provider', 'provider_attempt_id'], 'client_payment_attempt_provider_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_payment_attempts');
    }
};
