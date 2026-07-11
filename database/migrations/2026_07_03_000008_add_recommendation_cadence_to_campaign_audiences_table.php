<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campaign_audiences', function (Blueprint $table) {
            if (! Schema::hasColumn('campaign_audiences', 'recommendation_cadence_days')) {
                $table->unsignedSmallInteger('recommendation_cadence_days')->default(7)->after('recommendation_history');
            }
        });
    }

    public function down(): void
    {
        Schema::table('campaign_audiences', function (Blueprint $table) {
            if (Schema::hasColumn('campaign_audiences', 'recommendation_cadence_days')) {
                $table->dropColumn('recommendation_cadence_days');
            }
        });
    }
};
