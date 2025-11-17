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
        Schema::table('tax_categories', function (Blueprint $table) {
            $table->text('confirmation_prompt')->nullable()->after('deduction_behavior');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tax_categories', function (Blueprint $table) {
            $table->dropColumn('confirmation_prompt');
        });
    }
};
