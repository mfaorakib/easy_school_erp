# Online Exam & Question Bank — Business-Logic Spec

> Reverse-engineered from the reference system, then re-designed cleaner for EasySchool ERP.
> Everything is **academic-year scoped** (`academic_year_id`); the reference's `school_id` / SaaS
> columns are dropped.

The domain lets teachers build a reusable **question bank**, assemble **online exams** from it,
have students **sit** those exams online, **auto-grade** the objective questions immediately, and
**manually mark** the subjective (fill-in-the-blank) answers afterward.

---

## 1. Entities & tables

| # | Table | Model | Grain / purpose |
|---|---|---|---|
| 1 | `question_groups` | `QuestionGroup` | A category to organise bank questions ("General Knowledge"). Year-scoped. |
| 2 | `question_banks` | `QuestionBank` | A reusable question. `type` ∈ `mcq` / `truefalse` / `fill`, `difficulty` ∈ `easy`/`medium`/`hard`, `question`, `marks`, `correct_bool` (truefalse), `answer_text` (fill reference). Optional `class_id`/`section_id`. Year-scoped. |
| 3 | `question_options` | `QuestionOption` | An MCQ option; `is_correct` flags the right one(s). Child of a bank question. |
| 4 | `online_exams` | `OnlineExam` | An exam for a class/section/(subject). `exam_date`, `start_time`/`end_time`, `duration_minutes`, `instruction`, `auto_mark`, `is_published`. Year-scoped. |
| 5 | `online_exam_questions` | *(pivot)* | Which bank questions are attached to an exam, with a `position`. |
| 6 | `online_exam_attempts` | `OnlineExamAttempt` | One student's sitting. `status` ∈ `pending`/`submitted`/`marked`, `total_marks`, `submitted_at`. One row per (exam, student). Year-scoped. |
| 7 | `online_exam_answers` | `OnlineExamAnswer` | A student's answer to one question. `selected_options` (JSON, mcq), `bool_answer` (truefalse), `text_answer` (fill), `obtain_marks`, `is_correct`, `marked_by`. |

### Layer model
```
QuestionGroup
   └── QuestionBank (mcq | truefalse | fill)
          └── QuestionOption            (mcq only; is_correct)
OnlineExam
   ├── online_exam_questions ── QuestionBank   (attach from bank, ordered)
   └── OnlineExamAttempt (per student)
          └── OnlineExamAnswer ── QuestionBank  (per answered question)
```

---

## 2. Question types & grading

| Type | Student input | Stored in answer | Graded by |
|---|---|---|---|
| `mcq` | one/more options | `selected_options` (option-id array) | **auto**: exact set match vs `is_correct` options |
| `truefalse` | true / false | `bool_answer` | **auto**: equals `correct_bool` |
| `fill` | free text | `text_answer` | **manual** only (teacher enters marks) |

**Auto-grading** (only when the exam's `auto_mark` is on) runs on submit for objective questions:
- **MCQ** — full marks only when the chosen option set **exactly equals** the correct-option set
  (all correct chosen, nothing incorrect chosen); otherwise 0.
- **True/False** — full marks when `bool_answer === correct_bool`; otherwise 0.
- **Fill** — never auto-graded regardless of `auto_mark`; `obtain_marks` stays `null` (awaits marking).

**Attempt status transitions**
- `pending` → created when the student opens the exam (`startAttempt`).
- `submitted` → after `submitAttempt`; objective parts scored, but at least one answer is still
  ungraded (a fill answer, or any objective answer when `auto_mark` is off).
- `marked` → once **every** answer has a non-null `obtain_marks`. `total_marks` = Σ obtained.

Marking a pending answer (`markAnswer`) clamps to `0..question.marks`, re-sums the attempt, and
promotes it to `marked` when nothing remains ungraded.

---

## 3. Divergence from the reference (deliberate improvements)

| Reference behaviour | EasySchool rebuild | Why |
|---|---|---|
| MCQ marked correct on **any** overlap with correct options (`array_intersect != []`) | **Exact-set match** required | The lenient rule scored a wrong-plus-right selection as full marks. |
| Answer state split across `sm_student_take_online_exams`, `..._questions` **and** a later `online_exam_student_answer_markings` table (overlapping/duplicated) | Single `online_exam_answers` row per (attempt, question) holding response **and** grade | One source of truth; no reconciling two marking tables. |
| Many boolean flags on the exam (`is_taken`, `is_waiting`, `is_running`, `is_closed`) | Collapsed to attempt `status` + exam `is_published` | The flags were redundant/de-normalised state. |
| Separate `sm_question_levels` CRUD | Folded into a `difficulty` enum on the question | A whole screen for a 3-value lookup wasn't worth it. |
| `mark`-as-integer columns | `decimal` marks | Supports half-marks in manual grading. |

Everything the reference *does* (three question types, reusable bank scoped to class, assemble
exam from bank, student sitting, auto-mark toggle, manual marking of subjective answers, per-student
result) is preserved.

---

## 4. Service surface (`OnlineExamService`)

- `assignQuestions(OnlineExam, int[]): void` — sync the exam↔bank pivot, numbering `position`.
- `startAttempt(OnlineExam, Student): OnlineExamAttempt` — get-or-create (pending).
- `submitAttempt(OnlineExamAttempt, array $responses): OnlineExamAttempt` — persist answers,
  auto-grade objective ones, total, set status. `$responses` keyed by question id →
  `['options'=>[], 'bool'=>, 'text'=>]`.
- `markAnswer(OnlineExamAnswer, float, ?int $by): OnlineExamAnswer` — manual score + re-total.
- `recomputeTotal(OnlineExamAttempt): void` — Σ graded, set `marked`/`submitted`.
