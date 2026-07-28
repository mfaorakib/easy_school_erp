<?php

namespace Modules\Documents\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A certificate template with a placeholder-driven HTML body. Not year-scoped. */
class CertificateTemplate extends Model
{
    use SoftDeletes;

    public const HOLDER_STUDENT = 'student';
    public const HOLDER_STAFF = 'staff';
    public const HOLDER_GENERAL = 'general';

    protected $fillable = [
        'name', 'holder_type', 'document_prefix', 'heading', 'body',
        'header_image_path', 'background_image_path',
        'signature_left_path', 'signature_left_label',
        'signature_right_path', 'signature_right_label',
        'orientation', 'accent_color', 'font_family', 'border_style', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }
}
