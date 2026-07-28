<?php

namespace Modules\DownloadCenter\Models;

use App\Core\Concerns\BelongsToAcademicYear;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\AcademicCore\Models\SchoolClass;
use Modules\AcademicCore\Models\Section;

class DownloadContent extends Model
{
    use BelongsToAcademicYear, SoftDeletes;

    protected $fillable = [
        'content_type_id', 'title', 'description', 'class_id', 'section_id',
        'audiences', 'file_path', 'external_url', 'publish_date', 'is_published',
        'academic_year_id', 'uploaded_by',
    ];

    protected $casts = [
        'audiences'    => 'array',
        'publish_date' => 'date',
        'is_published' => 'boolean',
    ];

    public function type(): BelongsTo
    {
        return $this->belongsTo(ContentType::class, 'content_type_id');
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function scopePublished($q)
    {
        return $q->where('is_published', true);
    }

    /** Content visible to a given role slug (audiences contains 'all' or the role). */
    public function scopeForRole($q, string $role)
    {
        return $q->where(function ($w) use ($role) {
            $w->whereJsonContains('audiences', 'all')
              ->orWhereJsonContains('audiences', $role)
              ->orWhereNull('audiences');
        });
    }

    public function link(): ?string
    {
        return $this->external_url ?: ($this->file_path ? asset('storage/'.$this->file_path) : null);
    }
}
