<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('has_demo_data')->default(false)->after('email');
            $table->timestamp('demo_data_loaded_at')->nullable()->after('has_demo_data');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['has_demo_data', 'demo_data_loaded_at']);
        });
    }
};
