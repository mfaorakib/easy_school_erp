<?php

namespace Modules\Fees\Models;

use App\Core\Concerns\BelongsToAcademicYear;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\AcademicCore\Models\Student;

/**
 * An ad-hoc, one-off fine against a student — NOT part of the structured Fee
 * Group/Type/Master/Assignment hierarchy. Year-scoped. Once collected, a real
 * accounting income entry is posted (see StudentFineService::collect()).
 */
class StudentFine extends Model
{
    use BelongsToAcademicYear, SoftDeletes;

    protected $fillable = [
        'student_id', 'academic_year_id', 'receipt_no', 'reason', 'amount',
        'fine_date', 'is_collected', 'collected_at', 'imposed_by',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'fine_date'    => 'date',
        'is_collected' => 'boolean',
        'collected_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function imposedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'imposed_by');
    }

    /**
     * Single source of truth for how a fined student is displayed — reused by
     * both the index table and the receipt so the two views can't drift apart.
     */
    public function studentLabel(): string
    {
        $name = $this->student->full_name ?? '—';
        $record = $this->student->records->first(); // already eager-loaded + .live()-scoped by the controller

        if (! $record) {
            return $name;
        }

        $classSection = implode(' - ', array_filter([
            optional($record->schoolClass)->name,
            optional($record->section)->name,
        ]));

        $segments = array_filter([$classSection, $record->roll_no ? "Roll {$record->roll_no}" : null]);

        return $segments ? "{$name} · ".implode(' · ', $segments) : $name;
    }
}
