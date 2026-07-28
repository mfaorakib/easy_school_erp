<?php

namespace Modules\AcademicCore\Models;

use App\Core\Concerns\BelongsToAcademicYear;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/** School class / grade. Table `classes` ("Class" is a reserved word). */
class SchoolClass extends Model
{
    use BelongsToAcademicYear, SoftDeletes;

    protected $table = 'classes';

    protected $fillable = ['name', 'pass_mark', 'position', 'is_active', 'academic_year_id', 'template_id'];

    protected $casts = ['is_active' => 'boolean', 'pass_mark' => 'float'];

    public function classSections(): HasMany
    {
        return $this->hasMany(ClassSection::class, 'class_id');
    }

    public function sections(): BelongsToMany
    {
        return $this->belongsToMany(Section::class, 'class_sections', 'class_id', 'section_id');
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }
}
