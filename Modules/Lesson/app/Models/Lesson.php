<?php

namespace Modules\Lesson\Models;

use App\Core\Concerns\BelongsToAcademicYear;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\AcademicCore\Models\SchoolClass;
use Modules\AcademicCore\Models\Section;
use Modules\AcademicCore\Models\Subject;
use Modules\HumanResource\Models\Staff;

class Lesson extends Model
{
    use BelongsToAcademicYear;

    protected $fillable = [
        'class_id', 'section_id', 'subject_id', 'teacher_id', 'title',
        'description', 'lesson_date', 'academic_year_id', 'created_by',
    ];

    protected $casts = ['lesson_date' => 'date'];

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'teacher_id');
    }

    public function topics(): HasMany
    {
        return $this->hasMany(LessonTopic::class)->orderBy('position')->orderBy('id');
    }

    /** Completion percentage from its topics (0 when it has none). */
    public function progress(): int
    {
        $total = $this->topics()->count();
        if ($total === 0) {
            return 0;
        }

        return (int) round($this->topics()->where('is_complete', true)->count() / $total * 100);
    }
}
