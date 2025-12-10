<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->boolean('is_blocked')->default(false)->after('status');
            $table->string('blocked_reason')->nullable()->after('is_blocked');
            $table->timestamp('blocked_at')->nullable()->after('blocked_reason');
            $table->foreignId('blocked_by_user_id')->nullable()->constrained('users')->nullOnDelete()->after('blocked_at');
            $table->integer('violation_count')->default(0)->after('blocked_by_user_id');

            // Index for quick blocked client lookups
            $table->index(['user_id', 'is_blocked']);
            $table->index(['email', 'is_blocked']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'is_blocked']);
            $table->dropIndex(['email', 'is_blocked']);
            $table->dropForeign(['blocked_by_user_id']);
            $table->dropColumn([
                'is_blocked',
                'blocked_reason',
                'blocked_at',
                'blocked_by_user_id',
                'violation_count',
            ]);
        });
    }
};
