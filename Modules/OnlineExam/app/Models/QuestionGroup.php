<?php

namespace Modules\OnlineExam\Models;

use App\Core\Concerns\BelongsToAcademicYear;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A category grouping question-bank items. Year-scoped. */
class QuestionGroup extends Model
{
    use BelongsToAcademicYear, SoftDeletes;

    protected $fillable = ['title', 'is_active', 'academic_year_id'];

    protected $casts = ['is_active' => 'boolean'];

    public function questions(): HasMany
    {
        return $this->hasMany(QuestionBank::class);
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }
}
