<?php

namespace Modules\Admission\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\AcademicCore\Models\DocumentType;

/** A document uploaded with a still-pending public application (see AdmissionApplication::documents()). */
class AdmissionApplicationDocument extends Model
{
    use SoftDeletes;

    protected $fillable = ['admission_application_id', 'document_type_id', 'file_path'];

    public function application(): BelongsTo
    {
        return $this->belongsTo(AdmissionApplication::class, 'admission_application_id');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class, 'document_type_id');
    }
}
