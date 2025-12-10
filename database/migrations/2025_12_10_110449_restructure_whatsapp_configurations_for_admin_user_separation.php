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
        // Restructure user WhatsApp configurations to just store phone number
        // First, add new columns if they don't exist
        Schema::table('whats_app_configurations', function (Blueprint $table) {
            if (!Schema::hasColumn('whats_app_configurations', 'phone_number_id')) {
                $table->string('phone_number_id')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('whats_app_configurations', 'phone_number')) {
                $table->string('phone_number')->nullable()->after('phone_number_id');
            }
            if (!Schema::hasColumn('whats_app_configurations', 'phone_number_display')) {
                $table->string('phone_number_display')->nullable()->after('phone_number');
            }
        });

        // Then remove technical fields (moved to admin config)
        // Note: We'll keep them for now and remove in a separate migration after data migration if needed
        // This is safer for existing installations
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_admin_config');
        
        Schema::table('whats_app_configurations', function (Blueprint $table) {
            $table->string('business_account_id')->nullable();
            $table->text('access_token')->nullable();
            $table->string('verify_token')->nullable();
            $table->text('app_secret')->nullable();
            $table->string('api_version')->default('v21.0');
            $table->string('webhook_url')->nullable();
            $table->timestamp('webhook_verified_at')->nullable();
            
            $table->dropColumn([
                'phone_number_id',
                'phone_number',
                'phone_number_display',
            ]);
        });
    }
};
