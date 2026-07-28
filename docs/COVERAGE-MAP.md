# EasySchool ERP — the reference system Feature Coverage Map

This is the authoritative feature/module coverage map used to guarantee the EasySchool ERP rebuild leaves **no the reference system feature behind**.

**Scope rule:** Target is a **single-school** system. **SaaS subscription / billing / purchase-code / subdomain-tenancy is EXCLUDED.** Everything else — **including Inventory** — is **IN scope**. **Multi-language is IN scope.**

**Status legend:**
- `DONE` — already built in the rebuild (Foundation, Access, AcademicCore basics).
- `PLANNED` — in scope, not yet built.
- `EXCLUDED (SaaS billing only)` — deliberately dropped (SaaS/tenancy/billing plumbing only).

the reference system source key: nwidart modules live under `Modules/`; controllers under `app/Http/Controllers/Admin/<Area>`; root models are `app/Sm*.php`; routes in `routes/admin_tenant.php`, `routes/api.php`, `routes/v2api.php`.

---

## Coverage Table

| Feature Area | the reference system source (module / controllers / tables) | Target EasySchool module | Status |
|---|---|---|---|
| **— Academic —** | | | |
| Base setup / school profile | `SystemSettings/SmBaseSetupController`, `SmBaseSetup`, `SmSchool` | Foundation | `DONE` |
| Academic year / session scoping | `SmSessionController`, `ApiSmAcademicYearController`, `SmAcademicYear`, `SmSession` | Foundation (academic-year scope) | `DONE` |
| Classes | `Academics/SmClassController`, `SmClass`, `SmClassSection` | AcademicCore | `DONE` |
| Sections | `SmSectionController`, `SmSection` | AcademicCore | `DONE` |
| Subjects & optional/elective subjects | `Academics/SmSubjectController`, `SmSubject`, `SmClassOptionalSubject`, `SmOptionalSubjectAssign` | AcademicCore | `DONE` |
| Assign subjects (class-subject map) | `GlobalAssignSubjectController`, `SmAssignSubject` | AcademicCore | `PLANNED` |
| Class rooms | `Academics/SmClassRoomController`, `SmClassRoom` | Timetable (Classrooms) | `DONE` |
| Class time / periods | `Academics/SmClassTimeController`, `SmClassTime` | Timetable (Periods) | `DONE` |
| Class routine / timetable | `XSmClassRoutineNewController`, `SmClassRoutine`, `SmClassRoutineUpdate` | Timetable (Builder + Routine + Teacher) | `DONE` |
| Assign class teacher | `Academics/SmAssignClassTeacherController`, `SmAssignClassTeacher`, `SmClassTeacher` | AcademicCore | `PLANNED` |
| Teacher academics / classes panel | `teacher/SmAcademicsController`, `SmTeacherController` | AcademicCore | `PLANNED` |
| Promote students / student promotion | `SmStudentPromoteController`, `SmStudentPromotion` | AcademicCore | `PLANNED` |
| Lesson plan | `Modules/Lesson`, `Lesson` module | AcademicCore (Lesson Plan) | `PLANNED` |
| **— People (Students / Parents / Staff) —** | | | |
| Student admission / info | `StudentInfo/SmStudentAdmissionController`, `SmStudent`, `SmStudentDocument`, `SmStudentTimeline` | AcademicCore (Students, walk-in) + Admission (public application → staff confirm, with admin-configurable required-document uploads staged until confirm) + `StudentDocument` (per-student document list, view/print) | `DONE` |
| Student categories / groups | `SmStudentCategoryController`, `SmStudentGroupController`, `SmStudentCategory`, `SmStudentGroup` | People (Students) | `PLANNED` |
| Student parents / guardians | `SmStudentParentController`, `SmParent`, `StaffAsParent` | People (Guardians) | `PLANNED` |
| Multi-record / bulk student ops | `StudentMultiRecordController`, `SmStudentExcelFormat`, `StudentBulkTemporary` | People (Students) | `PLANNED` |
| Student / frontend student list | `FrontendStudentListController`, `SmStudentReportController` | People (Students) | `PLANNED` |
| Custom fields (student/staff forms) | `SmCustomFieldController` | People / System (Custom Fields) | `PLANNED` |
| Graduate / ex-student list | `GraduateListController`, `routes/graduate.php` | People (Graduates) | `PLANNED` |
| Alumni panel | `Modules? Alumni (add-on)`, `Alumni/AlumniPanelController`, `routes/alumni.php` | People (Alumni) | `PLANNED` |
| **— Attendance —** | | | |
| Student attendance | `StudentInfo/SmStudentAttendanceController`, `SmStudentAttendance`, `SmStudentAttendanceImport` | Attendance | `PLANNED` |
| Subject-wise attendance | `SmSubjectAttendanceController`, `SmSubjectAttendance` | Attendance | `PLANNED` |
| Staff attendance | `SmStaffAttendence`, `SmStaffAttendanceImport`, `StudentAttendanceBulk` | Attendance (Staff) | `PLANNED` |
| Exam attendance | `SmExamAttendance`, `SmExamAttendanceChild` | Attendance (Exam) | `PLANNED` |
| Student absent notification | `Modules/StudentAbsentNotification` | Attendance (Notifications) | `PLANNED` |
| Biometric attendance import | `InfixBiometrics` (add-on, disabled) | Attendance (Biometric) | `PLANNED` |
| **— Examination —** | | | |
| Exam types / setup | `Examination/SmExamController`, `SmExamSetupController`, `SmExam`, `SmExamType`, `SmExamSetup`, `SmExamSetting` | Examination | `PLANNED` |
| Exam schedule / routine | `SmExamRoutineController`, `SmExamSchedule`, `SmExamScheduleSubject` | Examination | `PLANNED` |
| Marks registration & grades | `SmMarksGradeController`, `SmMarksRegister(+Child)`, `SmMarksGrade`, `SmMarkStore`, `SmExamMarksRegister` | Examination | `PLANNED` |
| Marks by SMS | `SmMarksSendSms`, `SmMarksRegisterChild` | Examination / Communication | `PLANNED` |
| Result store / merit list | `SmResultStore`, `SmTemporaryMeritlist`, `SmCustomTemporaryResultController`, `SmCustomTemporaryResult` | Examination (Results) | `PLANNED` |
| Custom result / progress card settings | `Examination/CustomResultSettingController`, `CustomResultSetting`, `SmExamSignatureSettings` | Examination (Results) | `PLANNED` |
| Seat plan | `SmSeatPlan`, `SmSeatPlanChild` | Examination (auto-generated, room+bench assignment, anti-cheating mix) + Printing (room/all-room printable charts) | `DONE` |
| Exam plan | `Modules/ExamPlan` | Examination (Exam Plan) | `PLANNED` |
| Result reports (tabulation/progress/marksheet) | `Report/SmReportController` (tabulation, progress card, marksheet) | Reports (results/merit) + Printing (Marksheet/Tabulation/Progress Card) | `DONE` |
| **— Fees & Accounts —** | | | |
| Fees types / groups / masters | `Modules/Fees`, `SmFeesMasterController`, `SmFeesType`, `SmFeesGroup`, `SmFeesMaster` | Fees | `PLANNED` |
| Fees assign / discount / carry-forward | `SmFeesAssign`, `SmFeesAssignDiscount`, `SmFeesDiscount`, `SmFeesCarryForward` | Fees (assign + discount/scholarship attach with reason + carry-forward) | `DONE` |
| Fees collection / payment | `FeesCollection/SmFeesCollectController`, `SmFeesPayment`, `Parent/Student SmFeesController` | Fees (Collection) | `PLANNED` |
| Fees reports / collection reports | `SmFeesReportController`, `SmCollectionReportController`, balance/fine reports | Reports (Fees Collection + Due) | `DONE` |
| Wallet | `Modules/Wallet` | Fees (Wallet) | `PLANNED` |
| Payment gateways / methods | `SmPaymentGatewayController`, `SmPaymentMethhod` (gateway add-ons excluded) | Fees (Stripe + bKash + Nagad real integration, manual channels, Guardian Portal checkout) | `DONE` |
| Income | `Accounts/SmAddIncomeController`, `SmIncomeHeadController`, `SmAddIncome`, `SmIncomeHead` | Accounting (typed heads + transactions) | `DONE` |
| Expense | `Accounts/SmAddExpenseController`, `SmExpenseHeadController`, `SmAddExpense`, `SmExpenseHead` | Accounting (typed heads + transactions) | `DONE` |
| Bank accounts / chart of accounts | `SmBankAccountController`, `SmChartOfAccountController`, `SmBankAccount`, `SmAmountTransfer` | Accounting (Bank Accounts + Transfers, derived balance) | `DONE` |
| **— HR & Payroll —** | | | |
| Staff / employees | `Hr/SmStaffController`, `SmStaff`, `SmHumanDepartmentController`, `SmDesignationController` | HR (Staff) | `PLANNED` |
| Payroll generate / salary templates | `SmHrPayrollGenerate`, `SmHrSalaryTemplate`, `SmHrPayrollEarnDeduc` | Payroll (templates + generate + payslips) | `DONE` |
| Payroll bulk print / reports | `BulkPrint` (payroll views), `payroll-report` routes | Payroll (Summary; bulk-print PLANNED) | `PARTIAL` |
| Leave management | `Leave/SmLeaveDefineController`, `SmLeaveRequestController`, `SmLeaveTypeController`, `SmLeaveDefine`, `SmLeaveRequest`, `SmLeaveType` | Leave (Types + Applications + Approvals + Balance) | `DONE` |
| Teacher evaluation | `TeacherEvaluationController`, `TeacherEvaluationReportController` | Leave (Teacher Evaluation) | `DONE` |
| Shift management | `ShiftController`, `ShiftModuleDataGetController` | Leave (Shifts + Assign) | `DONE` |
| **— Library —** | | | |
| Books / categories | `Library/SmBookController`, `SmBookCategoryController`, `SmBook`, `SmBookCategory`, `LibrarySubject` | Library | `PLANNED` |
| Members / book issue-return | `SmLibraryMemberController`, `SmLibraryMember`, `SmBookIssue` | Library | `PLANNED` |
| **— Inventory —** | | | |
| Items / categories / store | `Inventory/SmItemController`, `SmItemCategoryController`, `SmItemStoreController`, `SmItem`, `SmItemCategory`, `SmItemStore` | Inventory | `PLANNED` |
| Suppliers | `Inventory/SmSupplierController`, `SmSupplier` | Inventory | `PLANNED` |
| Item receive / issue / sell | `SmItemSellController`, `SmItemReceive(+Child)`, `SmItemIssue`, `SmItemSell(+Child)`, `SmInventoryPayment`, `SmProductPurchase` | Inventory | `PLANNED` |
| **— Transport —** | | | |
| Routes / vehicles / assign | `Transport/SmRouteController`, `SmVehicleController`, `SmAssignVehicleController`, `SmTransportController`, `SmRoute`, `SmVehicle`, `SmAssignVehicle` | Transport | `PLANNED` |
| Transport reports | `student-transport-report` routes | Reports (Transport) | `PLANNED` |
| **— Dormitory / Hostel —** | | | |
| Dormitory / rooms / room types | `Dormitory/SmDormitoryController`, `SmRoomListController`, `SmRoomTypeController`, `SmDormitoryList`, `SmRoomList`, `SmRoomType` | Dormitory | `PLANNED` |
| Dormitory reports | `student-dormitory-report` routes | Reports (Dormitory) | `PLANNED` |
| **— Communication —** | | | |
| Notice board | `Communicate/SmNoticeController`, `SmNoticeBoard` | Communication | `PLANNED` |
| SMS / Email templates & gateways | `SmsTemplateController`, `SmsEmailTemplateController`, `SmsTemplate`, `SmSmsGateway`, `SmEmailSetting`, `SmEmailSmsLog`, `SmSendMessage` | Communication | `PLANNED` |
| Notifications | `SmNotificationController`, `SmNotification` | Communication (Notifications) | `PLANNED` |
| Chat / messaging | `Modules/Chat` | Chat (direct + groups + block) | `DONE` |
| Events | `SmEventController`, `SmEvent` | Communication (Events) | `PLANNED` |
| Email/SMS logs | `SmEmailSmsLog`, `SmUserLog` | Communication (Logs) | `PLANNED` |
| **— Front Office / Admin Section —** | | | |
| Admission query / enquiry | `SmStudentAdmissionController` (query), `SmAdmissionQuery`, `SmAdmissionQueryFollowup` | FrontOffice (Enquiries + follow-ups) | `DONE` |
| Visitor book | `SmVisitorController`, `SmVisitor` | FrontOffice (Visitor Book) | `DONE` |
| Phone call log | `SmPhoneCallLog` | FrontOffice (Call Log) | `DONE` |
| Postal dispatch / receive | `AdminSection/SmPostalDispatchController`, `SmPostalReceiveController` | FrontOffice (Postal, one typed table) | `DONE` |
| Complaints | `SmComplaintController`, `SmComplaintTypeController`, `SmComplaint` | FrontOffice (Complaints + Types) | `DONE` |
| Behaviour records / incidents | `Modules/BehaviourRecords` (`Incident`, `AssignIncident`, comments) | Front Office (Behaviour) | `PLANNED` |
| Certificates (student) | `SmStudentCertificate`, `generate-certificate` routes | Documents (Certificate templates + generate) | `DONE` |
| ID cards (student & staff) | `SmStudentIdCardController`, `SmStudentIdCard`, `BulkPrint` id-card views | Documents (ID Card templates + generate) | `DONE` |
| Bulk print (certs/ID/fees/payroll) | `Modules/BulkPrint`, `InvoiceSetting`, `FeesInvoiceSetting` | Printing (batch print) + Documents | `DONE` |
| To-do list | `SmToDo` | Front Office (To-Do) | `PLANNED` |
| **— Homework & Study Material —** | | | |
| Homework | `Homework/SmHomeworkController`, `SmHomework`, `SmHomeworkStudent`, `SmStudentHomework`, `SmUploadHomeworkContent`, `teacher/HomeWorkController` | Academics (Homework) | `PLANNED` |
| Study material / content upload | `Modules/StudyMaterialSupport`, `StudyMaterial/*`, `GlobalUploadContentController`, `SmTeacherUploadContent`, `TeacherContentController` | Academics (Study Material) | `PLANNED` |
| Download center | `Modules/DownloadCenter`, `DownloadController` | Academics (Downloads) | `PLANNED` |
| **— Online Learning —** | | | |
| Online exam / question bank | `OnlineExam/SmOnlineExamController`, `SmQuestionBankController`, `SmQuestionGroupController`, `SmQuestionLevelController`, `SmOnlineExam*`, `SmQuestionBank*`, `Student/SmOnlineExamController` | OnlineExam | `DONE` |
| Question bank taking / marking | `SmStudentTakeOnlineExam*`, `OnlineExamStudentAnswerMarking` | OnlineExam (attempts/answers) | `DONE` |
| Courses | `SmCourseController`, `SmCourse`, `SmCourseCategory` | Online Learning (Courses) | `PLANNED` |
| Video watch / lecture tracking | `Modules/VideoWatch` | Online Learning | `PLANNED` |
| Live class (Zoom/Jitsi/Gmeet/BBB) | `BBB`, `Jitsi`, `Gmeet`, `InAppLiveClass` add-ons (disabled) | Online Learning (Live Class) | `PLANNED` |
| AI content generation | `AiContent` add-on (disabled) | Online Learning (AI) | `PLANNED` |
| **— Front Website / CMS —** | | | |
| Home / about / contact pages | `FrontSettings/HomePageController`, `AboutPageController`, `SmContactPage`, ... | Builder (CMS Pages + Blocks) | `DONE` |
| Sliders / testimonials / donors / social | `SmHomeSliderController`, `SmTestimonialController`, ... | Builder (Sliders + Testimonials + Settings social) | `DONE` |
| News / news categories | `SmNewsController`, `UserNewsController`, `NewsHeadingController`, `SmNews`, `SmNewsCategory`, `SmNewsPage`, `News` add-on | Builder (compose via page sections) | `DEFERRED (user opted for full page builder)` |
| Courses / course pages (public) | `SmCourseHeadingController`, `SmCourseListController`, `SmCoursePage` | Builder (compose via page sections) | `DEFERRED (user opted for full page builder)` |
| Academic calendar / routine pages | `SmAcademicCalendarController`, `SmClassExamRoutinePageController`, `SmFrontClassRoutineController`, `SmFrontExamRoutineController` | Front CMS | `PLANNED` |
| Custom pages / footer / menus | `SmPageController`, `SmFooterWidgetController`, `SmHeaderMenuManager` | Builder (Pages + Menus header/footer) | `DONE` |
| Page builder / option builder | `PageBuilderController`, `StoreMenuController`, ... | Builder (block-based page builder) | `DONE` |
| Forum | `Forum` add-on, `UserForumController` | (deferred) | `DEFERRED (user opted for full page builder)` |
| **— Reports —** | | | |
| Student / guardian / attendance reports | `Report/*`, `SmStudentAttendanceReportController`, `student-report`, `guardian-report` routes | Reports (Student/Guardian/Attendance) | `DONE` |
| Academic history / marksheet reports | `StudentAcademicHistoryController`, `SubjectMarkSheetReportController` | Printing (Marksheet + Progress Card) | `DONE` |
| Login / transaction / fine reports | `student-login-report`, `transaction-report`, `fine-report` routes, `SmUserLog` | Reports (Wallet Statement; login/fine PLANNED) | `PARTIAL` |
| **— System / Settings —** | | | |
| General settings / currency | `SmGeneralSettings`, `GeneralSettings/SmManageCurrencyController`, `SmCurrency` | System (Settings) | `DONE` |
| Localization / timezone / date format | `SystemSettings/SmLocalizationController`, `SmTimeZone`, `SmDateFormat` | Settings (Localization) | `DONE` |
| Holidays / weekends | `SmHolidayController`, `SmHoliday`, `SmWeekend` | Settings (Holidays + weekend_days) | `DONE` |
| Themes / styles / background | `Style/ThemeController`, `SmBackGroundSettingController` | Settings (Appearance) + Builder (public colors) | `DONE` |
| Menu manage / custom menu / dashboard | `Modules/MenuManage`, `MenuGenerateController`, `CustomMenu` add-on, `SmDashboardSetting` | System (Menu) | `PLANNED` |
| Module manager / plugins | `SystemSettings/InfixModuleManagerController`, `PluginController`, `SmModule`, `SmModuleLink`, `SmAddOns`, `InfixModuleManager` | System (Modules) | `PLANNED` |
| Maintenance mode / preloader | `MaintenanceModeController`, `PreloaderSettingController` | System (Settings) | `PLANNED` |
| Backup / version | `SmBackup`, `SmSystemVersion` | System (Backup) | `PLANNED` |
| Login access control / user log | `SmLoginAccessControlController`, `SmUserLog`, `SmFrontendPersmission` | System (Access) | `PLANNED` |
| Two-factor auth | `Modules/TwoFactorAuth` | System (Security) | `PLANNED` |
| Template settings | `Modules/TemplateSettings` | System (Templates) | `PLANNED` |
| Import / export center | `ImportController`, `UploadFileController`, `*Import` models | System (Import/Export) | `PLANNED` |
| **— Access (Roles / Auth) —** | | | |
| Roles & permissions | `Modules/RolePermission`, `RolePermission/SmRolePermissionController`, `SmRolePermission`, `Role`, `SmModulePermission(+Assign)` | Access (~127 action-level permissions, role CRUD + matrix UI, user role assignment — management done; route/nav *enforcement* across other modules is a separate future sweep) | `PARTIAL` |
| Authentication / users | `SmAuthController`, `Auth/*`, `UserController`, `User` | Access | `DONE` |
| Instructions / login page | `SmInstructionController`, `SmInstruction` | Access | `PLANNED` |
| **— Misc —** | | | |
| Search (global) | `SmSearchController` | Misc | `PLANNED` |
| Dashboards (admin/teacher/student/parent) | `HomeController`, `SmStudentPanelController`, `SmParentPanelController`, panel controllers | Dashboard (admin, DONE) + GuardianPortal (parent DONE, student self-view DONE — own profile/attendance/notices/leave request); teacher panel still missing | `PARTIAL` |
| Customer panel | `Customer/SmCustomerPanelController` | Misc | `PLANNED` |
| University module | `University` add-on (disabled) | Misc (University) | `PLANNED` |
| Lead / CRM | `Lead` add-on (disabled) | Misc (Lead) | `PLANNED` |

---

## Modules explicitly EXCLUDED

Only SaaS/tenancy/billing plumbing is dropped. All academic and operational features above remain in scope.

| the reference system source | Reason |
|---|---|
| `SaasSubscription` (module + `modules_statuses.json`) | SaaS subscription plans & recurring billing — not needed for single school. |
| `SaasRolePermission`, `SaasHr` | SaaS-tier variants of role-permission / HR; superseded by the single-school `RolePermission` / HR modules. |
| Purchase-code / license activation | Marketplace license validation — irrelevant to owned single-school deploy. |
| Subdomain / multi-tenant plumbing | `routes/tenant.php`, `routes/admin_tenant.php` tenancy scoping, per-tenant DB — collapsed to one school. |
| Subscription payment gateways *as billing plumbing* | Gateway code is REUSED for student fees (in scope); only the SaaS-billing use is excluded. |

> Note: `Inventory` is DISABLED in `modules_statuses.json` but is **IN scope** per the rebuild rule and appears above as `PLANNED`.

---

## Cross-cutting concerns

These apply across every module and must be baked into the rebuild foundation, not bolted on per-feature.

- **Multi-language (IN scope):** `LanguageController`, `SmLanguageController`, `SmLanguage`, `SmLanguagePhrase`, `Language` model, per-module `Resources/lang/<locale>/*.php`. Every module ships translatable strings; RTL supported.
- **Roles & permissions:** `Modules/RolePermission`, `SmRolePermission`, `SmModulePermission(+Assign)`, `SmModulePermissionAssign`, `SmFrontendPersmission`. Reference system gates every controller/route by permission with a role-scoped menu. Our rebuild has the full management side (roles, ~127 permissions, matrix UI, user assignment) but routes are still `auth`-only — gating every module's routes + filtering the sidebar by permission remains a follow-up.
- **Academic-year scoping:** `SmAcademicYear`, `SmSession`, `YearCheck`, `CheckClass`, `CheckSection` helpers. Nearly all data rows carry `academic_id` / `school_id`; queries filtered by active year.
- **Notifications:** `SmNotification`, `SmNotificationController`, `StudentAbsentNotification` module, SMS/email templates + gateways, `SmEmailSmsLog`. Fan-out to admin/teacher/student/parent channels.
- **Import / export:** `ImportController`, `UploadFileController`, `*Import` models (student/staff/attendance), Excel formats (`SmStudentExcelFormat`), bulk temporary tables. CSV/Excel in, datatable/report out.
- **PDF / print:** `UserPDFController`, `PDF` add-on, `Modules/BulkPrint` (certificates, ID cards, fees invoices, payroll), invoice settings, tabulation/progress-card/marksheet print routes.
