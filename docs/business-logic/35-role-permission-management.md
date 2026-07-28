# Role & Permission Management — Business-Logic Spec

> Before this phase, `Modules\Access` had 9 hardcoded roles and **zero
> permissions** anywhere in the system — no admin UI to create a role, assign
> a permission, or change which role a user held. Any authenticated user
> could reach any `auth`-gated admin route regardless of role.

## Scope: management, not enforcement

This phase builds the **admin UI to define and assign** roles/permissions.
It deliberately does **not** wire permission checks onto the ~30 modules'
existing routes or filter the sidebar nav by permission — that is a
separate, later sweep (touches every module's `routes/web.php` +
`layouts/admin.blade.php`). Today, `auth` alone still gates most admin
routes; this phase makes the *vocabulary and assignment* exist so
enforcement has something real to hook into when it's built.

## Entities

No new tables — this phase uses `spatie/laravel-permission`'s existing
`roles`, `permissions`, `model_has_roles`, `role_has_permissions` tables
(already migrated, previously populated with only the 9 role rows and no
permissions at all).

| Component | Purpose |
|---|---|
| `Access\Support\PermissionRegistry` | The permission vocabulary: ~127 `{area}.{action}` keys grouped into ~30 areas (e.g. `leave.approve`, `fees.discounts.attach`, `builder.pages.edit`). Single source of truth for both the seeder and the matrix UI. |
| `Access\Services\RoleManagementService` | `createRole()` · `updatePermissions()` (full sync) · `deleteRole()` (aborts 422 for the 9 system roles) · `assignRoles()` (full sync on a user). |

## Business rules

- **Action-level granularity.** Chosen explicitly (over coarser module-level
  permissions) after the user picked it via a direct trade-off question: each
  functional area gets `view`/`create`/`edit`/`delete` as separate
  permissions plus a few genuinely distinct special actions (`leave.approve`,
  `fees.collect`, `documents.generate`, `admission.confirm`, etc.) — not a
  single `manage` catch-all per module.
- **The 9 fixed roles can be re-permissioned but never deleted or renamed** —
  `RoleManagementService::isSystemRole()`/`deleteRole()` enforce this at the
  service layer (verified: deleting `teacher` → 422; deleting a freshly
  created custom role → 302 success). Business logic elsewhere depends on
  these exact slugs existing (`Staff::scopeTeachers()`, the Guardian Portal's
  `role:parent` gate, `StudentAdmissionService`'s hardcoded role assignments).
- **Custom roles are fully supported** — admins can create a new role (e.g.
  "Exam Coordinator") with just a name, then assign it any subset of the 127
  permissions via the same matrix screen used for system roles.
- **Sensible starting defaults, not a blank slate.** `PermissionSeeder` gives
  every one of the 9 roles a curated starting permission set (super-admin =
  all 127; admin = all except `roles.create/edit/delete`, reserved for
  super-admin; teacher/accountant/receptionist/librarian/driver = a focused
  subset matching their real job; student/parent = minimal) — the new UI is
  exactly where a school fine-tunes these afterwards, not where they start
  from zero.
- **A user can hold multiple roles** — `assignRoles()` does a full
  `syncRoles()`, verified by assigning both `parent` and `accountant` to one
  user in one save.

## Screens

**Roles** (`/access/roles`): list (permission count + assigned-user count per
role, system roles marked and delete-protected) → create (name only) →
permission matrix (one card per area, checkboxes, select-all/clear-all).
**Users** (`/access/users`): searchable list (name/email/username, current
roles as badges) → edit (checkbox list of all roles, `syncRoles()` on save).

## Service surface

`RoleManagementService`: `createRole()` · `updatePermissions()` ·
`deleteRole()` · `isSystemRole()` · `assignRoles()`.
`PermissionRegistry`: `all()` (grouped) · `keys()` (flat list).
