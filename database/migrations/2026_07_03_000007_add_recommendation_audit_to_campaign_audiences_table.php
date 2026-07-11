<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campaign_audiences', function (Blueprint $table) {
            if (! Schema::hasColumn('campaign_audiences', 'recommendation_history')) {
                $table->json('recommendation_history')->nullable()->after('recommendation_snoozed_until');
            }
        });
    }

    public function down(): void
    {
        Schema::table('campaign_audiences', function (Blueprint $table) {
            if (Schema::hasColumn('campaign_audiences', 'recommendation_history')) {
                $table->dropColumn('recommendation_history');
            }
        });
    }
};
