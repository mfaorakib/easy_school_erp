<?php

namespace Modules\Homework\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\AcademicCore\Models\Student;

class HomeworkStudent extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'homework_id', 'student_id', 'is_complete', 'obtained_marks', 'comment', 'evaluated_on',
    ];

    protected $casts = [
        'is_complete'    => 'boolean',
        'obtained_marks' => 'decimal:2',
        'evaluated_on'   => 'date',
    ];

    public function homework(): BelongsTo
    {
        return $this->belongsTo(Homework::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
