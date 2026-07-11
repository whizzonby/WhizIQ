<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('email_campaigns', function (Blueprint $table) {
            if (! Schema::hasColumn('email_campaigns', 'campaign_audience_id')) {
                $table->foreignId('campaign_audience_id')
                    ->nullable()
                    ->after('email_template_id')
                    ->constrained('campaign_audiences')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('email_campaigns', 'automation_source')) {
                $table->string('automation_source')->nullable()->after('reply_to');
            }

            if (! Schema::hasColumn('email_campaigns', 'prepared_at')) {
                $table->timestamp('prepared_at')->nullable()->after('automation_source');
            }

            if (! Schema::hasColumn('email_campaigns', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable()->after('prepared_at');
            }

            $table->index(['user_id', 'automation_source', 'status'], 'email_campaigns_automation_status_index');
        });
    }

    public function down(): void
    {
        Schema::table('email_campaigns', function (Blueprint $table) {
            $table->dropIndex('email_campaigns_automation_status_index');

            if (Schema::hasColumn('email_campaigns', 'reviewed_at')) {
                $table->dropColumn('reviewed_at');
            }

            if (Schema::hasColumn('email_campaigns', 'prepared_at')) {
                $table->dropColumn('prepared_at');
            }

            if (Schema::hasColumn('email_campaigns', 'automation_source')) {
                $table->dropColumn('automation_source');
            }

            if (Schema::hasColumn('email_campaigns', 'campaign_audience_id')) {
                $table->dropConstrainedForeignId('campaign_audience_id');
            }
        });
    }
};
