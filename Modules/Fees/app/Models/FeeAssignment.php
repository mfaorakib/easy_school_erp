<?php

namespace Modules\Fees\Models;

use App\Core\Concerns\BelongsToAcademicYear;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\AcademicCore\Models\Student;

/**
 * One fee owed by one student. Money math (net payable, paid, due, fine) is
 * computed by FeeService; this model holds relations + the cached status.
 */
class FeeAssignment extends Model
{
    use BelongsToAcademicYear, SoftDeletes;

    protected $fillable = ['fee_master_id', 'student_id', 'academic_year_id', 'status'];

    public function master(): BelongsTo
    {
        return $this->belongsTo(FeeMaster::class, 'fee_master_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(FeePayment::class);
    }

    public function discounts(): BelongsToMany
    {
        return $this->belongsToMany(FeeDiscount::class, 'fee_assignment_discount')
            ->withPivot(['reason', 'applied_by', 'applied_at'])
            ->withTimestamps();
    }

    public function intents(): HasMany
    {
        return $this->hasMany(FeePaymentIntent::class);
    }
}
