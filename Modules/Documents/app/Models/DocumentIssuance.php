<?php

namespace Modules\Documents\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** The persisted document number for one (template, holder) pair — see the migration for why. */
class DocumentIssuance extends Model
{
    protected $fillable = [
        'certificate_template_id', 'holder_type', 'holder_id',
        'document_no', 'issued_at', 'issued_by',
    ];

    protected $casts = ['issued_at' => 'date'];

    public function template(): BelongsTo
    {
        return $this->belongsTo(CertificateTemplate::class, 'certificate_template_id');
    }
}
