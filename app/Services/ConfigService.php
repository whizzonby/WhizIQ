<?php

namespace App\Services;

use App\Constants\ConfigConstants;
use App\Models\Config;
use Exception;

class ConfigService
{
    public function isAdminSettingsEnabled()
    {
        return config('app.admin_settings.enabled', false);
    }

    public function loadConfigs()
    {
        if (! $this->isAdminSettingsEnabled()) {
            return;
        }

        $configs = cache()->many(ConfigConstants::OVERRIDABLE_CONFIGS);

        $missingKeys = array_keys(array_filter($configs, fn ($value) => is_null($value)));

        // Re-sync from the database whenever ANY overridable config is
        // missing from cache - not only when every key is missing. A
        // partial cache eviction (e.g. just one key expiring/getting
        // cleared) previously made the app silently ignore an admin-set
        // value for that one key and fall back to the deploy-time config
        // default instead, even though every other setting still worked.
        if (! empty($missingKeys)) {
            $dbConfigs = Config::getAll();

            foreach ($missingKeys as $key) {
                $dbValue = $dbConfigs[$key] ?? null;

                if (! is_null($dbValue)) {
                    cache()->forever($key, $dbValue);
                }

                $configs[$key] = $dbValue;
            }
        }

        config($this->toKeyValueArray($configs));
    }

    public function set(string $key, $value): void
    {
        if (! in_array($key, ConfigConstants::OVERRIDABLE_CONFIGS)) {
            throw new Exception("Config key $key is not overridable");
        }

        Config::set($key, $value);

        cache()->forever($key, $value);

        config([$key => $value]);
    }

    public function exportAllConfigs(): void
    {
        if (! $this->isAdminSettingsEnabled()) {
            return;
        }

        $configs = Config::getAll();

        foreach ($configs as $key => $value) {
            cache()->forever($key, $value);
        }
    }

    /**
     * This is a one-time operation to encrypt sensitive configs to migrate non-encrypted sensitive configs to be encrypted.
     */
    public function encryptSensitiveConfigs()
    {
        foreach (ConfigConstants::ENCRYPTED_CONFIGS as $key) {
            $value = Config::get($key);
            if ($value) {
                Config::set($key, $value);
            }
        }
    }

    public function get(string $key, ?string $default = null): string|array|null
    {
        try {
            return Config::get($key) ?? config($key) ?? $default;
        } catch (Exception $e) {
            return $default;
        }
    }

    private function toKeyValueArray($configs): array
    {
        $result = [];
        foreach ($configs as $key => $value) {
            if (is_null($value)) {
                continue;
            }

            $result[$key] = $value;
        }

        return $result;
    }
}
