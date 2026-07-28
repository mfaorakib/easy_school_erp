<?php

namespace Modules\FrontOffice\Models;

use App\Core\Concerns\BelongsToAcademicYear;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\HumanResource\Models\Staff;

/** A logged complaint. Year-scoped. */
class Complaint extends Model
{
    use BelongsToAcademicYear, SoftDeletes;

    public const STATUS_OPEN = 'open';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_RESOLVED = 'resolved';

    protected $fillable = [
        'complaint_type_id', 'complainant_name', 'phone', 'source', 'complaint_date',
        'description', 'action_taken', 'assigned_to', 'status', 'attachment_path', 'academic_year_id',
    ];

    protected $casts = ['complaint_date' => 'date'];

    public function type(): BelongsTo
    {
        return $this->belongsTo(ComplaintType::class, 'complaint_type_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'assigned_to');
    }
}
