# Library — Business Logic

Documents the LIBRARY module of the reference system so it can be faithfully rebuilt.
Source: models under `app/` (`SmBook`, `SmBookCategory`, `SmBookIssue`, `SmLibraryMember`,
`LibrarySubject`), controllers under `app/Http/Controllers/Admin/Library/`, and the
`CREATE TABLE` blocks in the reference SQL dump.

> IMPORTANT FINDING: The reference system has **no fine / late-fee feature and no
> return-date column**. A book issue only tracks `given_date`, `due_date`, and a status
> flag (`I` issued / `R` returned). Fine handling is therefore an **addition** in the clean
> rebuild (section 6), not something copied from the reference.

---

## 1. Entities & Tables

### 1.1 Book Category — `sm_book_categories`
| Column | Type | Notes |
|---|---|---|
| id | int unsigned PK | |
| category_name | varchar(200) | |
| created_at / updated_at | timestamp | |
| school_id | int unsigned | multi-tenant scope |
| academic_id | int unsigned | academic-year scope |

Model `SmBookCategory` applies the **AcademicSchoolScope** global scope → every query is
implicitly filtered by `school_id` + `academic_id`. So categories are **academic-year scoped**.

### 1.2 Library Subject — `library_subjects`
Secondary classification of a book (a "subject" belonging to a category).
| Column | Type | Notes |
|---|---|---|
| id | int unsigned PK | |
| subject_name | varchar(255) | |
| sb_category_id | varchar(255) | FK → sm_book_categories.id |
| subject_code | varchar(255) | |
| subject_type | varchar(191) default `'T'` | |
| active_status | tinyint default 1 | |
| school_id / academic_id | int unsigned | |

Model applies **StatusAcademicSchoolScope** (active_status + school + academic). **Academic-year scoped.**

### 1.3 Book — `sm_books`
| Column | Type | Notes |
|---|---|---|
| id | int unsigned PK | |
| book_title | varchar(200) | |
| book_number | varchar(200) | internal accession / copy number |
| isbn_no | varchar(200) | |
| publisher_name | varchar(200) | |
| author_name | varchar(200) | |
| rack_number | varchar(50) | shelf location |
| **quantity** | int default 0 | **doubles as the available-copies counter** (see below) |
| book_price | int | |
| post_date | date | date added |
| details | varchar(500) | |
| active_status | tinyint default 1 | |
| book_subject_id | int unsigned | FK → library_subjects.id |
| book_category_id | int unsigned | FK → sm_book_categories.id |
| school_id / academic_id | int unsigned | |

Model `SmBook` applies **ActiveStatusSchoolScope** (active_status + school; not academic).
Books are effectively **school scoped** and `academic_id` is stored on write but the global
scope does not filter by it. Treat as **academic-year tagged, school scoped**.

> There is **no separate total-copies vs available-copies**. The single `quantity` column
> is decremented on issue and incremented on return, so at any moment `quantity` = copies
> currently on the shelf. The original total is not retained.

### 1.4 Library Member — `sm_library_members`
| Column | Type | Notes |
|---|---|---|
| id | int unsigned PK | |
| **member_ud_id** | varchar(191) | the library **card / member id** (human-facing, must be unique) |
| member_type | int unsigned | role id: **2 = Student, 3 = Parent, other = Staff** |
| student_staff_id | int unsigned | links to `users.id` (the user_id of the student/staff/parent) |
| active_status | tinyint default 1 | membership active flag |
| school_id / academic_id | int unsigned | |

Model `SmLibraryMember` applies **StatusAcademicSchoolScope**. **Academic-year scoped.**
Relationships resolve `student_staff_id` against `SmStudent.user_id`, `SmStaff.user_id`,
or `SmParent.user_id` depending on `member_type`.

### 1.5 Book Issue — `sm_book_issues`
| Column | Type | Notes |
|---|---|---|
| id | int unsigned PK | |
| book_id | int unsigned | FK → sm_books.id |
| member_id | int unsigned | matches `sm_library_members.student_staff_id` (the user_id), **not** the member row id |
| given_date | date | issue date (set to today) |
| due_date | date | expected return date (chosen by operator) |
| **issue_status** | varchar(191) | `'I'` = issued/out, `'R'` = returned |
| quantity | int | present in schema but unused by issue flow (always 1 conceptually) |
| note | varchar(500) | |
| active_status | tinyint default 1 | |
| school_id / academic_id | int unsigned | |

Model `SmBookIssue` applies **StatusAcademicSchoolScope**. **Academic-year scoped.**
Note `member()` relates `member_id` → `SmLibraryMember.student_staff_id`.
**There is no `return_date` and no `fine` column.**

### 1.6 Scoping summary
| Entity | Global scope | Academic-year scoped? |
|---|---|---|
| Book Category | AcademicSchoolScope | Yes |
| Library Subject | StatusAcademicSchoolScope | Yes |
| Book | ActiveStatusSchoolScope | Tagged with academic_id, filtered by school only |
| Library Member | StatusAcademicSchoolScope | Yes |
| Book Issue | StatusAcademicSchoolScope | Yes |

All entities carry `school_id` (multi-tenant SaaS).

---

## 2. Issue Flow (reference)

Controller: `SmBookController@saveIssueBookData`.

1. **Validate** input: `book_id` required, `due_date` required and `after:now`
   (API path additionally requires `user_id`).
2. **Duplicate check** — reject if this member already has this exact book out:
   `SmBookIssue where member_id = X and book_id = Y and issue_status = 'I'` exists
   → warn "You have already issued this book" and stop.
3. **Availability check** — load the book, read `book->quantity`. If `quantity == 0`
   → warn "This book not available now" and stop.
4. **Create the issue row**:
   - `book_id`, `member_id` = the member's user id,
   - `given_date` = today (`date('Y-m-d')`),
   - `due_date` = the submitted due date,
   - `issue_status = 'I'`,
   - `school_id`, `academic_id`, `created_by`.
5. **Decrement availability** — on successful save: `book->quantity -= 1; save()`.
6. Send an "Issue/Return_Book" notification to the student/parent.

**Loan period / due date**: NOT computed. The operator picks `due_date` manually; the only
rule is it must be a future date. There is **no fixed loan period** constant.

**Per-member issue limit**: NONE. The only restriction is you cannot issue the *same book*
twice to the same member while it is still out (step 2). A member may hold any number of
*different* books.

---

## 3. Return Flow (reference)

Controller: `SmBookController@returnBook($issue_book_id)`.

1. Find the issue row by id.
2. Set `issue_status = 'R'`, set `updated_by`, save.
3. On success: reload the book and **increment availability**: `book->quantity += 1; save()`.
4. Send an "Issue/Return_Book" notification.

**No return date is recorded. No fine is calculated or charged.** The `due_date` is stored
but never compared against an actual return date in the reference code. Overdue status can
only be inferred in views by comparing `due_date` to `now()` while `issue_status = 'I'`.

---

## 4. Member Management (reference)

Controller: `SmLibraryMemberController`.

- **Who can be a member**: a Student (`member_type = 2`), a Parent/Guardian
  (`member_type = 3`), or a Staff member (any other role id). Selection resolves to a
  `student_staff_id` = the chosen person's `users.id`.
- **Card / member id**: `member_ud_id`, entered by the operator, validated
  `required | max:120 | unique:sm_library_members,member_ud_id`.
- **One membership per person**: creating a member checks for an existing row with the same
  `student_staff_id`; if found (and inactive) it is re-activated rather than duplicated.
- **Cancel membership** (`cancelMembership`): blocked if the member still has any book out
  (`SmBookIssue where member_id = student_staff_id and issue_status = 'I'`) → "This member
  have to return book". Otherwise the member row is deleted.

---

## 5. Category & Subject management
Standard CRUD (`SmBookCategoryController`, `SmBookController@store/edit/update/delete` for
subjects). Delete is guarded by `tableList::getTableList()` which refuses deletion when the
category/subject/book is referenced elsewhere.

---

## 6. Clean Single-School Rebuild Recommendation

Target: one school, one database. **Drop `school_id`** everywhere. **Keep `academic_year_id`
scoping** where the reference scopes by academic year (categories, subjects, members, issues,
and tag books with it too). Split `quantity` into an explicit **total vs available** pair, and
**add the fine feature the reference lacks** (return date + per-day fine).

### 6.1 `book_categories`
```
id                PK
name              varchar
academic_year_id  FK  (nullable if categories are shared across years)
timestamps
```

### 6.2 `book_subjects` (optional secondary classification)
```
id                PK
name              varchar
code              varchar nullable
category_id       FK -> book_categories
academic_year_id  FK
is_active         bool default true
timestamps
```

### 6.3 `books`
```
id                PK
title             varchar
book_number       varchar        -- accession number
isbn              varchar nullable
author            varchar nullable
publisher         varchar nullable
rack_number       varchar nullable
category_id       FK -> book_categories
subject_id        FK -> book_subjects nullable
quantity          int   -- TOTAL copies owned (never decremented)
available         int   -- copies currently on shelf (0..quantity)
price             decimal(10,2) nullable
post_date         date
details           text nullable
academic_year_id  FK
is_active         bool default true
timestamps
```
Rule: `0 <= available <= quantity`. Issue → `available--`; return → `available++`.
(Fixes the reference conflation where the single `quantity` lost the true total.)

### 6.4 `library_members`
```
id                PK
member_card_id    varchar UNIQUE      -- was member_ud_id
member_type       enum(student,staff,parent)
user_id           FK -> users         -- was student_staff_id
academic_year_id  FK
is_active         bool default true
timestamps
UNIQUE(user_id, academic_year_id)     -- one active membership per person per year
```

### 6.5 `book_issues`
```
id                PK
book_id           FK -> books
member_id         FK -> library_members   -- proper FK to the member row id
issue_date        date
due_date          date
return_date       date nullable           -- NEW: recorded on return
status            enum(issued,returned) default issued
fine_amount       decimal(10,2) default 0 -- NEW: computed at return
fine_paid         bool default false       -- NEW
note              text nullable
academic_year_id  FK
timestamps
```

### 6.6 Rebuilt issue rules
- Reject if the same member already has the same book with `status = issued`.
- Reject if `books.available == 0`.
- `issue_date = today`. `due_date` = today + **loan-period-days** (make it a library setting,
  e.g. 14 days; still allow manual override, must be a future date).
- Optionally enforce a **max concurrent issues per member** setting (the reference has none).
- On save: `books.available--`.

### 6.7 Rebuilt return + fine algorithm
```
on return(issue):
    issue.return_date = today
    issue.status = 'returned'
    days_late = max(0, days_between(issue.due_date, today))
    issue.fine_amount = days_late * FINE_PER_DAY      -- FINE_PER_DAY = library setting
    book.available = min(book.quantity, book.available + 1)
    save
```
- `FINE_PER_DAY` (and the grace/loan period) live in a `library_settings` table/config.
- `fine_amount = 0` when returned on or before `due_date`.
- Track `fine_paid` so overdue collections can be reconciled; optionally post the fine to the
  student's fees/accounting ledger.

### 6.8 Migration notes from the reference
- Reference `member_id` on issues = the user's id, not the member row id. In the rebuild point
  `book_issues.member_id` at `library_members.id` and translate on import.
- Reference has no `return_date`; historical returned rows get `return_date = updated_at` and
  `fine_amount = 0` on import.
- Reference `quantity` = current available; on import set `available = quantity` and, if the
  true total is unknown, `quantity = available + count(open issues for that book)`.
```
```

---

## 7. Summary (issue / return / fine rules)

1. **Issue**: block if the member already holds that same book (`status = issued`) or if the
   book's available count is 0; then create an issue with `issue_date = today`, an operator-set
   future `due_date`, `status = issued`, and decrement the book's available count by 1.
2. **Due date / loan period**: the reference does NOT compute it (operator picks any future
   date, no fixed period); the rebuild derives `due_date = today + loan_period_days` from a
   library setting, still overridable.
3. **Per-member limit**: the reference has none (only the same-book-once rule); the rebuild may
   add an optional max-concurrent-issues setting.
4. **Return**: mark `status = returned` and increment the book's available count by 1; the
   reference records no return date and computes no fine.
5. **Fine (rebuild only)**: `fine = max(0, days_between(due_date, return_date)) * fine_per_day`,
   zero when returned on time, using a configurable per-day rate, with `fine_paid` tracked.
