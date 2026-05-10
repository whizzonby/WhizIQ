<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_client_id')->nullable()->constrained()->nullOnDelete();

            $table->string('quote_number');
            $table->string('status')->default('draft'); // draft, sent, accepted, rejected, expired
            $table->date('quote_date');
            $table->date('valid_until')->nullable();
            $table->string('currency', 3)->default('USD');

            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);

            $table->text('notes')->nullable();
            $table->text('terms')->nullable();
            $table->text('footer')->nullable();

            $table->string('template')->default('modern');
            $table->string('primary_color')->default('#3b82f6');
            $table->string('accent_color')->default('#10b981');
            $table->string('pdf_path')->nullable();

            $table->unique(['user_id', 'quote_number']);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotes');
    }
};
