<?php

namespace Modules\Fees\Models;

use App\Core\Concerns\BelongsToAcademicYear;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\AcademicCore\Models\Student;

class FeeCarryForward extends Model
{
    use BelongsToAcademicYear, SoftDeletes;

    protected $fillable = ['student_id', 'amount', 'paid', 'from_year_id', 'academic_year_id', 'note'];

    protected $casts = ['amount' => 'decimal:2', 'paid' => 'decimal:2'];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
