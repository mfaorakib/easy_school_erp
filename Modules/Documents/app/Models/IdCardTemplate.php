<?php

namespace Modules\Documents\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A designable ID-card template. Not year-scoped. */
class IdCardTemplate extends Model
{
    use SoftDeletes;

    public const HOLDER_STUDENT = 'student';
    public const HOLDER_STAFF = 'staff';

    /** Optional detail rows a template may print. */
    public const AVAILABLE_FIELDS = ['roll', 'blood_group', 'phone', 'address', 'dob', 'session', 'department'];

    protected $fillable = [
        'name', 'holder_type', 'title', 'background_color', 'text_color',
        'logo_path', 'background_image_path', 'signature_path', 'signature_label',
        'fields', 'footer_text', 'orientation', 'is_active',
    ];

    protected $casts = [
        'fields'    => 'array',
        'is_active' => 'boolean',
    ];

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }

    public function showsField(string $key): bool
    {
        return in_array($key, (array) $this->fields, true);
    }
}
