<?php

namespace Modules\FrontOffice\Models;

use App\Core\Concerns\BelongsToAcademicYear;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A visitor logbook entry. Year-scoped. */
class Visitor extends Model
{
    use BelongsToAcademicYear, SoftDeletes;

    protected $fillable = [
        'name', 'phone', 'purpose', 'to_meet', 'id_card', 'no_of_person',
        'visit_date', 'in_time', 'out_time', 'note', 'academic_year_id',
    ];

    protected $casts = ['visit_date' => 'date'];

    public function isCheckedOut(): bool
    {
        return ! empty($this->out_time);
    }
}
