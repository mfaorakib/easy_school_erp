<?php

namespace Modules\OnlineExam\Services;

use Illuminate\Support\Facades\DB;
use Modules\AcademicCore\Models\Student;
use Modules\OnlineExam\Models\OnlineExam;
use Modules\OnlineExam\Models\OnlineExamAnswer;
use Modules\OnlineExam\Models\OnlineExamAttempt;
use Modules\OnlineExam\Models\QuestionBank;

/**
 * Online-exam grading engine.
 *
 * Auto-grading rules (applied on submit when the exam has auto_mark on):
 *   - mcq:       full marks only when the chosen option set EXACTLY equals the
 *                correct option set (all correct chosen, no incorrect chosen).
 *   - truefalse: full marks when the chosen boolean equals the question's answer.
 *   - fill:      never auto-graded — left null (awaits manual marking) regardless
 *                of the auto_mark flag.
 *
 * An attempt is 'marked' only once every answer has a non-null obtain_marks;
 * otherwise it stays 'submitted' until a teacher marks the remaining answers.
 *
 * (Note: the reference system awarded MCQ marks on ANY overlap with the correct
 * options — a laxer rule. This rebuild tightens it to an exact-set match.)
 */
class OnlineExamService
{
    /** Attach question-bank items to an exam, numbering them in the given order. */
    public function assignQuestions(OnlineExam $exam, array $questionBankIds): void
    {
        $sync = [];
        $position = 1;
        foreach ($questionBankIds as $id) {
            $sync[(int) $id] = ['position' => $position++];
        }

        $exam->questions()->sync($sync);
    }

    /** Get (or start) a student's attempt for an exam. */
    public function startAttempt(OnlineExam $exam, Student $student): OnlineExamAttempt
    {
        return OnlineExamAttempt::firstOrCreate(
            ['online_exam_id' => $exam->id, 'student_id' => $student->id],
            ['status' => OnlineExamAttempt::STATUS_PENDING],
        );
    }

    /**
     * Store a student's responses and auto-grade the objective ones.
     *
     * @param  array<int, array{options?: array, bool?: mixed, text?: ?string}>  $responses
     *         keyed by question_bank_id
     */
    public function submitAttempt(OnlineExamAttempt $attempt, array $responses): OnlineExamAttempt
    {
        $exam = $attempt->exam;
        $autoMark = (bool) $exam->auto_mark;
        $questions = $exam->questions()->with('options')->get()->keyBy('id');

        DB::transaction(function () use ($attempt, $responses, $questions, $autoMark) {
            foreach ($questions as $qid => $question) {
                $response = $responses[$qid] ?? [];

                $answer = new OnlineExamAnswer([
                    'attempt_id'       => $attempt->id,
                    'question_bank_id' => $qid,
                    'selected_options' => $question->isMcq() ? array_map('intval', (array) ($response['options'] ?? [])) : null,
                    'bool_answer'      => $question->isTrueFalse() ? $this->toBool($response['bool'] ?? null) : null,
                    'text_answer'      => $question->isFill() ? ($response['text'] ?? null) : null,
                ]);

                if ($autoMark && $question->isAutoGradable()) {
                    $this->applyAutoGrade($answer, $question);
                }
                // Objective question with auto_mark off, or a fill question: leave null → awaits marking.

                $attempt->answers()->save($answer);
            }

            $attempt->update([
                'status'       => OnlineExamAttempt::STATUS_SUBMITTED,
                'submitted_at' => now(),
            ]);

            $this->recomputeTotal($attempt->fresh('answers'));
        });

        return $attempt->fresh();
    }

    /** Manually score a single (typically fill-in) answer, then re-total the attempt. */
    public function markAnswer(OnlineExamAnswer $answer, float $marks, ?int $markedBy = null): OnlineExamAnswer
    {
        $max = (float) $answer->question->marks;
        $marks = max(0.0, min($marks, $max));

        $answer->update([
            'obtain_marks' => $marks,
            'is_correct'   => $marks >= $max && $max > 0,
            'marked_by'    => $markedBy,
        ]);

        $this->recomputeTotal($answer->attempt->fresh('answers'));

        return $answer->fresh();
    }

    /** Grade an objective answer in place (does not persist). */
    protected function applyAutoGrade(OnlineExamAnswer $answer, QuestionBank $question): void
    {
        $correct = false;

        if ($question->isMcq()) {
            $chosen = array_map('intval', (array) $answer->selected_options);
            sort($chosen);
            $expected = $question->correctOptionIds();
            sort($expected);
            $correct = $chosen === $expected && $expected !== [];
        } elseif ($question->isTrueFalse()) {
            $correct = $answer->bool_answer !== null
                && (bool) $answer->bool_answer === (bool) $question->correct_bool;
        }

        $answer->is_correct = $correct;
        $answer->obtain_marks = $correct ? (float) $question->marks : 0.0;
    }

    /**
     * Sum the graded answers into the attempt total. If every answer is graded the
     * attempt becomes 'marked'; otherwise it stays 'submitted'.
     */
    public function recomputeTotal(OnlineExamAttempt $attempt): void
    {
        $graded = $attempt->answers->whereNotNull('obtain_marks');
        $total = (float) $graded->sum('obtain_marks');
        $allGraded = $attempt->answers->whereNull('obtain_marks')->isEmpty();

        $attempt->update([
            'total_marks' => $total,
            'status'      => $allGraded ? OnlineExamAttempt::STATUS_MARKED : OnlineExamAttempt::STATUS_SUBMITTED,
        ]);
    }

    private function toBool(mixed $value): ?bool
    {
        if ($value === null || $value === '') {
            return null;
        }

        return in_array($value, [true, 1, '1', 'T', 't', 'true', 'on'], true);
    }
}
