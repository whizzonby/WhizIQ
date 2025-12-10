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
        Schema::create('whatsapp_admin_config', function (Blueprint $table) {
            $table->id();
            
            // WhatsApp Business API Credentials (set by admin)
            $table->string('business_account_id')->nullable();
            $table->text('access_token')->nullable(); // Encrypted
            $table->string('verify_token')->nullable();
            $table->text('app_secret')->nullable(); // Encrypted
            $table->string('api_version')->default('v21.0');
            
            // Status
            $table->boolean('is_active')->default(false);
            $table->timestamp('configured_at')->nullable();
            $table->timestamp('last_verified_at')->nullable();
            
            // Webhook configuration
            $table->string('webhook_url')->nullable();
            $table->timestamp('webhook_verified_at')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_admin_config');
    }
};
