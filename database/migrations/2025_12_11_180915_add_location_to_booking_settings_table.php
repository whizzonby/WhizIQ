<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booking_settings', function (Blueprint $table) {
            $table->string('business_address')->nullable()->after('welcome_message');
            $table->string('business_city')->nullable()->after('business_address');
            $table->string('business_country')->nullable()->after('business_city');
        });
    }

    public function down(): void
    {
        Schema::table('booking_settings', function (Blueprint $table) {
            $table->dropColumn(['business_address', 'business_city', 'business_country']);
        });
    }
};
