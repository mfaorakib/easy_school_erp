# 11 - Transport Business Logic

Documents how the reference system models school transport: routes, vehicles,
which vehicles serve which route, and how a student is linked to transport.
Ends with a clean single-school rebuild recommendation.

---

## 1. Entities & Tables

### 1.1 Route (`sm_routes`)

A named transport route with a single fare.

| Column | Type | Notes |
|---|---|---|
| `id` | int unsigned PK | |
| `title` | varchar(200) | Route name/label (e.g. "North Line") |
| `far` | float | Route **fare** (misspelled "far"), the per-student transport charge |
| `active_status` | tinyint (default 1) | Soft on/off flag |
| `created_at` / `updated_at` | timestamp | |
| `created_by` / `updated_by` | int unsigned (default 1) | Audit user |
| `school_id` | int unsigned (default 1) | Multi-tenant scope |
| `academic_id` | int unsigned (default 1) | Academic-year scope |

- Model `App\SmRoute` casts `title` string, `far` float.
- Applies a global scope `ActiveStatusSchoolScope` (auto-filters by active status + school).
- **Scope: school-scoped AND academic-year-scoped.**

### 1.2 Vehicle (`sm_vehicles`)

A physical vehicle. Driver details are NOT stored inline — the vehicle points at
a staff record via `driver_id`.

| Column | Type | Notes |
|---|---|---|
| `id` | int unsigned PK | |
| `vehicle_no` | varchar(255) | Registration / vehicle number |
| `vehicle_model` | varchar(255) | Model description |
| `made_year` | int nullable | Manufacture year |
| `note` | text nullable | Free note |
| `active_status` | tinyint (default 1) | |
| `driver_id` | int unsigned nullable | FK -> `sm_staffs.id` (staff with driver role) |
| `created_at` / `updated_at` | timestamp | |
| `created_by` / `updated_by` | int unsigned | Audit |
| `school_id` | int unsigned (default 1) | Multi-tenant scope |
| `academic_id` | int unsigned (default 1) | Academic-year scope |

- Model `App\SmVehicle`: `driver()` = `belongsTo(SmStaff, 'driver_id')`.
- **There is NO capacity column, and NO inline driver name / phone / license.**
  The driver is a staff member; his name/phone/license come from the staff record.
  In `SmVehicleController`, the driver dropdown is `SmStaff::whereRole(9)->get()`
  (role 9 = Driver), and only `driver_id` is saved on the vehicle.
- The `ActiveStatusSchoolScope` global scope is present but **commented out** on this
  model; queries instead filter `school_id` manually in the controllers.
- **Scope: school-scoped AND academic-year-scoped** (columns exist and are set on save).

### 1.3 Assign-Vehicle (`sm_assign_vehicles`)

Maps a route to the set of vehicles that serve it. IMPORTANT: this is NOT a normalized
pivot — one row per route, with the vehicle IDs stored as a **comma-separated string**.

| Column | Type | Notes |
|---|---|---|
| `id` | int unsigned PK | |
| `active_status` | tinyint (default 1) | |
| `vehicle_id` | int unsigned nullable | **Overloaded: stores a CSV string of vehicle IDs** (e.g. "3,7,12") |
| `route_id` | int unsigned nullable | FK -> `sm_routes.id` |
| `created_at` / `updated_at` | timestamp | |
| `created_by` / `updated_by` | int unsigned | Audit |
| `school_id` | int unsigned (default 1) | Multi-tenant scope |
| `academic_id` | int unsigned (default 1) | Academic-year scope |

- Model `App\SmAssignVehicle`: `route()` = `belongsTo(SmRoute,'route_id')`,
  `vehicle()` = `belongsTo(SmVehicle,'vehicle_id')` (single-value relation, does not
  reflect the CSV reality).
- **Scope: school-scoped AND academic-year-scoped.**

---

## 2. How a Vehicle is Assigned to a Route

- The relationship is **one route -> many vehicles**.
- It is stored **denormalized**: `SmAssignVehicleController::store()` takes the selected
  `route` and an array of `vehicles`, concatenates the vehicle IDs into a single
  comma-separated string, and writes that string into `sm_assign_vehicles.vehicle_id`
  as a single row keyed by `route_id`.
- On edit, the CSV is split with `explode(',', $assign_vehicle->vehicle_id)` to
  re-populate the multi-select.
- Consequences of this design: no real referential integrity on the vehicle side,
  no per-assignment metadata, and a vehicle appearing on two routes is just its ID
  present in two CSV strings. A vehicle is effectively unique-per-route only by convention.

---

## 3. How a Student is Assigned Transport (Route + Vehicle)

- Stored **directly on the student row** (`sm_students`), NOT in a separate table:
  - `route_list_id` int unsigned nullable -> FK `sm_routes.id` `ON DELETE SET NULL`
  - `vechile_id` int unsigned nullable (note the misspelling) -> FK `sm_vehicles.id` `ON DELETE SET NULL`
- Set during student admission / edit. Example (parent admission flow):
  `$student->route_list_id = $request->route;` and `$student->vechile_id = $request->vehicle;`
- Model `App\SmStudent`:
  - `route()` = `belongsTo(SmRoute, 'route_list_id')`
  - `vehicle()` = `belongsTo(SmVehicle, 'vechile_id', ...)`
- Because the assignment is two plain columns on the student, each student has exactly
  **one current route + one current vehicle**. There is no historical per-year transport
  record; the `sm_students` row carries academic scoping generally, but the transport
  columns simply hold the current value (they are overwritten, not versioned).
- `SmTransportController::studentTransportReportSearch()` treats these columns as the
  source of truth, filtering students by `route_list_id` and `vechile_id`.

**Assignment scoping summary:** route and vehicle definitions and the route<->vehicle
map are school- and academic-year-scoped; the student's transport link is a mutable
current value on the student row (not a historical, per-year record).

---

## 4. Route Fare & Fees (brief)

- The fare lives on the route itself: `sm_routes.far` (float). Each route = one flat fare.
- This fare is what feeds transport fee charges downstream (a student on a route incurs
  that route's fare). Fee collection mechanics are out of scope here — see the fees
  business-logic doc. The only transport-side fact is: **fare is a per-route amount stored
  on `sm_routes.far`.**

---

## 5. Clean Single-School Rebuild Recommendation

Drop the multi-tenant `school_id` everywhere (single school). Normalize the route<->vehicle
mapping into a real pivot. Keep academic-year scoping where it matters (on the student
assignment, so transport can change year to year and be reported historically). Fix the
`vechile_id` misspelling. Keep driver info as a reference to staff.

### `transport_routes`
| Column | Type | Notes |
|---|---|---|
| `id` | PK | |
| `name` | varchar | (was `title`) |
| `fare` | decimal(10,2) | (was `far`; use decimal, not float, for money) |
| `is_active` | boolean | (was `active_status`) |
| `created_by` / `updated_by`, timestamps | | audit |

### `vehicles`
| Column | Type | Notes |
|---|---|---|
| `id` | PK | |
| `vehicle_no` | varchar | |
| `vehicle_model` | varchar | |
| `made_year` | int nullable | |
| `capacity` | int nullable | **NEW** — the reference had none; add it for seating limits |
| `driver_id` | FK -> staff/`users` | keep driver as a staff reference |
| `note` | text nullable | |
| `is_active` | boolean | |
| timestamps, audit | | |

### `route_vehicle` (pivot — replaces `sm_assign_vehicles` CSV)
| Column | Type | Notes |
|---|---|---|
| `id` | PK | |
| `route_id` | FK -> `transport_routes.id` | |
| `vehicle_id` | FK -> `vehicles.id` | |
| unique(`route_id`,`vehicle_id`) | | prevents duplicates; real referential integrity |

One row per (route, vehicle) pair. A route has many vehicles; a vehicle can serve many
routes. This replaces the comma-separated `vehicle_id` string entirely.

### `student_transports` (replaces the two columns on the student row)
| Column | Type | Notes |
|---|---|---|
| `id` | PK | |
| `student_id` | FK -> `students.id` | |
| `route_id` | FK -> `transport_routes.id` `ON DELETE SET NULL` | |
| `vehicle_id` | FK -> `vehicles.id` `ON DELETE SET NULL` | (fixes `vechile_id` typo) |
| `academic_year_id` | FK | **keep academic-year scoping here** so assignment is per-year/historical |
| `fare_snapshot` | decimal(10,2) nullable | optional: capture route fare at assignment time for fee stability |
| unique(`student_id`,`academic_year_id`) | | one transport assignment per student per year |
| timestamps, audit | | |

Notes:
- Drop `school_id` from all four tables.
- Move student transport off the `sm_students` row into `student_transports` so history
  is preserved across years and the student table stays lean.
- Enforce that `vehicle_id` on a student assignment is a vehicle actually mapped to the
  chosen `route_id` (validate against `route_vehicle`).
- Keep route fare on `transport_routes.fare`; let the fees module read it (optionally
  snapshot it per assignment).

---

## Summary — Assignment Rules (5 lines)

1. A route (`sm_routes`: title + `far` fare) and a vehicle (`sm_vehicles`: vehicle_no,
   model, made_year, `driver_id`->staff; no capacity, no inline driver fields) are both
   school- and academic-year-scoped.
2. Vehicles are assigned to a route in `sm_assign_vehicles` as ONE row per route whose
   `vehicle_id` column holds a COMMA-SEPARATED list of vehicle IDs (denormalized, not a pivot).
3. A student's transport is stored directly on the student row via `route_list_id`
   (-> route) and `vechile_id` (-> vehicle, misspelled), each FK `ON DELETE SET NULL`,
   set at admission/edit as a single current value.
4. Route fare is the flat per-route amount `sm_routes.far`, which feeds transport fees.
5. Rebuild: `transport_routes`, `vehicles`, a real `route_vehicle` pivot, and
   `student_transports` (academic-year-scoped, typo fixed); drop `school_id` everywhere.
