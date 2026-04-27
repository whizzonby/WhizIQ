<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointment_types', function (Blueprint $table) {
            $table->foreignId('aftercare_sequence_id')
                ->nullable()
                ->after('aftercare_template_id')
                ->constrained('aftercare_sequences')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('appointment_types', function (Blueprint $table) {
            $table->dropForeign(['aftercare_sequence_id']);
            $table->dropColumn('aftercare_sequence_id');
        });
    }
};
