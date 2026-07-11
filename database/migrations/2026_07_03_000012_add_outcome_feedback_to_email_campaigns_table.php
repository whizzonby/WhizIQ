<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('email_campaigns', function (Blueprint $table) {
            if (! Schema::hasColumn('email_campaigns', 'outcome_feedback')) {
                $table->string('outcome_feedback')->nullable()->after('dismissal_reason');
            }

            if (! Schema::hasColumn('email_campaigns', 'outcome_feedback_at')) {
                $table->timestamp('outcome_feedback_at')->nullable()->after('outcome_feedback');
            }
        });
    }

    public function down(): void
    {
        Schema::table('email_campaigns', function (Blueprint $table) {
            if (Schema::hasColumn('email_campaigns', 'outcome_feedback_at')) {
                $table->dropColumn('outcome_feedback_at');
            }

            if (Schema::hasColumn('email_campaigns', 'outcome_feedback')) {
                $table->dropColumn('outcome_feedback');
            }
        });
    }
};
