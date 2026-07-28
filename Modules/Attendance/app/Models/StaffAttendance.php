<?php

namespace Modules\Attendance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Attendance\Enums\AttendanceStatus;
use Modules\HumanResource\Models\Staff;

class StaffAttendance extends Model
{
    use SoftDeletes;

    protected $fillable = ['staff_id', 'date', 'status', 'note'];

    protected $casts = [
        'date'   => 'date',
        'status' => AttendanceStatus::class,
    ];

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }
}
