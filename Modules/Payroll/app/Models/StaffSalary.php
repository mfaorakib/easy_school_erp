<?php

namespace Modules\Payroll\Models;

use App\Core\Concerns\BelongsToAcademicYear;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\HumanResource\Models\Staff;

/** Assigns a salary template to a staff member. */
class StaffSalary extends Model
{
    use BelongsToAcademicYear, SoftDeletes;

    protected $fillable = ['staff_id', 'salary_template_id', 'academic_year_id'];

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(SalaryTemplate::class, 'salary_template_id');
    }
}
