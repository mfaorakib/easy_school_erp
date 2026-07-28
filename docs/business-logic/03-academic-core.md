# the reference system - Academic Core Business-Logic Specification

> Reverse-engineered from the legacy Laravel app at c:\reference-source. This document
> describes BEHAVIOR to replicate, with citations to source files/tables.
> Descriptive only - no code in the legacy app was modified.

## 0. Cross-cutting concepts (understand first)

Almost every academic row is scoped by three columns:

- school_id - multi-tenant (one DB, many schools). SchoolScope /
  ActiveStatusSchoolScope global scopes filter by the logged-in user school_id.
  Source: app\Scopes\*, applied in each model boot().
- academic_id - the active academic year (sm_academic_years, helper
  getAcademicId()). GlobalAcademicScope + StatusAcademicSchoolScope filter most
  academic tables to the current year. Consequence: classes, sections, subjects
  and assign_subjects are effectively re-created per academic year.
- active_status - soft on/off flag (1 = active). scopeStatus() filters it.

Feature flags gate behavior: moduleStatusCheck('University'|'Lead'|'OnlineExam'|..)
and shiftEnable() (optional Shift dimension, table shifts, model App\Models\Shift).
When shift is disabled, shift_id is stored null/empty and dropped from every
uniqueness check. The University module swaps class/section for
faculty/department/semester and is OUT OF SCOPE here; replicate the K-12 path only.

Roles (users.role_id): 1 Admin, 2 Student, 3 Parent, 4 Teacher. A teacher is an
SmStaff with role_id = 4 OR previous_role_id = 4.

---

## 1. Class -> Section -> Subject hierarchy

### 1.1 Entities

| Concept | Model | Table | Key columns |
|---|---|---|---|
| Class | SmClass | sm_classes | id, class_name, pass_mark, academic_id, school_id, active_status, created_by |
| Section | SmSection | sm_sections | id, section_name, capacity, academic_id, school_id, active_status |
| Subject | SmSubject | sm_subjects | id, subject_name, subject_code, subject_type, pass_mark, academic_id, school_id, active_status |
| Class-Section pivot | SmClassSection | sm_class_sections | id, class_id, section_id, shift_id, academic_id, school_id |
| Subject assignment | SmAssignSubject | sm_assign_subjects | id, class_id, section_id, subject_id, teacher_id, shift_id, academic_id, school_id, active_status |
| Optional-subject setup | SmClassOptionalSubject | sm_class_optional_subject | id, class_id, gpa_above |
| Optional-subject per student | SmOptionalSubjectAssign | (per-student) | student_id, subject_id, session_id, academic_id |
| Classroom | SmClassRoom | sm_class_rooms | id, room_no, capacity, school_id, active_status |

subject_type is free-text/string (Theory/Practical etc.), cast in SmSubject.

### 1.2 Class links to Sections via the sm_class_sections pivot

- SmClass::classSections() = hasMany(SmClassSection, class_id).
- SmClass::classSectionAll() = belongsToMany(SmSection, sm_class_sections,
  class_id, section_id).
- Creation (SmClassController::store): create the SmClass (class_name, pass_mark,
  academic_id, school_id, created_by), then for each selected section insert one
  SmClassSection row. If shiftEnable(), the loop nests foreach shift -> foreach
  section, i.e. one pivot row per (class, shift, section).
- Update (SmClassController::update): DELETES ALL sm_class_sections rows for that
  class_id first, then re-inserts from the submitted list (destructive re-sync,
  not a diff). class_name and pass_mark are updated on the class row.
- Delete (SmClassController::delete): blocked if the class id is referenced
  elsewhere (tableList::getTableList('class_id',id)); allowed only when no
  dependents beyond chat groups / class sections. Deletes pivot rows and related
  chat_groups, then the class.

A Section is a global list per school/year, independent of class; it becomes a
class section only via a pivot row. The same section (e.g. "A") is reused across
many classes.

### 1.3 Subjects assigned to Class + Section (sm_assign_subjects)

Managed by SmAssignSubjectController::assignSubjectStore.

- Input: class, optional section_id, index-aligned arrays subjects[] and
  teachers[], optional shift, and an update flag (0 = insert, 1 = replace).
- No section chosen (section_id empty): subjects are assigned to EVERY section of
  the class - it loops all SmClassSection rows for that class and inserts one
  SmAssignSubject per section. (In update==1 no-section mode it appends instead of
  deleting first.)
- Section chosen: on update==1 it DELETES all existing sm_assign_subjects for that
  class+section first, then re-inserts the submitted (subject, teacher) pairs
  (full replace of that section plan).
- Each row: class_id, section_id, subject_id, teacher_id, shift_id (null if shift
  disabled), academic_id, school_id.
- Side effect: fires CreateClassGroupChat to create a class group chat.
- A teacher classes derive from sm_assign_subjects (SmStaff::classes() joins
  sm_classes on teacher_id).
- Uniqueness is NOT DB-enforced; the controller enforces "replace on update"
  manually. Duplicate (class, section, subject) rows are possible via the
  no-section append path.

### 1.4 Optional subjects

- Per-class config SmClassOptionalSubject (class_id, gpa_above): GPA credit
  threshold for optional subjects in a class.
- Per-student choice SmOptionalSubjectAssign (student_id, subject_id, session_id):
  the optional subject a student actually takes.
- Result impact (SmAssignSubject::get_student_result): when a student subject
  equals their optional subject, only GPA above gpa_above is credited
  (optional_grade = grade.gpa - setup.gpa_above), and that subject is excluded
  from the divisor when averaging GPA (number_of_subject -= 1). Any subject with
  gpa < 1 fails the whole result -> GPA 0.00.
  SmStudent::getOptionalSubjectSetupAttribute fetches setup by student class_id.

---

## 2. Student admission

### 2.1 Core tables

- sm_students (SmStudent, global SchoolScope). Key columns: user_id, parent_id,
  role_id(=2), admission_no(int), roll_no(int), first_name, last_name, full_name,
  gender_id, date_of_birth, caste, email, mobile, admission_date, student_photo,
  bloodgroup_id, religion_id, height, weight, current_address, permanent_address,
  route_list_id, vechile_id, driver_id, dormitory_id, room_id, national_id_no,
  local_id_no, bank_account_no, bank_name, ifsc_code, previous_school_details,
  aditional_notes, document_title_1..4, document_file_1..4, school_id, academic_id,
  student_category_id, student_group_id, custom_field/custom_field_form_name,
  active_status. Lead module adds lead_id, lead_city_id, source_id. Schema
  misspellings preserved: vechile_id, aditional_notes.
- users - one row per student (role 2) and per parent (role 3).
- sm_parents (SmParent) - guardian info (see 2.5).
- student_records (StudentRecord) - the enrollment join row (see 2.4); this, not
  sm_students, is the source of truth for class/section/session enrollment.

Controller: SmStudentAdmissionController (store(); helpers insertStudentRecord(),
updateStudentInfo()).

### 2.2 Admission-number generation

- NOT auto-persisted by the backend. The add form pre-fills the field with
  max(admission_no for this school) + 1 (or 1 if none). Source: controller index()
  sets max_admission_id = SmStudent::...->max('admission_no'); blade
  student_admission.blade.php:311 renders
  value = max_admission_id != '' ? max_admission_id + 1 : 1.
- Admin may override. The submitted admission_number is stored verbatim into
  sm_students.admission_no. roll_no similarly pre-filled with max+1, editable.
- Uniqueness is enforced only per school_id in the bulk-import path (exists()
  check); the single-admission path does NOT hard-block duplicates.
- users.username defaults to phone, else email, else the admission number. Default
  password for created student/parent users = Hash::make(123456).

### 2.3 Admission flow (store())

1. If a student user already exists (matched by phone/email, role 2) and there is
   NO StudentRecord for the submitted class+section+session, treat as
   re-enrollment: optionally update info, then insertStudentRecord() for the new
   class/section/session and return. If a record already exists -> "Already
   Enroll".
2. Otherwise create new: users row (role 2) -> resolve/create parent (2.5) ->
   sm_students row -> QR code student-{id} -> clone leave definitions
   (SmLeaveDefine) -> notifications (Assign_Vehicle/Dormitory, Student_Admission to
   student/parent/class-teacher/super-admin) -> insertStudentRecord() with
   is_default = 1 -> email/SMS credentials. All within a DB transaction.
3. created_at for user/parent/student is forced to
   {academic_year->year}-01-01 12:00:00 (dated to the session year, not now).

### 2.4 Tying a student to class/section/session - insertStudentRecord()

Central enrollment routine (reused by promotion and record edit). A
student_records row = one enrollment of one student in one
class/section/shift/session/academic-year.

student_records key columns: student_id, class_id, section_id, shift_id,
session_id, academic_id, school_id, roll_no, is_default(int), is_promote(int),
is_graduate, lead_id.

Rules:
- Single default per year: if the new record is default (or is_default not supplied
  -> treated as default), all other records for that student in the current
  academic_id are set is_default = 0 first.
- Single-roll mode: if generalSetting()->multiple_roll == 0, the roll_number is
  written to sm_students.roll_no AND propagated to all of that student records in
  the current year (one roll number per year). If multiple_roll == 1, roll number
  lives per-record.
- is_promote = request.is_promote ?? 0 (0 = current/live, 1 = historical).
- shift_id = request shift if shiftEnable() else empty.
- After save: assigns direct fees if directFees(); adds the student user to
  matching class/section/shift chat groups.
- If record_id passed -> updates that existing record (edit) and first removes the
  student from the old group chat.

Live class/section of a student = StudentRecord where is_promote=0 and
academic_id=current (relations studentRecord(), defaultClass() is_default=1).

### 2.5 Category, group, guardian/parent linkage

- Category: sm_students.student_category_id -> SmStudentCategory
  (sm_student_categories).
- Group: sm_students.student_group_id -> SmStudentGroup (sm_student_groups, e.g.
  Science/Commerce). Both are lookup lists loaded into the admission form
  (loadData()), scoped by school. Session/year = sm_students.academic_id, also
  copied to session_id/academic_id on StudentRecord.
- Parent/guardian (SmParent, sm_parents): user_id, fathers_name, fathers_mobile,
  fathers_occupation, fathers_photo, mothers_*, guardians_name, guardians_mobile,
  guardians_email, guardians_occupation, guardians_relation, relation,
  guardians_photo, guardians_address, is_guardian, school_id, academic_id.
  Linkage in store():
  1. If parent_id supplied -> reuse existing SmParent.
  2. Else if any guardian phone/email present -> find/create a parent users row
     (role 3) and an SmParent row; link sm_students.parent_id.
  3. Staff-as-parent: if the guardian matches an existing staff/parent
     (StaffAsParentController), reuse that staff user_id/parent and set
     sm_staffs.parent_id.
  Parent has many children: SmParent::childrens() = hasMany(SmStudent, parent_id)
  where active_status=1; SmStudent::parents() = belongsTo(SmParent).
- Documents: inline document_title_1..4 / document_file_1..4 on sm_students, plus
  a separate SmStudentDocument (student_staff_id, academic_id).

---

## 3. Class-teacher assignment

Models: SmAssignClassTeacher (sm_assign_class_teachers: class_id, section_id,
shift_id, academic_id, school_id, active_status) is the slot; SmClassTeacher
(sm_class_teachers: assign_class_teacher_id, teacher_id, school_id, academic_id)
holds the teacher(s) for that slot. Controller: SmAssignClassTeacherController.

- Store: reject if an active SmAssignClassTeacher already exists for (class,
  section, academic_id, school_id [, shift]) -> "Class Teacher already assigned."
  Else create one SmAssignClassTeacher + one SmClassTeacher for the chosen teacher.
  Fires ClassTeacherGetAllStudent; notifies the teacher plus all students/parents
  in that class/section.
- Update: reject if another slot (different id) already has the same class+section
  for the year. Then DELETE existing SmClassTeacher rows for that assign id and
  re-create - supporting multiple teachers per slot. Updates class/section/shift
  on the slot.
- Delete: blocked if referenced; removes child SmClassTeacher rows then the slot.
- One slot per class+section+year (+shift); one-to-many teachers under it. Used at
  admission to notify the class teacher of a new student.

---

## 4. Student promotion

Controller: SmStudentPromoteController (index, studentCurrentSearch, promote,
studentSearchWithExam, rollCheck). History table: SmStudentPromotion
(sm_student_promotions).

### 4.1 Two modes (by generalSetting()->promotionSetting)

- promotionSetting == 0 -> manual promotion (student_promote_new): search by
  current session/class/section[/shift] and target promote_session.
- else -> exam/merit-based (student_promote_with_exam): students ordered by
  SmTemporaryMeritlist (gpa_point desc if
  CustomResultSetting.merit_list_setting == 'total_grade', else total_marks desc),
  built from an exam; the merit list must be generated first.

Search (studentCurrentSearch) collects students whose current live record
(is_promote=0) matches current session/class/section[/shift], and offers the "next
class" = first class in the promote-session list EXCLUDING the current class
(classes->except(class_id)->first()).

### 4.2 The promotion algorithm (promote())

Input: pre_class, pre_section, pre_shift, current_session (FROM), promote_session
(TO year), and per-student promote[student_id] = {student, class, section, shift,
roll_number, result}. Optional global is_graduate flag.

For each submitted student having student + target class + section:

1. Duplicate check: look for an existing student_records (is_promote=0) in the
   target class/section[/shift]/promote_session. If found -> skip (already
   promoted).
2. Roll number: if a roll_number was supplied and the target slot already has a
   record -> validation error "Roll no already exist". If none supplied ->
   auto-assign max(roll_no in target class/section[/shift]/promote_session) + 1.
3. Fetch the previous live record pre_record (student in
   pre_class/pre_section[/pre_shift]/current_session, is_promote=0).
4. If no existing target record: create an SmStudentPromotion history row:
   student_id, previous_class_id=pre_class, current_class_id=target,
   previous_session_id=current_session, current_session_id=promote_session,
   previous_section_id/current_section_id, previous_shift_id/current_shift_id,
   admission_number (student snapshot), student_info & merit_student_info (full
   student JSON snapshot), previous_roll_number=pre_record.roll_no,
   current_roll_number=roll_number, academic_id=promote_session, result_status =
   submitted result or 'F' (fail default).
5. Notify target class-teacher and student/parent ("Student_Promote").
6. Create the new enrollment via insertStudentRecord() with student_id,
   roll_number, class=target, section=target, shift=target,
   session=promote_session (new student_records row, default is_promote=0). This
   also re-assigns fees and chat groups.
7. Remove the student user from the OLD class/section/session chat groups.
8. Mark the previous record promoted: pre_record.is_promote = 1 (becomes
   historical; the new record is now live). Send promote SMS/email.

Graduation branch: if a student is submitted with student set but section null (or
global is_graduate=1): find the live record, set is_graduate=1 and is_promote=1,
and insert a Graduate row (student_id, record_id, class_id, section_id,
session_id[, shift_id], school_id, created_by). No new enrollment.

Net effect: promotion never edits the old record class - it LAYERS a new
student_records row for the next year and flips the old one is_promote to 1, while
writing an auditable sm_student_promotions snapshot. Class/session history is
reconstructable (SmStudent::getClassesAttribute / getSessionsAttribute read
min/max over the promotion rows).

---

## 5. Staff, designation, department

- SmStaff (sm_staffs, global ActiveStatusSchoolScope, guarded=[id]). Notable
  columns: user_id, role_id, previous_role_id, department_id, designation_id,
  gender_id, parent_id, first_name, last_name, full_name, school_id, active_status
  (plus HR/payroll/contact fields).
- Designation: sm_staffs.designation_id -> SmDesignation (sm_designations).
- Department: sm_staffs.department_id -> SmHumanDepartment (sm_human_departments).
  Both lookups carry ActiveStatusSchoolScope (school + active_status). Both
  relations use withDefault().
- Teacher identification: whereTeacher() / scopeWhereRole() = role_id = 4 OR
  previous_role_id = 4, plus active_status=1 and school_id. previous_role_id lets a
  promoted staff member still be selectable as teacher.
- Role -> Modules\RolePermission\Entities\InfixRole (SmStaff::roles()); gender ->
  SmBaseSetup.
- A staff can double as a parent (sm_staffs.parent_id -> SmParent), set during
  admission staff-as-parent path (2.5).
- Teacher taught classes derive from sm_assign_subjects (SmStaff::classes() join,
  distinct class).

---

## Source file index

- Hierarchy: SmClass.php, SmSection.php, SmSubject.php, SmClassSection.php,
  SmAssignSubject.php, SmClassOptionalSubject.php, SmOptionalSubjectAssign.php,
  SmClassRoom.php; Admin/Academics/SmClassController.php, SmSubjectController.php,
  SmAssignSubjectController.php.
- Students/parents: SmStudent.php, SmParent.php, SmStudentCategory.php,
  SmStudentGroup.php, SmStudentDocument.php, Models/StudentRecord.php;
  Admin/StudentInfo/SmStudentAdmissionController.php.
- Class teacher: SmAssignClassTeacher.php, SmClassTeacher.php;
  Admin/Academics/SmAssignClassTeacherController.php.
- Promotion: SmStudentPromotion.php; SmStudentPromoteController.php.
- Staff: SmStaff.php, SmDesignation.php, SmHumanDepartment.php.
- Cross-cutting: app\Scopes\*, app\Helpers\Helper.php (getAcademicId, shiftEnable,
  generalSetting, directFees).
