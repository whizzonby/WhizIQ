<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campaign_audiences', function (Blueprint $table) {
            if (! Schema::hasColumn('campaign_audiences', 'recommendation_dismissed_at')) {
                $table->timestamp('recommendation_dismissed_at')->nullable()->after('last_launched_at');
            }

            if (! Schema::hasColumn('campaign_audiences', 'recommendation_snoozed_until')) {
                $table->timestamp('recommendation_snoozed_until')->nullable()->after('recommendation_dismissed_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('campaign_audiences', function (Blueprint $table) {
            if (Schema::hasColumn('campaign_audiences', 'recommendation_snoozed_until')) {
                $table->dropColumn('recommendation_snoozed_until');
            }

            if (Schema::hasColumn('campaign_audiences', 'recommendation_dismissed_at')) {
                $table->dropColumn('recommendation_dismissed_at');
            }
        });
    }
};
