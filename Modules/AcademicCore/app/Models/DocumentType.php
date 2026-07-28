<?php

namespace Modules\AcademicCore\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Admin-configurable document catalog (Birth Certificate, Transfer Certificate, ...). */
class DocumentType extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'is_required', 'is_active', 'sort_order'];

    protected $casts = [
        'is_required' => 'boolean',
        'is_active'   => 'boolean',
    ];

    public function scopeActive($q)
    {
        return $q->where('is_active', true)->orderBy('sort_order')->orderBy('name');
    }
}
