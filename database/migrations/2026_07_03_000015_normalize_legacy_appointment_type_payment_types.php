<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * 'upfront'/'deposit' payment types are no longer offered in the admin
     * form and nothing in the booking flow collects payment before
     * confirmation for them anymore. Normalize any existing rows to
     * 'invoice' so behavior matches what owners see and can configure.
     */
    public function up(): void
    {
        DB::table('appointment_types')
            ->whereIn('payment_type', ['upfront', 'deposit'])
            ->update(['payment_type' => 'invoice']);
    }

    public function down(): void
    {
        // Not reversible: original upfront/deposit distinction is not preserved.
    }
};
