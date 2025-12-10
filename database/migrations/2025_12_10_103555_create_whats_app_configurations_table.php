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
        Schema::create('whats_app_configurations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            
            // WhatsApp Business API Credentials
            $table->string('business_account_id')->nullable();
            $table->string('phone_number_id')->nullable();
            $table->text('access_token')->nullable(); // Encrypted
            $table->string('verify_token')->nullable();
            $table->text('app_secret')->nullable(); // Encrypted
            $table->string('api_version')->default('v21.0');
            
            // Status
            $table->boolean('is_active')->default(false);
            $table->timestamp('connected_at')->nullable();
            $table->timestamp('last_verified_at')->nullable();
            
            // Webhook configuration
            $table->string('webhook_url')->nullable();
            $table->timestamp('webhook_verified_at')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('user_id');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whats_app_configurations');
    }
};
