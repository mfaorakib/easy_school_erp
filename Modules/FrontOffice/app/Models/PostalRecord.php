<?php

namespace Modules\FrontOffice\Models;

use App\Core\Concerns\BelongsToAcademicYear;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A postal dispatch or receive record. Year-scoped. */
class PostalRecord extends Model
{
    use BelongsToAcademicYear, SoftDeletes;

    public const TYPE_DISPATCH = 'dispatch';
    public const TYPE_RECEIVE = 'receive';

    protected $fillable = [
        'type', 'title', 'party', 'reference_no', 'address',
        'postal_date', 'note', 'attachment_path', 'academic_year_id',
    ];

    protected $casts = ['postal_date' => 'date'];

    public function scopeType($q, string $type)
    {
        return $q->where('type', $type);
    }
}
