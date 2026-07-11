<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notification_preferences', function (Blueprint $table) {
            if (! Schema::hasColumn('notification_preferences', 'alert_campaign_draft_prepared')) {
                $table->boolean('alert_campaign_draft_prepared')->default(true)->after('alert_task_due');
            }
        });
    }

    public function down(): void
    {
        Schema::table('notification_preferences', function (Blueprint $table) {
            if (Schema::hasColumn('notification_preferences', 'alert_campaign_draft_prepared')) {
                $table->dropColumn('alert_campaign_draft_prepared');
            }
        });
    }
};
