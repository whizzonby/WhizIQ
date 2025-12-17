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
        Schema::table('client_invoices', function (Blueprint $table) {
            // Drop the existing unique constraint on invoice_number
            $table->dropUnique(['invoice_number']);
            
            // Add composite unique constraint on user_id and invoice_number
            // This allows the same invoice number for different users
            $table->unique(['user_id', 'invoice_number'], 'client_invoices_user_invoice_number_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('client_invoices', function (Blueprint $table) {
            // Drop the composite unique constraint
            $table->dropUnique('client_invoices_user_invoice_number_unique');
            
            // Restore the original global unique constraint on invoice_number
            $table->unique('invoice_number');
        });
    }
};
