<?php

namespace Modules\Leave\Models;

use App\Core\Concerns\BelongsToAcademicYear;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\HumanResource\Models\Staff;

/** A teacher evaluation with JSON criteria + average score. Year-scoped. */
class TeacherEvaluation extends Model
{
    use BelongsToAcademicYear, SoftDeletes;

    protected $fillable = [
        'staff_id', 'term', 'evaluation_date', 'criteria', 'total_score',
        'remarks', 'evaluated_by', 'academic_year_id',
    ];

    protected $casts = [
        'criteria'        => 'array',
        'evaluation_date' => 'date',
        'total_score'     => 'decimal:2',
    ];

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluated_by');
    }
}
