<?php

namespace Modules\HumanResource\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Access\Enums\Role;
use Modules\Foundation\Models\Department;
use Modules\Foundation\Models\Designation;

/**
 * HR profile of a user (the reference system sm_staffs). A "teacher" is a staff whose user
 * holds the teacher role. Persists across academic years (not year-scoped).
 */
class Staff extends Model
{
    use SoftDeletes;

    protected $table = 'staff';

    protected $fillable = [
        'user_id', 'staff_no', 'first_name', 'last_name', 'full_name',
        'designation_id', 'department_id', 'gender_id', 'guardian_id',
        'date_of_birth', 'date_of_joining', 'leaving_date', 'mobile', 'email', 'photo',
        'qualification', 'current_address', 'permanent_address', 'basic_salary', 'is_active',
        'bank_name', 'bank_account_no', 'bank_branch',
    ];

    protected $casts = [
        'is_active'       => 'boolean',
        'date_of_birth'   => 'date',
        'date_of_joining' => 'date',
        'leaving_date'    => 'date',
        'basic_salary'    => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function displayName(): string
    {
        return $this->full_name ?: ($this->user?->name ?? '—');
    }

    /** Teachers only (the reference system whereTeacher()). */
    public function scopeTeachers($q)
    {
        return $q->where('is_active', true)
            ->whereHas('user', fn ($u) => $u->role(Role::Teacher->value));
    }
}
