<?php

namespace Modules\Leave\Models;

use App\Core\Concerns\BelongsToAcademicYear;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\HumanResource\Models\Staff;

/** Assigns a shift to a staff member. */
class StaffShift extends Model
{
    use BelongsToAcademicYear;

    protected $fillable = ['staff_id', 'shift_id', 'academic_year_id'];

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }
}
