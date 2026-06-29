<?php

namespace Modules\Integration\app\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Integration extends Model
{
    // Fillable fields
    protected $fillable = [
        'name',
        'description',
        'type',
        'platform',
        'config',
        'status',
    ];


    // Accessor and Mutator for config
    protected function config(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? json_decode($value, true) : [],
            set: fn ($value) => empty($value) ? null : json_encode($value)
        );
    }

    // Helper method to get config value safely
    public function getConfigValue(string $key, $default = null)
    {
        return data_get($this->config, $key, $default);
    }

    // Helper method to set config value
    public function setConfigValue(string $key, $value): void
    {
        $config = $this->config;
        data_set($config, $key, $value);
        $this->config = $config;
        $this->save();
    }

    // Check if config has a key
    public function hasConfigKey(string $key): bool
    {
        return array_key_exists($key, $this->config);
    }

    // Merge new config with existing
    public function mergeConfig(array $newConfig): void
    {
        $this->config = array_merge($this->config, $newConfig);
        $this->save();
    }
}
