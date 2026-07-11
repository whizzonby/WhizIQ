<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('client_invoices', function (Blueprint $table) {
            if (! Schema::hasColumn('client_invoices', 'payment_link_generated_at')) {
                $table->timestamp('payment_link_generated_at')->nullable()->after('reminder_count');
            }

            if (! Schema::hasColumn('client_invoices', 'payment_link_emailed_at')) {
                $table->timestamp('payment_link_emailed_at')->nullable()->after('payment_link_generated_at');
            }

            if (! Schema::hasColumn('client_invoices', 'payment_link_email_count')) {
                $table->unsignedInteger('payment_link_email_count')->default(0)->after('payment_link_emailed_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('client_invoices', function (Blueprint $table) {
            if (Schema::hasColumn('client_invoices', 'payment_link_email_count')) {
                $table->dropColumn('payment_link_email_count');
            }

            if (Schema::hasColumn('client_invoices', 'payment_link_emailed_at')) {
                $table->dropColumn('payment_link_emailed_at');
            }

            if (Schema::hasColumn('client_invoices', 'payment_link_generated_at')) {
                $table->dropColumn('payment_link_generated_at');
            }
        });
    }
};
