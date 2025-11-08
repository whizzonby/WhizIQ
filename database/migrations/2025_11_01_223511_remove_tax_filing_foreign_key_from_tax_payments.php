<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('tax_payments')) {
            return;
        }

        // Check if foreign key exists before trying to drop it
        $foreignKeyExists = DB::select(
            "SELECT CONSTRAINT_NAME 
             FROM information_schema.KEY_COLUMN_USAGE 
             WHERE TABLE_SCHEMA = DATABASE() 
             AND TABLE_NAME = 'tax_payments' 
             AND CONSTRAINT_NAME = 'tax_payments_tax_filing_id_foreign' 
             AND REFERENCED_TABLE_NAME IS NOT NULL"
        );

        if (!empty($foreignKeyExists)) {
            Schema::table('tax_payments', function (Blueprint $table) {
                $table->dropForeign(['tax_filing_id']);
            });
        }

        // Drop column if it exists
        if (Schema::hasColumn('tax_payments', 'tax_filing_id')) {
            Schema::table('tax_payments', function (Blueprint $table) {
                $table->dropColumn('tax_filing_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tax_payments', function (Blueprint $table) {
            $table->foreignId('tax_filing_id')->nullable()->constrained()->onDelete('set null');
        });
    }
};
