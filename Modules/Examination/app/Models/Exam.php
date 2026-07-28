<?php

namespace Modules\Examination\Models;

use App\Core\Concerns\BelongsToAcademicYear;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Exam extends Model
{
    use BelongsToAcademicYear, SoftDeletes;

    protected $fillable = ['name', 'exam_type_id', 'is_active', 'academic_year_id'];

    protected $casts = ['is_active' => 'boolean'];

    public function type(): BelongsTo
    {
        return $this->belongsTo(ExamType::class, 'exam_type_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(ExamSchedule::class);
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }
}
