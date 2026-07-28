# Admin Dashboard — Design & Data Spec

> The reference system's admin dashboard shows count tiles, an income/expense
> chart, a notice board, an events calendar and a to-do list. EasySchool rebuilds
> it as a modern, **live-data** dashboard driven by the actual modules, with
> dependency-free charts.

## What it shows

| Widget | Data source |
|---|---|
| Greeting + quick actions | time-of-day; links to New Admission / Collect Fee / Add Notice / New Enquiry |
| KPI cards | live counts: students (live records), teachers, staff, guardians, classes |
| Income vs Expense (6-month bar chart) + This-month income, Fees collected, Bank balance | Accounting `account_transactions`, Fees `fee_payments`, derived bank balances |
| Today's Attendance (ring + present/absent/late) | Attendance `student_attendances` for today (P/L/A/F) |
| Notice board | Communication `notices` (published, latest) |
| Upcoming events | Communication `events` (start_date ≥ today) |
| Recent activity | merged feed: latest admission enquiries, fee payments, complaints |
| My Tasks | personal `todos` (this module) — add / toggle / delete |

## Design

- Served at the site's `/dashboard` (the Dashboard module owns the route; the old
  `Route::view` placeholder was removed).
- A gradient hero (greeting + quick-action chips), a KPI card band, then a widget
  grid (finance+attendance, notices+events, activity+todos). Theme-aware
  (light/dark via the admin layout's CSS variables), responsive, RTL.
- **Charts are dependency-free inline SVG** (grouped income/expense bars, gradient
  fills, legend) — no Chart.js/CDN, no build step. The attendance ring is a pure
  CSS `conic-gradient`.

## Aggregation (`DashboardService`)

`kpis()` · `finance()` (6-month series + month totals + derived bank balance via
`AccountingService`) · `attendanceToday()` (rate = `(P + L + 0.5·F)/marked`) ·
`notices()` · `upcomingEvents()` · `recentActivity()` (merged, time-sorted). Every
read degrades gracefully — an empty module yields zeros, not errors.

## The `todos` table

One tiny table (`user_id`, `title`, `is_done`, `position`) powers the personal
to-do widget; each user only sees and mutates their own (owner-guarded).

## Divergence from the reference (deliberate)

| Reference | EasySchool | Why |
|---|---|---|
| Chart.js / Morris via bundled assets | Inline SVG + CSS `conic-gradient` | Zero dependencies, no build step, CSP-safe. |
| Dummy/placeholder tiles | Live cross-module aggregates | The dashboard reflects real state. |
| Fixed theme | Theme-aware (light/dark) + RTL, responsive | Consistent with the app. |
