<?php

namespace Modules\GuardianPortal\Services;

use App\Models\User;
use Illuminate\Support\Collection;
use Modules\AcademicCore\Models\Guardian;
use Modules\AcademicCore\Models\Student;
use Modules\Fees\Models\FeePayment;
use Modules\Fees\Services\FeeService;

/**
 * Every read here is scoped to "the student(s) this session may view" — the
 * school-wide admin dashboard/queries are never reused directly. This portal
 * serves two kinds of session: a guardian (their own children) and a student
 * viewing themselves (`selfStudent()`) — children() resolves either one, so
 * every controller built on top of it (dashboard, payments, attendance,
 * leave) works unchanged for both roles. assertOwnsChild() is the one gate
 * that must be called before showing/acting on any specific student, so
 * neither a guardian nor a student can reach another family's data by
 * guessing a student id in the URL.
 */
class GuardianPortalService
{
    public function __construct(private readonly FeeService $fees) {}

    public function guardianFor(User $user): ?Guardian
    {
        return Guardian::where('user_id', $user->id)->first();
    }

    /** The student record for a user logged in with the student role (viewing themselves). */
    public function selfStudent(User $user): ?Student
    {
        return Student::where('user_id', $user->id)->first();
    }

    /** @return Collection<int, Student> the student(s) this session may view. */
    public function children(User $user): Collection
    {
        if ($self = $this->selfStudent($user)) {
            return collect([$self]);
        }

        $guardian = $this->guardianFor($user);

        return $guardian ? $guardian->children()->where('is_active', true)->get() : collect();
    }

    /** Aborts 403 if $studentId isn't one of this guardian's own children. */
    public function assertOwnsChild(User $user, int $studentId): Student
    {
        $student = $this->children($user)->firstWhere('id', $studentId);
        abort_if($student === null, 403, "You don't have access to this student.");

        return $student;
    }

    /** Fee ledger (assignment + balance pairs) for one of this guardian's children. */
    public function feeLedger(Student $student): array
    {
        return $this->fees->studentLedger($student->id);
    }

    /** This guardian's payment history across all of their children, newest first. */
    public function paymentHistory(User $user): Collection
    {
        $studentIds = $this->children($user)->pluck('id');

        return FeePayment::whereHas('assignment', fn ($q) => $q->whereIn('student_id', $studentIds))
            ->with(['assignment.master.type', 'assignment.student'])
            ->latest('paid_on')
            ->latest('id')
            ->get();
    }
}
