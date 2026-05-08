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
        if (! Schema::hasColumn('appointment_types', 'payment_type')) {
            Schema::table('appointment_types', function (Blueprint $table) {
                $table->enum('payment_type', ['none', 'invoice', 'upfront'])->default('invoice')->after('price');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointment_types', function (Blueprint $table) {
            $table->dropColumn('payment_type');
        });
    }
};
