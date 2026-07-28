<?php

namespace Modules\Admission\Services;

use App\Core\Support\Context;
use App\Models\User;
use Illuminate\Support\Str;
use Modules\AcademicCore\Models\StudentDocument;
use Modules\AcademicCore\Services\StudentAdmissionService;
use Modules\Admission\Models\AdmissionApplication;

/**
 * The public-application → staff-review workflow. Confirming an application
 * hands off to AcademicCore's StudentAdmissionService — the same routine the
 * admin "walk-in" admission form uses — so a confirmed application produces a
 * real, fully-enrolled Student (with photo + formatted unique_id), not a
 * parallel/duplicate creation path.
 *
 * `reviewer` is the acting authenticated User (not an HR Staff row) — some
 * confirming admins have no Staff profile, so this stays User-based to avoid
 * excluding them.
 */
class AdmissionService
{
    public function __construct(private readonly StudentAdmissionService $admission) {}

    /** Submit a new public application. Always starts pending. */
    public function apply(array $data): AdmissionApplication
    {
        return AdmissionApplication::create($data + [
            'reference_no'     => $this->uniqueReferenceNo(),
            'status'           => AdmissionApplication::STATUS_PENDING,
            'academic_year_id' => Context::academicYearId(),
        ]);
    }

    /** Look up a public application by its reference number. */
    public function findByReference(string $referenceNo): ?AdmissionApplication
    {
        return AdmissionApplication::where('reference_no', $referenceNo)->first();
    }

    /**
     * Confirm a pending application: create the real Student (photo + a
     * freshly generated unique_id) and stamp the application as confirmed.
     *
     * @param  array  $placement  class_id, section_id (staff picks/confirms the section) + optional photo override
     */
    public function confirm(AdmissionApplication $application, array $placement, User $reviewer): AdmissionApplication
    {
        abort_if($application->status !== AdmissionApplication::STATUS_PENDING, 422, 'This application has already been reviewed.');

        $student = $this->admission->admit([
            'first_name'          => $application->first_name,
            'last_name'           => $application->last_name,
            'gender_id'           => $application->gender_id,
            'date_of_birth'       => $application->date_of_birth?->toDateString(),
            'guardian_name'       => $application->guardian_name,
            'guardian_mobile'     => $application->guardian_mobile,
            'guardian_email'      => $application->guardian_email,
            'guardian_relation'   => $application->guardian_relation,
            'current_address'     => $application->present_address,
            'class_id'            => $placement['class_id'] ?? $application->desired_class_id,
            'section_id'          => $placement['section_id'],
            'photo'               => $placement['photo'] ?? $application->photo_path,
        ]);

        $application->update([
            'status'      => AdmissionApplication::STATUS_CONFIRMED,
            'student_id'  => $student->id,
            'unique_id'   => $student->unique_id,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
        ]);

        // Only NOW do the pending application's uploaded documents become
        // part of the real student's record — a rejected/abandoned
        // application's files never touch student_documents.
        foreach ($application->documents as $document) {
            StudentDocument::create([
                'student_id'       => $student->id,
                'document_type_id' => $document->document_type_id,
                'title'            => optional($document->type)->name ?? 'Document',
                'file_path'        => $document->file_path,
            ]);
        }

        return $application;
    }

    public function reject(AdmissionApplication $application, string $reason, User $reviewer): AdmissionApplication
    {
        abort_if($application->status !== AdmissionApplication::STATUS_PENDING, 422, 'This application has already been reviewed.');

        $application->update([
            'status'            => AdmissionApplication::STATUS_REJECTED,
            'rejection_reason'  => $reason,
            'reviewed_by'       => $reviewer->id,
            'reviewed_at'       => now(),
        ]);

        return $application;
    }

    private function uniqueReferenceNo(): string
    {
        do {
            $ref = 'APP-'.strtoupper(Str::random(8));
        } while (AdmissionApplication::where('reference_no', $ref)->exists());

        return $ref;
    }
}
