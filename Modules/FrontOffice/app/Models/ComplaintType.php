<?php

namespace Modules\FrontOffice\Models;

use App\Core\Concerns\BelongsToAcademicYear;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A category of complaint. Year-scoped. */
class ComplaintType extends Model
{
    use BelongsToAcademicYear, SoftDeletes;

    protected $fillable = ['name', 'is_active', 'academic_year_id'];

    protected $casts = ['is_active' => 'boolean'];

    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class);
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }
}
