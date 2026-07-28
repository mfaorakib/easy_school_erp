<?php

namespace Modules\AcademicCore\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Guardian / parent — persists across years, one guardian → many children. */
class Guardian extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'father_name', 'father_mobile', 'father_occupation', 'father_photo',
        'mother_name', 'mother_mobile', 'mother_occupation', 'mother_photo',
        'guardian_name', 'guardian_mobile', 'guardian_email', 'guardian_occupation',
        'guardian_relation', 'guardian_photo', 'guardian_address', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Active children (the reference system SmParent::childrens()). */
    public function children(): HasMany
    {
        return $this->hasMany(Student::class, 'guardian_id')->where('is_active', true);
    }
}
