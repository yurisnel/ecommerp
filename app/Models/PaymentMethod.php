<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentMethod extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'type',
        'description',
        'icon',
        'requires_config',
        'config_fields',
        'sort_order',
        'status',
    ];

    protected $casts = [
        'requires_config' => 'boolean',
        'config_fields' => 'array',
        'sort_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the configuration for this payment method
     */
    public function configs(): HasMany
    {
        return $this->hasMany(PaymentMethodConfig::class);
    }

    /**
     * Get the payments using this method
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get configuration value by key
     */
    public function getConfig(string $key, $default = null)
    {
        $config = $this->configs()->where('key', $key)->first();
        return $config ? $config->value : $default;
    }

    /**
     * Set configuration value
     */
    public function setConfig(string $key, $value, bool $encrypt = false): void
    {
        $this->configs()->updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'is_encrypted' => $encrypt,
            ]
        );
    }

    /**
     * Check if method is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
