<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsAppConfiguration extends Model
{
    protected $fillable = [
        'user_id',
        'phone_number_id',
        'phone_number',
        'phone_number_display',
        'is_active',
        'connected_at',
        'last_verified_at',
        // Legacy fields (will be removed after migration)
        'business_account_id',
        'access_token',
        'verify_token',
        'app_secret',
        'api_version',
        'webhook_url',
        'webhook_verified_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'connected_at' => 'datetime',
        'last_verified_at' => 'datetime',
        'webhook_verified_at' => 'datetime',
        'access_token' => 'encrypted',
        'app_secret' => 'encrypted',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Helper Methods
    public function isConfigured(): bool
    {
        // Check if admin has configured WhatsApp and user has connected their phone number
        $adminConfig = \App\Models\WhatsAppAdminConfig::getConfig();
        
        return $adminConfig 
            && $adminConfig->isConfigured() 
            && $adminConfig->is_active
            && !empty($this->phone_number_id);
    }

    public function markAsConnected(): void
    {
        $this->update([
            'is_active' => true,
            'connected_at' => now(),
            'last_verified_at' => now(),
        ]);
    }

    public function getApiBaseUrl(): string
    {
        $adminConfig = \App\Models\WhatsAppAdminConfig::getConfig();
        $apiVersion = $adminConfig?->api_version ?? 'v21.0';
        
        return "https://graph.facebook.com/{$apiVersion}/{$this->phone_number_id}";
    }

    // Static helper to get or create configuration for user
    public static function forUser(?int $userId = null): ?self
    {
        $userId = $userId ?? auth()->id();
        
        if (!$userId) {
            return null;
        }

        return static::firstOrCreate(
            ['user_id' => $userId],
            [
                'is_active' => false,
            ]
        );
    }
}
