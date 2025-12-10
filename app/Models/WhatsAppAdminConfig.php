<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsAppAdminConfig extends Model
{
    protected $table = 'whatsapp_admin_config';

    protected $fillable = [
        'business_account_id',
        'access_token',
        'verify_token',
        'app_secret',
        'api_version',
        'is_active',
        'configured_at',
        'last_verified_at',
        'webhook_url',
        'webhook_verified_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'configured_at' => 'datetime',
        'last_verified_at' => 'datetime',
        'webhook_verified_at' => 'datetime',
        'access_token' => 'encrypted',
        'app_secret' => 'encrypted',
    ];

    // Helper Methods
    public function isConfigured(): bool
    {
        return !empty($this->access_token) 
            && !empty($this->business_account_id);
    }

    public function markAsConfigured(): void
    {
        $this->update([
            'is_active' => true,
            'configured_at' => now(),
            'last_verified_at' => now(),
        ]);
    }

    // Static helper to get the admin config (singleton)
    // Cached to reduce database queries
    public static function getConfig(): ?self
    {
        return cache()->remember('whatsapp_admin_config', 3600, function () {
            return static::first() ?? static::create([
                'api_version' => 'v21.0',
                'is_active' => false,
            ]);
        });
    }

    // Clear cache when config is updated
    public static function clearCache(): void
    {
        cache()->forget('whatsapp_admin_config');
    }

    // Override save/update methods to clear cache
    public function save(array $options = []): bool
    {
        $result = parent::save($options);
        static::clearCache();
        return $result;
    }

    public function update(array $attributes = [], array $options = []): bool
    {
        $result = parent::update($attributes, $options);
        static::clearCache();
        return $result;
    }
}
