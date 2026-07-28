<?php

namespace Modules\Dormitory\Models;

use App\Core\Support\Context;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DormitoryRoom extends Model
{
    use SoftDeletes;

    protected $fillable = ['dormitory_id', 'room_type_id', 'room_no', 'capacity', 'cost', 'is_active'];

    protected $casts = ['cost' => 'decimal:2', 'is_active' => 'boolean'];

    public function dormitory(): BelongsTo
    {
        return $this->belongsTo(Dormitory::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(RoomType::class, 'room_type_id');
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(StudentDormitory::class);
    }

    /** Beds taken in the active academic year. */
    public function getOccupancyAttribute(): int
    {
        return $this->allocations()
            ->where('academic_year_id', Context::academicYearId())
            ->count();
    }

    public function getFreeBedsAttribute(): int
    {
        return max(0, (int) $this->capacity - $this->occupancy);
    }
}
