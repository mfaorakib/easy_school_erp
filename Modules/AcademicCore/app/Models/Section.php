<?php

namespace Modules\AcademicCore\Models;

use App\Core\Concerns\BelongsToAcademicYear;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Section extends Model
{
    use BelongsToAcademicYear, SoftDeletes;

    protected $fillable = ['name', 'capacity', 'is_active', 'academic_year_id'];

    protected $casts = ['is_active' => 'boolean'];

    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(SchoolClass::class, 'class_sections', 'section_id', 'class_id');
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }
}
