<?php

namespace Modules\Library\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class LibraryMember extends Model
{
    use SoftDeletes;

    protected $fillable = ['user_id', 'member_no', 'type', 'joined_on', 'is_active'];

    protected $casts = ['joined_on' => 'date', 'is_active' => 'boolean'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function issues(): HasMany
    {
        return $this->hasMany(BookIssue::class);
    }

    public function openIssues(): HasMany
    {
        return $this->issues()->where('is_returned', false);
    }

    public function student(): HasOne
    {
        return $this->hasOne(\Modules\AcademicCore\Models\Student::class, 'user_id', 'user_id');
    }

    public function staff(): HasOne
    {
        return $this->hasOne(\Modules\HumanResource\Models\Staff::class, 'user_id', 'user_id');
    }

    public function scopeStudents($q)
    {
        return $q->where('type', 'student');
    }

    public function scopeStaff($q)
    {
        return $q->where('type', 'staff');
    }

    /** Human-readable label for dropdowns/tables. Never throws; falls back gracefully. */
    public function displayLabel(): string
    {
        $name = optional($this->user)->name ?? '—';

        if ($this->type === 'student') {
            $record = $this->student?->records?->first();

            if ($record) {
                $classSection = implode(' - ', array_filter([
                    optional($record->schoolClass)->name,
                    optional($record->section)->name,
                ]));

                $segments = array_filter([$classSection, $record->roll_no ? "Roll {$record->roll_no}" : null]);

                return $segments
                    ? "{$name} · ".implode(' · ', $segments)." · {$this->member_no}"
                    : "{$name} · {$this->member_no}";
            }

            return "{$name} · {$this->member_no}";
        }

        if ($this->type === 'staff') {
            if ($this->staff) {
                $segments = array_filter([
                    optional($this->staff->designation)->title,
                    optional($this->staff->department)->name,
                ]);

                return $segments
                    ? "{$name} · ".implode(' · ', $segments)." · {$this->member_no}"
                    : "{$name} · {$this->member_no}";
            }

            return "{$name} · {$this->member_no}";
        }

        return "{$name} · {$this->member_no}";
    }
}
