<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Receipt identity
            $table->string('receipt_number')->unique();
            $table->date('receipt_date');

            // Customer — optional link to InvoiceClient, or free-text for walk-ins
            $table->foreignId('invoice_client_id')->nullable()->constrained()->nullOnDelete();
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();

            // Payment details
            $table->string('payment_method')->default('cash'); // cash, credit_card, bank_transfer, check, paypal, other
            $table->string('transaction_reference')->nullable();
            $table->decimal('amount_tendered', 10, 2)->nullable(); // for cash payments
            $table->decimal('change_due', 10, 2)->nullable();      // for cash payments

            // Financials
            $table->string('currency', 3)->default('USD');
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);

            // Items stored as JSON (simpler than a separate table for receipts)
            $table->json('items')->nullable();

            // Optional link to source invoice
            $table->foreignId('linked_invoice_id')->nullable()->references('id')->on('client_invoices')->nullOnDelete();

            // Presentation
            $table->string('template')->default('standard'); // standard, thermal
            $table->string('primary_color')->default('#10b981');
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receipts');
    }
};
