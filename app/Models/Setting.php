<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'type'];

    /**
     * Get a setting value with a default fallback
     */
    public static function getVal(string $key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        if (!$setting) {
            return $default;
        }

        switch ($setting->type) {
            case 'integer':
                return (int) $setting->value;
            case 'float':
                return (float) $setting->value;
            case 'boolean':
                return filter_var($setting->value, FILTER_VALIDATE_BOOLEAN);
            default:
                return $setting->value;
        }
    }

    /**
     * Get a setting value with a default fallback (alias)
     */
    public static function get(string $key, $default = null)
    {
        return self::getVal($key, $default);
    }

    /**
     * Set a setting value (alias)
     */
    public static function set(string $key, $value, string $type = 'string')
    {
        return self::setVal($key, $value, $type);
    }

    /**
     * Set a setting value
     */
    public static function setVal(string $key, $value, string $type = 'string')
    {
        return self::updateOrCreate(
            ['key' => $key],
            ['value' => (string) $value, 'type' => $type]
        );
    }
}
