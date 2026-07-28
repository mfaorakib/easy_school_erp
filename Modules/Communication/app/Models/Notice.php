<?php

namespace Modules\Communication\Models;

use App\Core\Concerns\BelongsToAcademicYear;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notice extends Model
{
    use BelongsToAcademicYear, SoftDeletes;

    protected $fillable = [
        'title', 'description', 'notice_date', 'publish_date',
        'audiences', 'is_published', 'created_by', 'academic_year_id',
    ];

    protected $casts = [
        'notice_date'  => 'date',
        'publish_date' => 'date',
        'audiences'    => 'array',
        'is_published' => 'boolean',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopePublished($q)
    {
        return $q->where('is_published', true);
    }

    /** Notices visible to a given role slug (or targeted to everyone). */
    public function scopeForRole($q, string $role)
    {
        return $q->where(function ($w) use ($role) {
            $w->whereJsonContains('audiences', 'all')
              ->orWhereJsonContains('audiences', $role);
        });
    }
}
