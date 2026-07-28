<?php

namespace Modules\Examination\Models;

use App\Core\Concerns\BelongsToAcademicYear;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GradeScale extends Model
{
    use BelongsToAcademicYear, SoftDeletes;

    protected $fillable = ['name', 'gpa', 'mark_from', 'mark_upto', 'academic_year_id'];

    protected $casts = [
        'gpa'       => 'decimal:2',
        'mark_from' => 'decimal:2',
        'mark_upto' => 'decimal:2',
    ];

    /** The grade row a percentage falls into (or null). */
    public static function forPercentage(float $percentage): ?self
    {
        return static::query()
            ->where('mark_from', '<=', $percentage)
            ->where('mark_upto', '>=', $percentage)
            ->orderByDesc('mark_from')
            ->first();
    }

    /** Overall grade for a computed GPA = highest grade whose GPA point ≤ the GPA. */
    public static function forGpa(float $gpa): ?self
    {
        return static::query()
            ->where('gpa', '<=', $gpa + 0.0001)
            ->orderByDesc('gpa')
            ->first();
    }
}
