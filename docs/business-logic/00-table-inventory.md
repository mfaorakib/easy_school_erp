# the reference system → EasySchool ERP — Complete Table Inventory

**Source:** `c:\reference-source\andiedu (1).sql`
**Total tables found:** **307** `CREATE TABLE` statements.

This inventory groups every table in the source dump by functional domain so that no table is missed during the EasySchool ERP rebuild. Tables are listed by exact name (as they appear in the dump), with a short purpose note where the name makes it obvious. A final section flags multi-tenant / SaaS tables that are **excluded** from a single-school build.

---

## Academic Structure (Classes, Sections, Subjects, Sessions)

- `sm_classes` — class/grade definitions
- `sm_sections` — sections
- `sm_class_sections` — class↔section mapping
- `sm_class_rooms` — physical rooms
- `sm_class_times` — period/time slots
- `sm_subjects` — subjects
- `sm_assign_subjects` — subject→class assignment
- `sm_class_optional_subject` — optional subjects per class
- `sm_optional_subject_assigns` — optional subject assignments
- `sm_academic_years` — academic years
- `sm_sessions` — academic sessions
- `sm_shifts` / `shifts` — school shifts
- `sm_assign_class_teachers` — class teacher assignment
- `sm_class_teachers` — class teachers
- `check_classes` — class check/helper
- `sm_base_groups` — base/student groups
- `sm_base_setups` — base setup config
- `sm_student_groups` — student groups

## Class Routine / Timetable

- `sm_class_routines` — class timetable
- `sm_class_routine_updates` — routine change log
- `sm_class_exam_routine_pages` — exam routine pages
- `front_class_routines` — public class routine
- `front_exam_routines` — public exam routine

## Students & Admissions

- `sm_students` — student master
- `sm_student_attendances` — student attendance
- `sm_student_attendance_imports` — attendance bulk import
- `student_attendance_bulks` — attendance bulk staging
- `sm_subject_attendances` — subject-wise attendance
- `sm_student_categories` — student categories
- `sm_student_certificates` — certificates
- `sm_student_documents` — uploaded documents
- `sm_student_excel_formats` — excel import format
- `sm_student_homeworks` — student homework link
- `sm_student_id_cards` — ID card templates
- `sm_student_promotions` — promotion records
- `sm_student_registration_fields` — custom registration fields
- `sm_student_timelines` — student activity timeline
- `student_academic_histories` — academic history
- `student_records` — student records
- `student_record_temporaries` — record staging
- `student_bulk_temporaries` — bulk import staging
- `sm_admission_queries` — admission enquiries
- `sm_admission_query_followups` — enquiry follow-ups
- `graduates` — graduated students
- `sm_parents` — parent/guardian master

## Staff / HR & Payroll

- `sm_staffs` — staff master
- `sm_staff_attendences` — staff attendance
- `sm_staff_attendance_imports` — staff attendance import
- `staff_import_bulk_temporaries` — staff bulk staging
- `sm_staff_registration_fields` — staff custom fields
- `sm_designations` — job designations
- `sm_human_departments` — HR departments
- `sm_expert_teachers` — featured/expert teachers
- `sm_setup_admins` — admin setup
- `teacher_evaluations` — teacher evaluations
- `teacher_evaluation_settings` — evaluation settings
- `sm_hourly_rates` — hourly pay rates
- `sm_hr_payroll_earn_deducs` — payroll earnings/deductions
- `sm_hr_payroll_generates` — payroll generation
- `sm_hr_salary_templates` — salary templates
- `payroll_payments` — payroll payments
- `sm_leave_defines` — leave definitions
- `sm_leave_types` — leave types
- `sm_leave_requests` — leave requests
- `sm_leave_deduction_infos` — leave deduction info

## Examinations & Marks

- `sm_exams` — exams
- `sm_exam_types` — exam types
- `sm_exam_setups` — exam setup
- `sm_exam_settings` — exam settings
- `sm_exam_schedules` — exam schedules
- `sm_exam_schedule_subjects` — schedule subjects
- `sm_exam_attendances` — exam attendance
- `sm_exam_attendance_children` — exam attendance detail
- `sm_exam_marks_registers` — exam marks register
- `sm_exam_signatures` — signature setup for results
- `exam_merit_positions` — merit positions
- `exam_step_skips` — exam step skip config
- `all_exam_wise_positions` — position by exam
- `sm_marks_grades` — grading scale
- `sm_marks_registers` — marks register
- `sm_marks_register_children` — marks register detail
- `sm_marks_send_sms` — send marks via SMS
- `sm_mark_stores` — mark storage
- `sm_result_stores` — result storage
- `sm_custom_temporary_results` — temp custom results
- `custom_result_settings` — custom result settings
- `sm_temporary_meritlists` — temporary merit lists
- `frontend_exam_results` — public exam results
- `front_results` — public results
- `admit_cards` — admit cards
- `admit_card_settings` — admit card settings
- `seat_plans` / `sm_seat_plans` — seat plans
- `sm_seat_plan_children` — seat plan detail
- `seat_plan_settings` / `sm_seat_plan_settings` — seat plan settings

## Online Exam / Question Bank

- `sm_online_exams` — online exams
- `sm_online_exam_marks` — online exam marks
- `sm_online_exam_questions` — online exam questions
- `sm_online_exam_question_assigns` — question assignments
- `sm_online_exam_question_mu_options` — MCQ options
- `online_exam_student_answer_markings` — answer markings
- `sm_student_take_online_exams` — student attempts
- `sm_student_take_online_exam_questions` — attempt questions
- `sm_student_take_onln_ex_ques_options` — attempt options
- `sm_question_banks` — question bank
- `sm_question_bank_mu_options` — question bank MCQ options
- `sm_question_groups` — question groups
- `sm_question_levels` — difficulty levels

## Fees & Billing

- `sm_fees_assigns` — fee assignment
- `sm_fees_assign_discounts` — assigned discounts
- `sm_fees_carry_forwards` — carry-forward dues
- `sm_fees_discounts` — fee discounts
- `sm_fees_groups` — fee groups
- `sm_fees_masters` — fee masters
- `sm_fees_payments` — fee payments
- `sm_fees_types` — fee types
- `fees_carry_forward_logs` — carry-forward logs
- `fees_carry_forward_settings` — carry-forward settings
- `fees_installment_credits` — installment credits
- `fees_invoices` — fee invoices
- `fees_invoice_settings` — invoice settings
- `direct_fees_installments` — direct fee installments
- `direct_fees_installment_assigns` — installment assignment
- `direct_fees_reminders` — payment reminders
- `direct_fees_settings` — direct fees settings
- `dire_fees_installment_child_payments` — installment child payments
- `due_fees_login_prevents` — block login on dues
- `fm_fees_groups` — (fees module) fee groups
- `fm_fees_invoices` — fee invoices
- `fm_fees_invoice_chields` — invoice detail
- `fm_fees_invoice_settings` — invoice settings
- `fm_fees_transactions` — fee transactions
- `fm_fees_transaction_chields` — transaction detail
- `fm_fees_types` — fee types
- `fm_fees_weavers` — fee waivers
- `invoice_settings` — invoice settings
- `sm_product_purchases` — product purchases

## Accounting / Income / Expense / Bank

- `sm_add_incomes` — income entries
- `sm_income_heads` — income heads
- `sm_add_expenses` — expense entries
- `sm_expense_heads` — expense heads
- `sm_chart_of_accounts` — chart of accounts
- `sm_bank_accounts` — bank accounts
- `sm_bank_payment_slips` — bank payment slips
- `sm_bank_statements` — bank statements
- `sm_amount_transfers` — inter-account transfers
- `sm_payment_methhods` — payment methods (sic)
- `sm_payment_gateway_settings` — payment gateway settings
- `transcations` — transactions (sic)
- `wallet_transactions` — wallet transactions
- `sm_donors` — donors

## Library

- `sm_books` — books catalog
- `sm_book_categories` — book categories
- `sm_book_issues` — book issue/return
- `sm_library_members` — library members
- `library_subjects` — library subjects

## Inventory / Store / Assets

- `sm_items` — inventory items
- `sm_item_categories` — item categories
- `sm_item_issues` — item issues
- `sm_item_receives` — item receipts
- `sm_item_receive_children` — receipt detail
- `sm_item_sells` — item sales
- `sm_item_sell_children` — sale detail
- `sm_item_stores` — item stores/warehouses
- `sm_inventory_payments` — inventory payments
- `sm_suppliers` — suppliers

## Transport

- `sm_routes` — transport routes
- `sm_vehicles` — vehicles
- `sm_assign_vehicles` — vehicle→route/student assignment

## Dormitory / Hostel

- `sm_dormitory_lists` — dormitories
- `sm_room_lists` — hostel rooms
- `sm_room_types` — room types

## Lessons / Homework / Lesson Plan

- `sm_lessons` — lessons
- `sm_lesson_details` — lesson details
- `sm_lesson_topics` — lesson topics
- `sm_lesson_topic_details` — topic details
- `lesson_planners` — lesson planners
- `lesson_plan_topics` — lesson plan topics
- `sm_homeworks` — homework
- `sm_homework_students` — homework↔student
- `sm_upload_homework_contents` — homework uploads
- `sm_instructions` — instructions
- `sm_teacher_upload_contents` — teacher uploads
- `sm_upload_contents` — uploaded contents
- `contents` — content library
- `content_share_lists` — content sharing
- `content_types` — content types
- `sm_content_types` — content types
- `video_uploads` — video uploads

## Communication (Notice, Message, SMS, Email, Chat)

- `sm_notice_boards` — notice board
- `sm_events` — events
- `sm_holidays` — holidays
- `sm_weekends` — weekend config
- `sm_send_messages` — sent messages
- `sm_email_sms_logs` — email/SMS logs
- `sm_email_settings` — email settings
- `sm_sms_gateways` — SMS gateways
- `sms_templates` — SMS templates
- `custom_sms_settings` — custom SMS settings
- `sm_notifications` — notifications
- `sm_notification_settings` — notification settings
- `notifications` — Laravel notifications
- `absent_notification_time_setups` — absence-alert timing
- `sm_phone_call_logs` — phone call log
- `sm_postal_dispatches` — postal dispatch
- `sm_postal_receives` — postal receive
- `sm_visitors` — visitor log
- `sm_complaints` — complaints
- `sm_contact_messages` — contact form messages
- `chat_conversations` — chat conversations
- `chat_block_users` — blocked chat users
- `chat_groups` — chat groups
- `chat_group_users` — chat group members
- `chat_group_message_recipients` — group message recipients
- `chat_group_message_removes` — removed group messages
- `chat_invitations` — chat invitations
- `chat_invitation_types` — invitation types
- `chat_statuses` — chat status

## Behaviour / Incidents / Discipline

- `incidents` — incidents
- `assign_incidents` — assigned incidents
- `assign_incident_comments` — incident comments
- `behaviour_record_settings` — behaviour record settings
- `sm_to_dos` — to-do items

## Frontend CMS / Public Website

- `sm_about_pages` — about page
- `sm_contact_pages` — contact page
- `sm_course_pages` — course pages
- `sm_courses` — courses
- `sm_course_categories` — course categories
- `sm_news` — news
- `sm_news_categories` — news categories
- `sm_news_comments` — news comments
- `sm_news_pages` — news pages
- `sm_pages` — CMS pages
- `sm_home_page_settings` — homepage settings
- `sm_header_menu_managers` — header menu
- `sm_photo_galleries` — photo galleries
- `sm_video_galleries` — video galleries
- `sm_testimonials` — testimonials
- `sm_social_media_icons` — social media links
- `sm_form_downloads` — downloadable forms
- `sm_custom_links` — custom links
- `home_sliders` — homepage sliders
- `speech_sliders` — speech/message sliders
- `front_academic_calendars` — public academic calendar
- `andiedu__pages` — CMS pages (page builder)
- `andiedu__settings` — page builder settings

## Roles, Permissions & Modules

- `roles` / `infix_roles` — roles
- `permissions` — permissions
- `permission_sections` — permission grouping
- `assign_permissions` — permission assignment
- `infix_permission_assigns` — permission assignment
- `sm_role_permissions` — role permissions
- `sm_frontend_persmissions` — frontend permissions (sic)
- `sm_module_permissions` — module permissions
- `sm_module_permission_assigns` — module permission assignment
- `sm_modules` — modules
- `sm_module_links` — module links
- `infix_module_infos` — module info
- `infix_module_managers` — module manager
- `infix_module_student_parent_infos` — student/parent module info
- `sm_add_ons` — add-ons
- `plugins` — plugins
- `sm_menus` — menus
- `default_menus` — default menus
- `sidebars` — sidebar config
- `sm_dashboard_settings` — dashboard settings

## System Settings & Configuration

- `sm_general_settings` — general settings
- `sm_background_settings` — background settings
- `sm_calendar_settings` — calendar settings
- `sm_date_formats` — date formats
- `sm_time_zones` — time zones
- `sm_currencies` — currencies
- `sm_countries` / `countries` — countries
- `continents` / `continets` — continents (dup, sic)
- `colors` — colors
- `color_theme` — color theme
- `themes` — themes
- `sm_styles` — styles
- `sm_languages` / `languages` — languages
- `sm_language_phrases` — translation phrases
- `sm_custom_fields` — custom fields
- `sm_backups` — backups
- `maintenance_settings` — maintenance mode
- `two_factor_settings` — 2FA settings
- `sm_system_versions` — system version
- `version_histories` — version history
- `sm_user_logs` — user activity logs
- `user_otp_codes` — OTP codes
- `sm_social_media_icons` — (also CMS) social links

## Framework / Infrastructure (Laravel internals)

- `migrations` — schema migrations
- `jobs` — queued jobs
- `failed_jobs` — failed jobs
- `password_resets` — password resets
- `users` — auth users
- `oauth_access_tokens` — Passport access tokens
- `oauth_auth_codes` — Passport auth codes
- `oauth_clients` — Passport clients
- `oauth_personal_access_clients` — Passport personal clients
- `oauth_refresh_tokens` — Passport refresh tokens
- `pulse_aggregates` — Laravel Pulse aggregates
- `pulse_entries` — Laravel Pulse entries
- `pulse_values` — Laravel Pulse values

---

## Tenancy / SaaS tables (EXCLUDED in single-school build)

These tables exist to support the reference system's multi-school SaaS / subscription model. In a single-school EasySchool ERP build they are **not needed** — the `school_id` concept collapses to a single implicit tenant.

- `sm_schools` — school/tenant registry (multi-tenant root)
- `school_modules` — per-school module enablement
- `sm_backups` — per-tenant backups *(retain only if per-school backup is wanted; otherwise SaaS-oriented)*

> **Note on `school_id`:** Most `sm_*` tables carry a `school_id` column for tenant isolation. In the single-school rebuild these columns can be dropped or defaulted to a constant. No separate subscription/package/plan tables were found in this dump (the reference system handles licensing/packages outside this schema), so the SaaS footprint here is essentially just `sm_schools` + `school_modules`.
