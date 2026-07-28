<?php

namespace Modules\Settings\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A payment method usable across fees/accounting. `driver` = manual (cash,
 * bank, or a mobile-banking channel with no live API — staff/guardian records
 * the transaction reference by hand) or a real gateway (stripe|bkash|nagad),
 * resolved by `Fees\Services\Gateways\PaymentGatewayManager`. `config` holds
 * driver-specific credentials — encrypted at rest, never plain text.
 */
class PaymentMethod extends Model
{
    use SoftDeletes;

    public const DRIVER_MANUAL = 'manual';

    public const DRIVER_STRIPE = 'stripe';

    public const DRIVER_BKASH = 'bkash';

    public const DRIVER_NAGAD = 'nagad';

    protected $fillable = ['name', 'driver', 'config', 'position', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'config'    => 'encrypted:array',
    ];

    public function scopeActive($q)
    {
        return $q->where('is_active', true)->orderBy('position')->orderBy('id');
    }

    /** Manual channels (cash/bank/unintegrated mobile banking) skip the gateway checkout entirely. */
    public function isGateway(): bool
    {
        return $this->driver !== self::DRIVER_MANUAL;
    }

    /** A driver-specific config value, e.g. $method->configValue('sandbox', true). */
    public function configValue(string $key, mixed $default = null): mixed
    {
        return data_get($this->config, $key, $default);
    }
}
