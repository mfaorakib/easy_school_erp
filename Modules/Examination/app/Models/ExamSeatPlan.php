<?php

namespace Modules\Examination\Models;

use App\Core\Concerns\BelongsToAcademicYear;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/** One exam's seating plan — rooms used + per-student seat assignments. */
class ExamSeatPlan extends Model
{
    use BelongsToAcademicYear, SoftDeletes;

    protected $fillable = ['exam_id', 'seats_per_bench', 'mix_classes', 'academic_year_id'];

    protected $casts = ['mix_classes' => 'boolean'];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(ExamSeatPlanRoom::class)->orderBy('position');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(ExamSeatAssignment::class);
    }
}
