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
        Schema::table('appointment_types', function (Blueprint $table) {
            $table->foreignId('aftercare_template_id')->nullable()->constrained('aftercare_templates')->nullOnDelete();
            $table->boolean('enable_aftercare')->default(false);

            $table->index(['user_id', 'enable_aftercare']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointment_types', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'enable_aftercare']);
            $table->dropForeign(['aftercare_template_id']);
            $table->dropColumn(['aftercare_template_id', 'enable_aftercare']);
        });
    }
};
