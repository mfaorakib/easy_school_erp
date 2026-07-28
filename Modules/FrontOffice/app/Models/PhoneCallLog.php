<?php

namespace Modules\FrontOffice\Models;

use App\Core\Concerns\BelongsToAcademicYear;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A phone call log entry. Year-scoped. */
class PhoneCallLog extends Model
{
    use BelongsToAcademicYear, SoftDeletes;

    public const TYPE_INCOMING = 'incoming';
    public const TYPE_OUTGOING = 'outgoing';

    protected $fillable = [
        'name', 'phone', 'call_type', 'call_date', 'call_duration',
        'description', 'next_follow_up_date', 'note', 'academic_year_id',
    ];

    protected $casts = [
        'call_date'           => 'date',
        'next_follow_up_date' => 'date',
    ];
}
