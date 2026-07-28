<?php

namespace Modules\Chat\Models;

use App\Core\Concerns\BelongsToAcademicYear;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\AcademicCore\Models\SchoolClass;
use Modules\AcademicCore\Models\Section;
use Modules\AcademicCore\Models\Subject;

/** A group conversation. Year-scoped. */
class ChatGroup extends Model
{
    use BelongsToAcademicYear, SoftDeletes;

    public const TYPE_OPEN = 'open';
    public const TYPE_ADMIN_ONLY = 'admin_only';

    protected $fillable = [
        'name', 'description', 'photo_path', 'type', 'created_by',
        'class_id', 'section_id', 'subject_id', 'academic_year_id',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function members(): HasMany
    {
        return $this->hasMany(ChatGroupMember::class, 'group_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatGroupMessage::class, 'group_id');
    }

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

    public function isAdminOnly(): bool
    {
        return $this->type === self::TYPE_ADMIN_ONLY;
    }
}
