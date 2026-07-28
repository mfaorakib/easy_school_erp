# 02 — Foundation & Settings Subsystem (the reference system) — Business Logic Spec

Source: `c:\reference-source` (Laravel, the reference system v9.0.0). Documents *behavior*, not code.

> EasySchool ERP note (single-school): drop `school_id`, SaaS bypass, subdomain tenancy.
> KEEP the academic-year scoping — it is core business logic.

## 0. Overview
Foundation = one settings row per school (`sm_general_settings`) + lookup tables (currency, date format, timezone, weekend, language) + one dominant rule: **almost every business table is scoped by academic year id (`academic_id`) AND `school_id`**, enforced via Eloquent global scopes + cached session helpers.

## 1. General Settings
- Table `sm_general_settings`, model `App\SmGeneralSettings`, **one row per school**. Eager-loads `currencyDetail`.
- Field groups: school identity (name/title/code/address/phone/email/website/copyright); branding (logo, favicon, preloader, active_theme default `edulia`); localization (currency code/symbol/format, language_id, date_format_id, time_zone_id, week_start_id, ttl_rtl); **active-session pointers `session_id` & `academic_id` → sm_academic_years** (kept identical in non-University mode); file/perf (file_size, ss_page_load, queue_connection, email_driver); licensing (purchase_code, envato_*); module on/off flag columns (Lesson, Chat, Wallet, ExamPlan, etc. — legacy UI flags); behavior toggles (promotionSetting, direct_fees_assign, with_guardian, due_fees_login, two_factor, role_based_sidebar, shift_enable, carry_forword_due_day, result_type, income_head_id).
- **Global loading (no view composer):** cached helper `generalSetting()` (session-cached) resolves the row by `app('school')` → `request('school_id')` → auth user's school → fallback 1. Singleton `app('school_info')`, helper `schoolConfig()`. Related cached helpers: `systemDateFormat()`, `dateConvert()`, `dateTimeConvert()`, `textDirection()`, `userLanguage()`.
- **Editing (`SmSystemSettingController::updateGeneralSettingsData`):** identity fields updated for current school only; **localization fields (language, date_format, currency, timezone, copyright) propagated to ALL schools**; writes timezone→`.env APP_TIMEZONE`, school_name→`APP_NAME`; `week_start_id` change **renumbers all SmWeekend rows** so chosen day = order 1; clears session caches (`generalSetting`, `system_date_format`, `sessionId`). Demo-mode guard blocks saving.
- `config.json` (root) = app version metadata for updater. `general_settings.json` (root) = denormalized runtime cache snapshot; **DB is authoritative**.

## 2. Academic Session / Year (CRITICAL scoping)
- **`SmAcademicYear`** (`sm_academic_years`): `year`, `title`, `starting_date`, `ending_date`, `copy_with_academic_year` (CSV of model class names to clone), active_status, school_id. Global scope `ActiveStatusSchoolScope`.
- **`SmSession`** (`sm_sessions`): legacy parallel list; **NOT the scoping key**. The real active session = `sm_general_settings.session_id` = an id into `sm_academic_years`. So in practice "session id" == academic year id.
- **Active year resolution `getAcademicId()`:** (1) `session('sessionId')` if cached → (2) `generalSetting()->session_id` (or `un_academic_id` under University) → (3) first active `SmAcademicYear` → cache into `session('sessionId')`. `App\YearCheck` = non-cached parallel (getYear, AcStartDate, AcEndDate).
- **Create year (`SmAcademicYearController::store`):** saves year, **immediately activates it** (settings session_id=academic_id=new id, session_year=year, bust caches), then **auto-clones structure into new year**: always replicates `SmMarksGrade`; for each model in `copy_with_academic_year` replicates rows (`withoutGlobalScopes`) with `academic_id = new id`; regenerates `SmClassSection` for all class×section. ← core onboarding behavior.
- **Switch year (`sessionChange` AJAX):** updates settings session_id/academic_id + session_year, `session()->put('sessionId', id)`, refresh generalSetting cache.
- **Delete year:** refuses to delete the current active year; cascades delete of `copy_with_academic_year` models (academic_id=id) + that year's SmClassSection.
- **Scoping enforcement — global scopes (`app\Scopes`):**
  - `StatusAcademicSchoolScope`: `active_status=1 AND academic_id=getAcademicId() AND school_id=auth->school_id`. **Primary operational scope.**
  - `AcademicSchoolScope`: academic_id + school_id (no active_status).
  - `ActiveStatusSchoolScope`: active_status + school_id (no academic). Used by SmAcademicYear, SmBaseSetup.
  - `SchoolScope`: school_id only (honors request override). Used by SmWeekend.
  - SaaS super-admin bypass: skip school_id when `moduleStatusCheck('Saas') && is_administrator=='yes' && Session('isSchoolAdmin')===false && role_id==1`.
  - Cross-year reads use `withoutGlobalScope(...)`.
- **Replication rule:** every operational table carries nullable `academic_id` (FK to sm_academic_years, onDelete setNull) + `school_id`. New rows stamp `academic_id=getAcademicId()`, `school_id=auth->school_id`. Reads auto-filtered to active year+school.

## 3. Base Setup Lookups
- `SmCurrency` (`sm_currencies`): name, code, symbol, currency_type (S/C), currency_position (P/S), decimal/thousand separators. Referenced by settings.currency = **code** (join). Accessors translate type/position.
- `SmDateFormat` (`sm_date_formats`): PHP `format` string; drives systemDateFormat/dateConvert (cached).
- `SmTimeZone` (`sm_time_zones`): `time_zone` string; on save written to `.env APP_TIMEZONE`.
- `SmLanguage` (`sm_languages`): language list; settings.language_id + per-user User.language.
- `SmWeekend` (`sm_weekends`): weekday rows with `order`; week_start_id picks first day; save reorders. Global scope `SchoolScope`. Also hosts class-routine query helpers (academic_id+school_id filtered).
- `SmBaseSetup` (`sm_base_setups`): generic name/value grouped by `SmBaseGroup` (base_setup_name, base_group_id). Global scope `ActiveStatusSchoolScope`. Groups: 1 Gender, 2 Religion, 3 Blood group.

## 4. Multi-School Columns (single-tenant)
- `school_id` on nearly all tables (FK sm_schools, default 1). `SmSchool` = tenant root, hasOne `settings`.
- Current tenant → `app('school')` (default SmSchool::find(1)), settings → `app('school_info')`.
- SaaS super-admin bypass + `SubdomainMiddleware` tenant resolution.
- **EasySchool:** drop all of this; assume single implicit school.

## 5. Module Enable/Disable
`moduleStatusCheck($name)` (cached) is authoritative "is feature enabled": module registered in InfixModuleManager + ServiceProvider present + non-empty purchase_code + (if SaaS plan active) included in plan. The boolean columns on settings are secondary UI toggles.

> **EasySchool structural improvement:** replace the ~100-column `sm_general_settings` with a slim settings row + a normalized `feature_flags`/`settings` key-value table. Replace `moduleStatusCheck` purchase-code/SaaS logic with a simple enabled-modules registry.
