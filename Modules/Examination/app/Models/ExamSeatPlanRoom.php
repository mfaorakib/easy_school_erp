<?php

namespace Modules\Examination\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Timetable\Models\Classroom;

/** One room used by a seat plan, and the order it fills in. */
class ExamSeatPlanRoom extends Model
{
    protected $fillable = ['exam_seat_plan_id', 'classroom_id', 'position'];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(ExamSeatPlan::class, 'exam_seat_plan_id');
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }
}
