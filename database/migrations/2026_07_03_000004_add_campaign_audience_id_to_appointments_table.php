<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            if (! Schema::hasColumn('appointments', 'campaign_audience_id')) {
                $table->foreignId('campaign_audience_id')
                    ->nullable()
                    ->after('client_invoice_id')
                    ->constrained('campaign_audiences')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            if (Schema::hasColumn('appointments', 'campaign_audience_id')) {
                $table->dropConstrainedForeignId('campaign_audience_id');
            }
        });
    }
};
