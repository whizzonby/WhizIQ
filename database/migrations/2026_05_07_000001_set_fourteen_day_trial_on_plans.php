<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $dayIntervalId = DB::table('intervals')->where('slug', 'day')->value('id');

        if (! $dayIntervalId) {
            return;
        }

        // Plans seeded with has_trial=true but trial_interval_count=0 have no real trial.
        // Set them to 14 days using the day interval so auto-trial on onboarding works.
        // Uses DB::table() to bypass the Plan model observer (which would otherwise
        // delete payment provider data on trial_interval_id changes).
        DB::table('plans')
            ->where('is_active', true)
            ->where('has_trial', true)
            ->where('trial_interval_count', 0)
            ->update([
                'trial_interval_id'    => $dayIntervalId,
                'trial_interval_count' => 14,
                'updated_at'           => now(),
            ]);
    }

    public function down(): void
    {
        $monthIntervalId = DB::table('intervals')->where('slug', 'month')->value('id');

        if (! $monthIntervalId) {
            return;
        }

        DB::table('plans')
            ->where('is_active', true)
            ->where('has_trial', true)
            ->where('trial_interval_count', 14)
            ->update([
                'trial_interval_id'    => $monthIntervalId,
                'trial_interval_count' => 0,
                'updated_at'           => now(),
            ]);
    }
};
