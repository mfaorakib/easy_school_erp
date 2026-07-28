# 04 — Foundation Schema (the reference system → EasySchool ERP)

Source dump: `andiedu (1).sql` (the reference system 8.2.8 / software_version 9.0.1).
Charset: `utf8mb4_unicode_ci`, engine `InnoDB` for all tables.
PK = `id` (int/bigint UNSIGNED, AUTO_INCREMENT via ALTER TABLE) unless noted.
Scoping columns legend: **S** = `school_id` (multi-tenant), **A** = `academic_id`, **Se** = `session_id`/session.

> NOTE for EasySchool ERP (single-school): drop `school_id`/`is_saas`/`is_custom_saas`/tenant columns.
> Keep `academic_id` + `session_id` scoping — that is core business logic, not tenancy.

NOTE ON MISSING NAMES: There is no `staff`, `students`, `parents`, `sm_roles`, or dedicated `permissions-assign` variant.
- staff  → `sm_staffs`
- students → `sm_students`
- parents → `sm_parents`
- roles → `roles` (there is NO `sm_roles`)
- permission tables → `permissions`, `sm_module_permission_assigns`

---

## IDENTITY & AUTH

### users
Central auth/identity table (one row per login: admin, staff, student, parent).
| column | type | null | default |
|---|---|---|---|
| id | int UNSIGNED | NO | PK auto |
| full_name | varchar(192) | YES | NULL |
| username | varchar(192) | YES | NULL |
| phone_number | varchar(191) | YES | NULL |
| email | varchar(192) | YES | NULL |
| password | varchar(100) | YES | NULL |
| usertype | varchar(210) | YES | NULL |
| active_status | tinyint | NO | 1 |
| random_code | text | YES | NULL |
| notificationToken | text | YES | NULL |
| remember_token | varchar(100) | YES | NULL |
| created_at | timestamp | YES | NULL |
| updated_at | timestamp | YES | NULL |
| language | varchar(191) | YES | 'en' |
| style_id | int | YES | 1 |
| rtl_ltl | int | YES | 2 |
| selected_session | int | YES | 1 |
| created_by | int | YES | 1 |
| updated_by | int | YES | 1 |
| access_status | int | YES | 1 |
| school_id | int UNSIGNED | YES | 1 |
| role_id | int UNSIGNED | YES | NULL |
| is_administrator | enum('yes','no') | NO | 'no' |
| is_registered | tinyint | NO | 0 |
| device_token | text | YES | NULL |
| stripe_id | varchar(191) | YES | NULL |
| card_brand | varchar(191) | YES | NULL |
| card_last_four | varchar(4) | YES | NULL |
| verified | varchar(191) | YES | NULL |
| trial_ends_at | timestamp | YES | NULL |
| wallet_balance | double | NO | 0 |

FKs: `role_id`→roles.id. Scoping: **S**, plus `selected_session`.

### roles
| column | type | null | default |
|---|---|---|---|
| id | int UNSIGNED | NO | PK auto |
| name | varchar(100) | YES | NULL |
| type | varchar(191) | NO | 'System' |
| active_status | tinyint | NO | 1 |
| created_by | varchar(191) | YES | '1' |
| updated_by | varchar(191) | YES | '1' |
| created_at | timestamp | YES | NULL |
| updated_at | timestamp | YES | NULL |
| school_id | int UNSIGNED | YES | 1 |

Seed roles: 1 Super admin, 2 Student, 3 Parents, 4 Teacher, 5 Admin, 6 Accountant, 7 Receptionist, 8 Librarian, 9 Driver. Scoping: **S**.

### permissions
Menu/permission registry (menu, submenu, action rows).
| column | type | null | default |
|---|---|---|---|
| id | bigint UNSIGNED | NO | PK auto |
| module | varchar(191) | YES | NULL |
| sidebar_menu | varchar(191) | YES | NULL |
| old_id | int | YES | NULL |
| section_id | int | YES | 1 |
| parent_id | int | YES | 0 |
| name | varchar(191) | YES | NULL |
| route | varchar(191) | YES | NULL |
| parent_route | varchar(191) | YES | NULL |
| type | int | YES | NULL (1=menu,2=submenu,3=action) |
| lang_name | varchar(191) | YES | NULL |
| icon | text | YES | NULL |
| svg | text | YES | NULL |
| status | tinyint | NO | 1 |
| menu_status | tinyint | NO | 1 |
| position | int | NO | 1 |
| is_saas | tinyint | NO | 0 |
| relate_to_child | tinyint | YES | 0 |
| is_menu | tinyint | YES | NULL |
| is_admin | tinyint | YES | 0 |
| is_teacher | tinyint | YES | 0 |
| is_student | tinyint | YES | 0 |
| is_parent | tinyint | YES | 0 |
| is_alumni | tinyint | YES | 0 |
| created_by | int UNSIGNED | YES | 1 |
| updated_by | int UNSIGNED | YES | 1 |
| permission_section | tinyint | YES | NULL |
| alternate_module | varchar(191) | YES | NULL |
| user_id | int | YES | NULL |
| role_id | int | YES | NULL |
| school_id | int UNSIGNED | YES | NULL |
| created_at | timestamp | YES | NULL |
| updated_at | timestamp | YES | NULL |
| custom_menu_id | int | YES | NULL |

FKs: `role_id`→roles.id, `user_id`→users.id, `parent_id`→permissions.id (self). Scoping: **S**.

---

## PEOPLE

### sm_staffs
| column | type | null | default |
|---|---|---|---|
| id | int UNSIGNED | NO | PK auto |
| staff_no | int | YES | NULL |
| first_name | varchar(100) | YES | NULL |
| last_name | varchar(100) | YES | NULL |
| full_name | varchar(200) | YES | NULL |
| fathers_name | varchar(100) | YES | NULL |
| mothers_name | varchar(100) | YES | NULL |
| date_of_birth | date | YES | NULL |
| date_of_joining | date | YES | NULL |
| email | varchar(50) | YES | NULL |
| mobile | varchar(50) | YES | NULL |
| emergency_mobile | varchar(50) | YES | NULL |
| marital_status | varchar(30) | YES | NULL |
| staff_photo | varchar(191) | YES | NULL |
| current_address | varchar(500) | YES | NULL |
| permanent_address | varchar(500) | YES | NULL |
| qualification | varchar(200) | YES | NULL |
| experience | varchar(200) | YES | NULL |
| epf_no | varchar(20) | YES | NULL |
| basic_salary | varchar(200) | YES | NULL |
| contract_type | varchar(200) | YES | NULL |
| location | varchar(50) | YES | NULL |
| casual_leave / medical_leave / metarnity_leave | varchar(15) | YES | NULL |
| bank_account_name/no, bank_name, bank_brach | varchar | YES | NULL |
| facebook_url/twiteer_url/linkedin_url/instragram_url | varchar(100) | YES | NULL |
| joining_letter / resume / other_document | varchar(500) | YES | NULL |
| notes | varchar(500) | YES | NULL |
| active_status | tinyint | NO | 1 |
| show_public | tinyint | NO | 0 |
| driving_license | varchar(255) | YES | NULL |
| driving_license_ex_date | date | YES | NULL |
| custom_field | text | YES | NULL |
| custom_field_form_name | varchar(191) | YES | NULL |
| created_at / updated_at | timestamp | YES | NULL |
| designation_id | int UNSIGNED | YES | 1 |
| department_id | int UNSIGNED | YES | 1 |
| user_id | int UNSIGNED | YES | 1 |
| parent_id | int | YES | NULL |
| role_id | int UNSIGNED | YES | 1 |
| previous_role_id | int | YES | NULL |
| gender_id | int UNSIGNED | YES | 1 |
| school_id | int UNSIGNED | YES | 1 |

FKs: `user_id`→users.id, `role_id`→roles.id, `designation_id`→sm_designations.id, `department_id`→sm_human_departments.id, `gender_id`→sm_base_setups.id. Scoping: **S** (no academic_id).

### sm_students
| column | type | null | default |
|---|---|---|---|
| id | int UNSIGNED | NO | PK auto |
| admission_no | int | YES | NULL |
| roll_no | int | YES | NULL |
| first_name / last_name | varchar(70) | YES | NULL |
| full_name | varchar(130) | YES | NULL |
| date_of_birth | date | YES | NULL |
| caste | varchar(50) | YES | NULL |
| email | varchar(50) | YES | NULL |
| mobile | varchar(20) | YES | NULL |
| admission_date | date | YES | NULL |
| student_photo | varchar(191) | YES | NULL |
| age / height | varchar(20) | YES | NULL |
| weight | varchar(200) | YES | NULL |
| current_address / permanent_address | text | YES | NULL |
| national_id_no / local_id_no | varchar(25) | YES | NULL |
| bank_account_no | varchar(30) | YES | NULL |
| bank_name | varchar(25) | YES | NULL |
| previous_school_details | varchar(500) | YES | NULL |
| aditional_notes | text | YES | NULL |
| ifsc_code | varchar(50) | YES | NULL |
| document_title_1..4 / document_file_1..4 | varchar(200) | YES | NULL |
| active_status | tinyint | NO | 1 |
| custom_field | text | YES | NULL |
| created_at / updated_at | timestamp | YES | NULL |
| bloodgroup_id / religion_id | int UNSIGNED | YES | NULL |
| route_list_id / dormitory_id / vechile_id / room_id | int UNSIGNED | YES | NULL |
| student_category_id | int UNSIGNED | YES | NULL |
| student_group_id | int UNSIGNED | YES | NULL |
| class_id | int UNSIGNED | YES | NULL |
| section_id | int UNSIGNED | YES | NULL |
| session_id | int UNSIGNED | YES | NULL |
| parent_id | int UNSIGNED | YES | NULL |
| user_id | int UNSIGNED | YES | NULL |
| role_id | int UNSIGNED | YES | NULL |
| gender_id | int UNSIGNED | YES | NULL |
| school_id | int UNSIGNED | NO | 1 |
| academic_id | int UNSIGNED | YES | NULL |

FKs: `user_id`→users, `parent_id`→sm_parents, `class_id`→sm_classes, `section_id`→sm_sections, `session_id`→sm_sessions, `student_category_id`→sm_student_categories, `student_group_id`→sm_student_groups, `bloodgroup_id`/`religion_id`/`gender_id`→sm_base_setups. Scoping: **S**, **A**, **Se**. NOTE: current class/section/session stored inline here AND historically in sm_student_records (year-over-year history table, not in this scope).

### sm_parents
| column | type | null | default |
|---|---|---|---|
| id | int UNSIGNED | NO | PK auto |
| fathers_name / fathers_mobile / fathers_occupation / fathers_photo | varchar(200) | YES | NULL |
| mothers_name / mothers_mobile / mothers_occupation / mothers_photo | varchar(200) | YES | NULL |
| relation | varchar(200) | YES | NULL |
| guardians_name / guardians_mobile / guardians_email / guardians_occupation | varchar(200) | YES | NULL |
| guardians_relation | varchar(30) | YES | NULL |
| guardians_photo / guardians_address | varchar(200) | YES | NULL |
| is_guardian | int | YES | NULL |
| active_status | tinyint | NO | 1 |
| created_at / updated_at | timestamp | YES | NULL |
| user_id | int UNSIGNED | YES | 1 |
| school_id | int UNSIGNED | YES | 1 |
| academic_id | int UNSIGNED | YES | NULL |

FKs: `user_id`→users.id. Scoping: **S**, **A**.

---

## ACADEMIC STRUCTURE

### sm_classes
| column | type | null | default |
|---|---|---|---|
| id | int UNSIGNED | NO | PK auto |
| class_name | varchar(15) | NO | — |
| pass_mark | double | YES | NULL |
| active_status | tinyint | NO | 1 |
| created_at / updated_at | timestamp | YES | NULL |
| created_by / updated_by | int UNSIGNED | YES | 1 |
| school_id | int UNSIGNED | YES | 1 |
| academic_id | int UNSIGNED | YES | NULL |
| parent_id | int | YES | NULL |
| shift_id | int | YES | NULL |

Scoping: **S**, **A**. `parent_id` = original class id when copied per academic year.

### sm_sections
| column | type | null | default |
|---|---|---|---|
| id | int UNSIGNED | NO | PK auto |
| parent_id | int | YES | NULL |
| section_name | varchar(15) | NO | — |
| active_status | tinyint | NO | 1 |
| created_at / updated_at | timestamp | YES | NULL |
| created_by / updated_by | int UNSIGNED | YES | 1 |
| school_id | int UNSIGNED | YES | 1 |
| un_academic_id | int UNSIGNED | YES | NULL |
| academic_id | int UNSIGNED | YES | 1 |

Scoping: **S**, **A**.

### sm_class_sections (class↔section join)
| column | type | null | default |
|---|---|---|---|
| id | int UNSIGNED | NO | PK auto |
| active_status | tinyint | NO | 1 |
| created_at / updated_at | timestamp | YES | NULL |
| class_id | int UNSIGNED | YES | NULL |
| section_id | int UNSIGNED | YES | NULL |
| school_id | int UNSIGNED | YES | 1 |
| academic_id | int UNSIGNED | YES | 1 |
| parent_id | int | YES | NULL |
| shift_id | int | YES | NULL |

FKs: `class_id`→sm_classes, `section_id`→sm_sections. Scoping: **S**, **A**.

### sm_subjects
| column | type | null | default |
|---|---|---|---|
| id | int UNSIGNED | NO | PK auto |
| subject_name | varchar(255) | NO | — |
| subject_code | varchar(255) | YES | NULL |
| pass_mark | double | YES | NULL |
| subject_type | enum('T','P') | NO | — (T=Theory,P=Practical) |
| active_status | tinyint | NO | 1 |
| created_at / updated_at | timestamp | YES | NULL |
| created_by / updated_by | int UNSIGNED | YES | 1 |
| school_id | int UNSIGNED | YES | 1 |
| academic_id | int UNSIGNED | YES | 1 |
| parent_id | int | YES | NULL |

Scoping: **S**, **A**.

### sm_assign_subjects (teacher↔class↔section↔subject)
| column | type | null | default |
|---|---|---|---|
| id | int UNSIGNED | NO | PK auto |
| active_status | tinyint | NO | 1 |
| created_at / updated_at | timestamp | YES | NULL |
| teacher_id | int UNSIGNED | YES | NULL |
| class_id | int UNSIGNED | YES | NULL |
| section_id | int UNSIGNED | YES | NULL |
| subject_id | int UNSIGNED | YES | NULL |
| created_by / updated_by | int UNSIGNED | YES | 1 |
| school_id | int UNSIGNED | YES | 1 |
| academic_id | int UNSIGNED | YES | 1 |
| parent_id | int | YES | NULL |
| shift_id | int | YES | NULL |

FKs: `teacher_id`→sm_staffs, `class_id`→sm_classes, `section_id`→sm_sections, `subject_id`→sm_subjects. Scoping: **S**, **A**.

### sm_class_teachers (class-teacher assignment)
| column | type | null | default |
|---|---|---|---|
| id | int UNSIGNED | NO | PK auto |
| active_status | tinyint | NO | 1 |
| created_at / updated_at | timestamp | YES | NULL |
| teacher_id | int UNSIGNED | YES | NULL |
| assign_class_teacher_id | int UNSIGNED | YES | NULL |
| created_by / updated_by | int UNSIGNED | YES | 1 |
| school_id | int UNSIGNED | YES | 1 |
| academic_id | int UNSIGNED | YES | 1 |
| shift_id | int | YES | NULL |

FKs: `teacher_id`→sm_staffs, `assign_class_teacher_id`→sm_class_sections.id. Scoping: **S**, **A**.

### sm_student_categories
`id`, `category_name` varchar(100) NOT NULL, timestamps, created_by/updated_by, school_id, academic_id. Scoping: **S**, **A**.

### sm_student_groups
`id`, `group` varchar(200) NOT NULL, active_status, timestamps, created_by/updated_by, school_id, academic_id. Scoping: **S**, **A**.

---

## SESSIONS / YEARS / SETTINGS

### sm_academic_years
| column | type | null | default |
|---|---|---|---|
| id | int UNSIGNED | NO | PK auto |
| year | varchar(200) | NO | — |
| title | varchar(200) | NO | — |
| starting_date | date | NO | — |
| ending_date | date | NO | — |
| copy_with_academic_year | varchar(191) | YES | NULL |
| active_status | tinyint | NO | 1 |
| created_at / updated_at | varchar(191) | YES | NULL |
| created_by / updated_by | int UNSIGNED | YES | 1 |
| school_id | int UNSIGNED | YES | 1 |

Seed: id 1 = '2025' Jan-Dec. Scoping: **S**. NOTE: created_at/updated_at are varchar here (not timestamp) in the original — normalize to timestamps in EasySchool.

### sm_sessions
`id`, `session` varchar(255) NOT NULL, active_status, timestamps, created_by/updated_by, school_id. Scoping: **S**. Referenced by users.selected_session / general_settings.session_id.

### sm_base_setups (generic lookup: gender, religion, blood group, grouped by base_group_id)
`id`, `base_setup_name` varchar(255) NOT NULL, active_status, timestamps, created_by/updated_by, `base_group_id`, school_id.
Seed groups: 1=Gender (Male/Female/Others), 2=Religion, 3=Blood group. Scoping: **S**.

### sm_general_settings (per-school config, one row)
Core: school_name, site_title, school_code, address/phone/email, file_size ('102400'), currency ('USD'), currency_symbol ('$'), currency_format ('symbol_amount'), logo/favicon, system_version, currency_code, language_name ('en'), session_year ('2025'), copyright_text, website_url, week_start_id, time_zone_id, attendance_layout (1), session_id, language_id (1), date_format_id (1), ss_page_load (3), sub_topic_enable (1), direct_fees_assign (0), with_guardian (1), result_type, due_fees_login (0), two_factor (0), active_theme ('edulia'), queue_connection ('database'), role_based_sidebar (0), shift_enable (0), carry_forword_due_day (60), academic_id, session_id.
Plus ~65 module toggle ints (Lesson, Chat, FeesCollection, InfixBiometrics, ResultReports, TemplateSettings, MenuManage, RolePermission, StudentAbsentNotification, ParentRegistration, OnlineExam, BulkPrint, Wallet, ExamPlan, BehaviourRecords, DownloadCenter, multiple_roll, promotionSetting, etc.). Scoping: **S**, **A**, ref session_id.

> For EasySchool: model general settings as a normalized `settings` key/value table OR a slim settings row + a separate `feature_flags` table, rather than 100 columns. This is a structural improvement over the original.

---

## HR / ORG LOOKUPS

### sm_designations
`id`, `title` varchar(255), active_status, timestamps, created_by/updated_by, school_id, is_saas. Scoping: **S** (no academic_id).

### sm_human_departments
`id`, `name` varchar(191), active_status, timestamps, created_by/updated_by, school_id, is_saas. Scoping: **S**.

---

## MODULE / PERMISSION FRAMEWORK

### sm_modules
`id`, `name` varchar(191) NOT NULL, active_status, `order` int NOT NULL, timestamps, created_by/updated_by, school_id.
Seed: 20 modules (Dashboard, Admin Section, Student Information, Teacher, Fees Collection, Accounts, Human resource, Leave Application, Examination, Academics, HomeWork, Communicate, Library, Inventory, Transport, Dormitory, Reports, System Settings, Common, Lesson). Scoping: **S**.

### sm_module_links
`id`, `module_id`, `name`, `route`, active_status, created_by/updated_by, school_id, timestamps. FK `module_id`→sm_modules. Scoping: **S**.

### sm_module_permission_assigns (role↔module grant)
`id`, active_status, timestamps, `module_id`, `role_id`, created_by/updated_by, school_id. FKs `module_id`→sm_modules, `role_id`→roles. Scoping: **S**.

---

## MULTI-TENANCY NOTES (for single-school conversion)
- Every foundation table carries `school_id` (default 1) → DROP in EasySchool single-school build.
- Academic-year scoping (`academic_id`) present on: sm_students, sm_parents, sm_classes, sm_sections, sm_class_sections, sm_subjects, sm_assign_subjects, sm_class_teachers, sm_student_categories, sm_student_groups, sm_general_settings → **KEEP** (core logic).
- Session scoping: `session_id`/`selected_session` on users, sm_students, sm_general_settings → sm_sessions → **KEEP**.
- `parent_id` on class/section/subject/class_section = self-reference to the "template" row copied when a new academic year is generated (year-over-year cloning) → **KEEP as `template_id`** (rename for clarity).
- `shift_id` columns = optional shift management (shift_enable flag).
