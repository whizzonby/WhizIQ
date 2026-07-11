<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('email_campaigns', function (Blueprint $table) {
            if (! Schema::hasColumn('email_campaigns', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->after('reviewed_at');
            }

            if (! Schema::hasColumn('email_campaigns', 'dismissed_at')) {
                $table->timestamp('dismissed_at')->nullable()->after('expires_at');
            }

            if (! Schema::hasColumn('email_campaigns', 'dismissal_reason')) {
                $table->string('dismissal_reason')->nullable()->after('dismissed_at');
            }

            $table->index(['automation_source', 'status', 'expires_at'], 'email_campaigns_prepared_expiry_index');
        });
    }

    public function down(): void
    {
        Schema::table('email_campaigns', function (Blueprint $table) {
            $table->dropIndex('email_campaigns_prepared_expiry_index');

            if (Schema::hasColumn('email_campaigns', 'dismissal_reason')) {
                $table->dropColumn('dismissal_reason');
            }

            if (Schema::hasColumn('email_campaigns', 'dismissed_at')) {
                $table->dropColumn('dismissed_at');
            }

            if (Schema::hasColumn('email_campaigns', 'expires_at')) {
                $table->dropColumn('expires_at');
            }
        });
    }
};
