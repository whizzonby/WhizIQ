<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('email_campaigns', function (Blueprint $table) {
            if (! Schema::hasColumn('email_campaigns', 'rescued_at')) {
                $table->timestamp('rescued_at')->nullable()->after('dismissal_reason');
            }

            if (! Schema::hasColumn('email_campaigns', 'rescue_reason')) {
                $table->string('rescue_reason')->nullable()->after('rescued_at');
            }

            $table->index(['automation_source', 'status', 'rescued_at'], 'email_campaigns_prepared_rescue_index');
        });
    }

    public function down(): void
    {
        Schema::table('email_campaigns', function (Blueprint $table) {
            $table->dropIndex('email_campaigns_prepared_rescue_index');

            if (Schema::hasColumn('email_campaigns', 'rescue_reason')) {
                $table->dropColumn('rescue_reason');
            }

            if (Schema::hasColumn('email_campaigns', 'rescued_at')) {
                $table->dropColumn('rescued_at');
            }
        });
    }
};
