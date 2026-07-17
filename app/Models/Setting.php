<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Setting Model
 */
class Setting extends Model
{
    protected string $table = 'settings';

    /**
     * Get a setting value by key.
     */
    public function getValue(string $key, mixed $default = null): mixed
    {
        $setting = $this->findOneBy('setting_key', $key);

        if (!$setting) {
            return $default;
        }

        return match ($setting['setting_type']) {
            'number' => is_numeric($setting['setting_value']) ? (float) $setting['setting_value'] : $default,
            'boolean' => filter_var($setting['setting_value'], FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($setting['setting_value'], true) ?? $default,
            default => $setting['setting_value'] ?? $default,
        };
    }

    /**
     * Set a setting value.
     */
    public function setValue(string $key, mixed $value, string $type = 'string'): void
    {
        $existing = $this->findOneBy('setting_key', $key);

        $storeValue = match ($type) {
            'json' => json_encode($value),
            'boolean' => $value ? '1' : '0',
            default => (string) $value,
        };

        if ($existing) {
            $this->update($existing['id'], [
                'setting_value' => $storeValue,
                'setting_type' => $type,
            ]);
        } else {
            $this->create([
                'setting_key' => $key,
                'setting_value' => $storeValue,
                'setting_type' => $type,
            ]);
        }
    }

    /**
     * Get all settings as key-value pairs.
     */
    public function getAllAsArray(): array
    {
        $settings = $this->all('setting_key', 'ASC');
        $result = [];

        foreach ($settings as $setting) {
            $result[$setting['setting_key']] = $setting['setting_value'];
        }

        return $result;
    }
}
