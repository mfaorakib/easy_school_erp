<?php

namespace Modules\AcademicCore\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Foundation\Models\BaseSetup;

/**
 * Student profile. Persists across years (NOT year-scoped). Current enrollment
 * is read from student_records where is_promote = 0 (the live record).
 */
class Student extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'guardian_id', 'admission_no', 'unique_id', 'admission_date',
        'first_name', 'last_name', 'full_name',
        'gender_id', 'religion_id', 'blood_group_id', 'date_of_birth',
        'email', 'mobile', 'photo', 'height', 'weight',
        'current_address', 'permanent_address', 'national_id_no',
        'student_category_id', 'student_group_id', 'admission_year_id', 'is_active',
    ];

    protected $casts = [
        'is_active'      => 'boolean',
        'admission_date' => 'date',
        'date_of_birth'  => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function guardian(): BelongsTo
    {
        return $this->belongsTo(Guardian::class);
    }

    public function gender(): BelongsTo
    {
        return $this->belongsTo(BaseSetup::class, 'gender_id');
    }

    public function records(): HasMany
    {
        return $this->hasMany(StudentRecord::class);
    }

    /** The live enrollment for the active year (is_promote = 0). */
    public function liveRecord(): HasMany
    {
        return $this->records()->where('is_promote', false);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(StudentDocument::class);
    }
}
