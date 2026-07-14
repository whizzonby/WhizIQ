<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            if (! Schema::hasColumn('subscriptions', 'first_reminder_sent_at')) {
                $table->timestamp('first_reminder_sent_at')->nullable()->after('trial_ends_at');
            }

            if (! Schema::hasColumn('subscriptions', 'second_reminder_sent_at')) {
                $table->timestamp('second_reminder_sent_at')->nullable()->after('first_reminder_sent_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            if (Schema::hasColumn('subscriptions', 'second_reminder_sent_at')) {
                $table->dropColumn('second_reminder_sent_at');
            }

            if (Schema::hasColumn('subscriptions', 'first_reminder_sent_at')) {
                $table->dropColumn('first_reminder_sent_at');
            }
        });
    }
};
