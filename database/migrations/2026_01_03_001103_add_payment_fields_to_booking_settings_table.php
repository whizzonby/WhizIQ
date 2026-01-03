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
        Schema::table('booking_settings', function (Blueprint $table) {
            $table->text('payment_instructions')->nullable()->after('meeting_instructions');
            $table->string('payment_link')->nullable()->after('payment_instructions');
            $table->boolean('show_payment_in_email')->default(false)->after('payment_link');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking_settings', function (Blueprint $table) {
            $table->dropColumn(['payment_instructions', 'payment_link', 'show_payment_in_email']);
        });
    }
};
