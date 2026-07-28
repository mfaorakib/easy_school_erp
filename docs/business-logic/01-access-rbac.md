# Access Control / RBAC & Auth — Business-Logic Spec (the reference system)

> Reverse-engineered from the legacy Laravel app at `c:\reference-source`. This documents *observed behavior* so it can be
> replicated in the new build. Source files/tables are cited inline. Nothing in `c:\reference-source` was modified.

---

## 1. User types / roles & the single-table model

### 1.1 One `users` table for everyone
All human actors (super admin, admins, teachers, students, parents, and every staff sub-role) live in **one
`users` table** mapped by `App\User` (`c:\reference-source\app\User.php`). Auth uses a single Eloquent provider
(`config/auth.php` → provider `users` → `App\User`). There are **no separate auth tables per role**.

The role is a single integer column **`users.role_id`** (cast to integer in `User::$casts`). Role rows live in a
separate table **`infix_roles`** (model `Modules\RolePermission\Entities\InfixRole`; legacy alias `App\Role`).

`User` relation: `roles()` = `belongsTo(InfixRole::class, 'role_id', 'id')`
(`c:\reference-source\app\User.php` ~line 160).

### 1.2 System roles (seeded, fixed IDs)
Seeded by `c:\reference-source\Modules\RolePermission\Database\Migrations\2014_12_01_000001_create_infix_roles_table.php`.
These IDs are **hard-coded throughout the codebase** and must be preserved:

| role_id | Name         | type     |
|--------:|--------------|----------|
| 1 | Super admin  | System |
| 2 | Student      | System |
| 3 | Parents      | System |
| 4 | Teacher      | System |
| 5 | Admin        | System |
| 6 | Accountant   | System |
| 7 | Receptionist | System |
| 8 | Librarian    | System |
| 9 | Driver       | System |

Additional special value: **Alumni role_id = `200000106`** — a magic constant returned by
`App\GlobalVariable::isAlumni()` (`c:\reference-source\app\GlobalVariable.php` line 48-51), NOT a normal seeded row.

`infix_roles` columns: `id, name, type (System|User Defined), active_status (default 1), created_by, updated_by,
school_id (FK sm_schools, default 1), is_saas (default 0)`.

### 1.3 Custom (school-defined) roles
Admins create extra roles via `RolePermissionController::roleStore` — sets `type='User Defined'`,
`school_id = Auth::user()->school_id` (`c:\reference-source\Modules\RolePermission\Http\Controllers\RolePermissionController.php`).
- Role listing (`role()`) excludes `id=1` (Super admin), excludes `is_saas=1`, requires `active_status=1`, and scopes
  to `school_id = current OR type='System'`. Role id `3` (Parents) is hidden unless `generalSetting()->with_guardian == 1`.
- Roles of `type='System'` cannot be deleted (`roleDelete`). Delete is also blocked if the role is still referenced by
  any table (checked via `App\tableList::getTableList('role_id', id)`).

### 1.4 Role membership per user type
- **Students** → `role_id = 2`, linked to `sm_students` (see §5).
- **Parents/Guardians** → `role_id = 3`, linked to `sm_parents`.
- **Everyone else (staff)** → a staff row in `sm_staffs` whose `role_id` is 1,4,5,6,7,8,9 or any custom role
  (see §4). `SmStaff` also has `previous_role_id` (a staff can be demoted/kept and still be found by
  `scopeWhereRole` / `scopeWhereTeacher` which match `role_id OR previous_role_id`).

---

## 2. Login, guards, redirects & the super-admin concept

### 2.1 Guard
Single stateful web guard. `config/auth.php`: default guard `web` (session driver, `users` provider); `api` guard uses
Laravel Passport. Everything role-related runs through guard `web`.

### 2.2 Login flow — `App\Http\Controllers\Auth\LoginController@login`
Route: `POST /login` (`c:\reference-source\routes\tenant.php`). Controller uses `guest` middleware (except logout).
Login accepts **email OR username OR phone_number** as the identifier (`credentials()` switches on
`filter_var(..., FILTER_VALIDATE_EMAIL)`; the `login()` body also probes `username`, then `phone_number`, then `email`,
each scoped to the resolved `school_id`).

Key branches:
- Users are looked up by email across schools. **Single match** → validate `school active_status`, `Hash::check`
  password, then `Auth::attempt`. **Multiple matches** (same email in >1 school) → build per-school secret-login links
  and, if exactly one password matches, redirect to that school's `school-secret-login`.
- Multi-tenant hand-off: when a user belongs to a different sub-domain/school, login redirects to
  `//{domain}.{short_url}/school-secret-login?code=<encrypted>` where the code is
  `encrypt("DevelopedBySpondonit-{email}-{password}")`. `secretLogin()` (`GET school-secret-login`,
  route name `scl.secret-login`) decrypts and calls `Auth::attempt` by username or email.
- Blocking checks after auth: reject if `school.active_status` false, or if `Auth::user()->access_status` /
  `active_status` is falsy → logout + error "You are not allowed".
- On success it primes many session keys (`generalSetting`, `all_module`, `academic_years`, `sessionId`, `session`,
  `role_id`, style, date format, email_template), writes an `SmUserLog` row (user_id, role_id, school_id, ip,
  academic_id, user_agent), calls `userStatusChange(id, 1)` (online flag), optionally triggers 2FA, then
  `sendLoginResponse` → `redirect()->intended('/after-login')`.
- **`session(['role_id' => Auth::user()->role_id])`** is set on login — several middleware read the *session*
  `role_id`, not the DB column.

### 2.3 Post-login redirect — `HomeController@dashboard` (`GET /after-login`)
Reads `role_id = Auth::user()->role_id` and redirects (`c:\reference-source\app\Http\Controllers\HomeController.php` ~line 33):

| Condition | Redirect |
|-----------|----------|
| `role_id==1 && is_administrator=='yes' && Saas module active` | `superadmin-dashboard` |
| `is_administrator=='yes' && Saas active && SaasHr active` | `superadmin-dashboard` |
| `role_id==2` (Student) | `student-dashboard` (unless blocked by due-fees, then logout) |
| `role_id==3` (Parent)  | `parent-dashboard`  (unless blocked by due-fees, then logout) |
| `role_id==GlobalVariable::isAlumni()` | `alumni-dashboard` |
| `role_id==""` | `login` |
| `Auth::user()->is_saas==1` | `saasStaffDashboard` |
| otherwise (admin/teacher/staff) | `admin-dashboard` |

Due-fees gate: if `generalSetting()->due_fees_login==1` and a `DueFeesLoginPrevent` row exists for the user, students
and parents are logged out at dashboard time with an "unpaid fees" message.

### 2.4 Super-admin concept
"Super admin" = **`role_id == 1` AND `users.is_administrator == 'yes'`** (string `'yes'`, `is_administrator` is a
fillable column on `users`). This pairing:
- Bypasses ALL permission checks. `userPermission()` helper returns `true` immediately when
  `role_id==1 && is_administrator=='yes'` (`c:\reference-source\app\Helpers\Helper.php` ~line 430).
- `UserRolePermission` middleware only enforces the permission array when `Auth::user()->role_id !== 1` — role 1 is
  always allowed (`c:\reference-source\app\Http\Middleware\UserRolePermission.php`).

---

## 3. Permission model & the actual check mechanism

### 3.1 Two generations of tables (both present)
**Legacy (older) generation** — largely dormant in current checks:
- `infix_module_infos` (`InfixModuleInfo`): module tree. Columns incl. `module_id, module_name, parent_id, name,
  route, parent_route, type (1=module,2=module link,3=links crud), active_status, school_id`. Self-referential via
  `parent_route -> route` (`subModule()`, `children()`).
- `infix_permission_assigns` (`InfixPermissionAssign`): maps `module_id` ↔ `role_id` (per school). `User::permissions()`
  and `Role::permissions()` still point here but the live gate uses the new tables.
- Legacy `App\SmModule*` models (`SmModule`, `SmModuleLink`, `SmModulePermission`, `SmModulePermissionAssign`) and
  `App\SmRolePermission` (→ `SmModuleLink`) exist but are superseded.

**Current (active) generation** — this is what actually gates access:
- **`permissions`** table (`Modules\RolePermission\Entities\Permission`). Created by
  `2023_03_26_035701_create_permissions_table.php`, seeded from
  `resources/var/permission/*.php` (admin, student, parent, section-sidebar lists) via `storePermissionData()`.
  Important columns: `id, old_id, module, sidebar_menu, parent_id, name, **route**, **parent_route**,
  type(1 menu/2 submenu/3 action), lang_name, icon, svg, status, menu_status, position, is_saas,
  **is_admin, is_teacher, is_student, is_parent**, permission_section, role_id, school_id`.
  The **`route` string is the permission key** (it equals the named route it protects).
- **`assign_permissions`** table (`AssignPermission`, `protected $table='assign_permissions'`). Join of
  `permission_id` ↔ `role_id` ↔ `school_id`. Created/seeded by `2023_03_26_043548_create_assign_permissions_table.php`
  which hard-codes default `old_id` lists per role: Admin→role 5, Teacher→role 4, Accountant→6, Receptionist→7,
  Librarian→8, Driver→9, Student→role 2, Parent→role 3.

`Permission` boot clears caches (`PermissionList_`, `RoleList_`, `oldPermissionSync`, etc. keyed by `SaasDomain()`) on
create/update.

### 3.2 How a role's permission set is resolved — the `permission` singleton
`AppServiceProvider::register` binds a `permission` singleton (`c:\reference-source\app\Providers\AppServiceProvider.php`
~line 129):
```
$permissionIds = AssignPermission::where('role_id', Auth::user()->role_id)
    ->when(role is not saas, fn => where('school_id', Auth::user()->school_id))
    ->pluck('permission_id');
return Permission::whereIn('id', $permissionIds)->pluck('route')->toArray();
```
So `app('permission')` = **a flat array of allowed `route` strings for the current user's role (and school)**.

### 3.3 The route-level gate — `userRolePermission` middleware
Registered in `Kernel` as `userRolePermission => App\Http\Middleware\UserRolePermission`. Applied **per route with the
protected route name as an argument**, e.g.
`Route::get('class', ...)->middleware('userRolePermission:class')`
(`c:\reference-source\routes\admin_tenant.php` and many module route files). Logic:
1. If not authenticated on guard `web` → redirect `login`.
2. Special parent due-fees lock: `role_id==3` with cached `have_due_fees_{id}` list → `abort(403)` unless on an
   allowed fees route.
3. `hasPermission($route)` — plan/menu-level check (SaaS plan gating via `planPermissions('menus')` +
   `isMenuAllowToShow`); returns true if not plan-restricted.
4. Core check: if `role_id !== 1` and a permission array exists → allow only if `in_array($route, $permissions)`,
   else `abort(403)`. **role_id 1 bypasses entirely.**

### 3.4 The in-view / in-controller gate — `userPermission()` helper
`userPermission($route)` (`c:\reference-source\app\Helpers\Helper.php` ~line 424) is used to show/hide menu items & guard
controller actions:
- returns `true` if `role_id==1 && is_administrator=='yes'`;
- else if permission array non-empty and `role_id!=1` → `in_array($route, app('permission'))`;
- else (Saas active) → hide only routes in disabled saas modules;
- else `true`.

### 3.5 Assigning permissions (admin UI)
`RolePermissionController::assignPermission($id)` builds the permission tree filtered by role type
(`is_student` for role 2, `is_parent` for role 3, otherwise `is_admin`), then `rolePermissionAssign()`:
- deletes existing `assign_permissions` rows for `(school_id, role_id)`,
- inserts one row per selected `permission_id`.
Changes take effect on next request (the `permission` singleton re-queries; user may need to re-login for cache).

### 3.6 Role-type flags drive which permissions apply
`permissions.is_admin / is_teacher / is_student / is_parent` decide which permission rows belong to which audience.
`Permission::scopeRoleWise` and `subModule()`/`childs()` branch on `auth()->user()->role_id` (or the session key
`role_permission_user_type`) — role 2 → student rows, role 3 → parent rows, alumni constant → student rows, everyone
else → admin/teacher rows.

### 3.7 Module on/off gating
`ModulePermissionMiddleware` (alias `module`) allows a request if `school_id==1` or `isModuleForSchool($module)`, else
redirects to dashboard with "Module Not Active". `scopeWhereNotInDeaActiveModulePermission` on Permission/InfixModuleInfo
strips permissions belonging to deactivated purchased modules (`InfixModuleManager` + `moduleStatusCheck`).

---

## 4. Staff ↔ User relationship

- Staff detail lives in **`sm_staffs`** (`App\SmStaff`, guarded `id`, global scope `ActiveStatusSchoolScope`).
- **Link is `sm_staffs.user_id` → `users.id`.** `SmStaff::staff_user()` = `belongsTo(User, 'user_id','id')`;
  reverse `User::staff()` = `belongsTo(SmStaff::class, 'id', 'user_id')` (note the "inverse" arg order used to match
  `users.id == sm_staffs.user_id`), loaded without global scopes.
- A staff row carries its own `role_id` (and `previous_role_id`) mirroring the linked user's role; `SmStaff::roles()`
  = `belongsTo(InfixRole)`. Teachers are staff with `role_id==4` (`scopeWhereTeacher`).
- So: **staff is a profile/HR extension of a `users` row.** Every staff member is a user (role 1,4,5,6,7,8,9 or custom);
  not every user is staff (students/parents are not in `sm_staffs`).
- `User::getProfileAttribute()` picks the avatar source by role: role 2 → student photo, role 3 → parent photo,
  otherwise → staff photo — confirming the 3-way split students/parents/staff.

---

## 5. Parent ↔ Student linking

- **Students**: `sm_students` (`App\SmStudent`). Link to auth via `sm_students.user_id → users.id`
  (`SmStudent` has `user_id` cast + `belongsTo(User,'user_id','id')`). Students are `role_id==2`.
- **Parents/Guardians**: `sm_parents` (`App\SmParent`). Link to auth via `sm_parents.user_id → users.id`
  (`SmParent` line ~61 `belongsTo(User,'user_id','id')`). Parents are `role_id==3`.
- **The link is `sm_students.parent_id → sm_parents.id`:**
  - `SmStudent::parents()` = `belongsTo(SmParent::class, 'parent_id', 'id')` (with `parent_user`, no SchoolScope).
  - `SmParent::students()` (SmParent ~line 66) = `hasMany(SmStudent::class, 'parent_id', 'id')->where('active_status',1)`.
- A parent therefore can have many students (children); a student points to one guardian row.
- Guardian visibility of role 3 in the roles list is gated by `generalSetting()->with_guardian == 1`.
- `User` convenience relations mirror this: `User::student()`, `User::parent()`, `User::staff()`.

---

## 6. Middleware map (for replication)

| Alias / class | Purpose |
|---------------|---------|
| `auth` (`Authenticate`) | standard Laravel auth |
| `guest` (`RedirectIfAuthenticated`) | login page only for guests |
| `CheckDashboardMiddleware` | wraps admin routes; redirects role 2→student, 3→parent, alumni→alumni dash; reads **session `role_id`** |
| `StudentMiddleware` | allow only session `role_id==2`; alumni→alumni-dashboard; other roles→dashboard |
| `ParentMiddleware` | allow only session `role_id==3` |
| `AlumniMiddleware` | allow only session `role_id==isAlumni()` |
| `CheckUserMiddleware` | if a `role_id` session exists → force to dashboard |
| `userRolePermission:<routeName>` | **the core per-route RBAC gate** (see §3.3) |
| `module:<Module>` | module-active gate |
| `2fa` (`TwoFactorMiddleware`) | optional two-factor step |
| `fees_due_check` (`FeesDueCheckMiddleware`) | due-fees lockout |

Note the split brain: dashboard/section middleware read **`Session::get('role_id')`**, while the permission gate and
helpers read **`Auth::user()->role_id`**. Both are set at login and must stay in sync.

---

## 7. Replication checklist (essentials)
1. Single `users` table + integer `role_id`; keep the fixed system role IDs 1-9 and the Alumni magic id `200000106`.
2. `is_administrator='yes'` + `role_id=1` = super admin → bypass all permission checks.
3. `permissions.route` string is the permission key; `assign_permissions(permission_id, role_id, school_id)` is the
   grant table; the effective permission set = allowed `route` strings for the role/school.
4. Protect each route via a per-route middleware arg equal to that route's permission key; role 1 bypasses.
5. Multi-school (`school_id`) scoping on roles, permissions, and grants (except `is_saas`/system rows and school_id==1).
6. Profiles: staff→`sm_staffs.user_id`, student→`sm_students.user_id`, parent→`sm_parents.user_id`;
   student↔parent via `sm_students.parent_id`.
7. Role-type flags `is_admin/is_teacher/is_student/is_parent` on permission rows decide audience.
