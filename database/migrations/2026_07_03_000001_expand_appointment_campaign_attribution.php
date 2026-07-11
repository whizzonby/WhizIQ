<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            if (! Schema::hasColumn('appointments', 'client_invoice_id')) {
                $table->foreignId('client_invoice_id')
                    ->nullable()
                    ->after('contact_id')
                    ->constrained('client_invoices')
                    ->nullOnDelete();
            }
        });

        match (DB::getDriverName()) {
            'mysql', 'mariadb' => DB::statement("ALTER TABLE appointments MODIFY booked_via VARCHAR(50) NOT NULL DEFAULT 'admin'"),
            'pgsql' => DB::statement("ALTER TABLE appointments ALTER COLUMN booked_via TYPE VARCHAR(50)"),
            default => null,
        };
    }

    public function down(): void
    {
        match (DB::getDriverName()) {
            'mysql', 'mariadb' => DB::statement("ALTER TABLE appointments MODIFY booked_via ENUM('admin', 'public_form') NOT NULL DEFAULT 'admin'"),
            'pgsql' => DB::statement("ALTER TABLE appointments ALTER COLUMN booked_via TYPE VARCHAR(20)"),
            default => null,
        };

        Schema::table('appointments', function (Blueprint $table) {
            if (Schema::hasColumn('appointments', 'client_invoice_id')) {
                $table->dropConstrainedForeignId('client_invoice_id');
            }
        });
    }
};
