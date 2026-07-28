<?php

namespace Modules\Foundation\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['group', 'key', 'value', 'type'];

    /** Cast the raw stored string to its declared type. */
    public function typedValue(): mixed
    {
        return match ($this->type) {
            'bool'  => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            'int'   => (int) $this->value,
            'json'  => json_decode($this->value ?? 'null', true),
            default => $this->value,
        };
    }
}
