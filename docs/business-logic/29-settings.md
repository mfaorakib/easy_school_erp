# System Settings Hub — Business-Logic Spec

> The reference system's largest menu area is System Settings (16+ items): general,
> email, SMS, currency, payment methods, module/login permission, localization,
> holidays, appearance, language, backup. EasySchool gathers the configuration into
> one **Settings** module over a shared key/value store.

## Entities & tables

| # | Table | Model | Purpose |
|---|---|---|---|
| — | `settings` (Foundation's, **reused**) | `Foundation\Setting` | Generic key/value config, grouped (general / localization / email / sms / appearance). `type` column (string/bool/int/json) drives casting. |
| 1 | `holidays` | `Holiday` | A school holiday or range (year-scoped). |
| 2 | `payment_methods` | `PaymentMethod` | The payment methods offered across fees/accounting. |

The Settings module deliberately **reuses Foundation's existing `settings` table**
rather than creating a second one (a naming collision surfaced this — one store is
correct). `SettingsService` is the read/write API over it.

## Setting groups (each a form screen)

| Group | Keys |
|---|---|
| **general** | school_name, address, phone, email, currency_code, currency_symbol |
| **localization** | timezone, date_format, default_language, weekend_days (JSON array) |
| **email** | mail_host, mail_port, mail_username, mail_password, mail_encryption, mail_from_address, mail_from_name |
| **sms** | sms_provider, sms_api_key, sms_sender_id |
| **appearance** | admin_primary_color, admin_theme |

Plus two CRUD screens: **Holidays** and **Payment Methods**.

## Business rules

- **One key/value store.** `SettingsService::group($g)` returns a group's
  key→typed-value map; `setMany($pairs, $group)` upserts, storing arrays as
  `type=json` (e.g. `weekend_days`). Reads cast by the stored `type`.
- Each settings screen edits exactly one group; unrelated groups are untouched.
- **Holidays** are academic-year-scoped, validated `to_date ≥ from_date`, with an
  inclusive `days()` count.
- **Payment methods** are a manageable, ordered, toggleable list (the string
  `payment_method` used by Fees/Accounting can be sourced from here).
- Settings persist configuration; wiring them into live mail/SMS transport is a
  later concern — the store is ready for it.

## Divergence from the reference (deliberate)

| Reference | EasySchool | Why |
|---|---|---|
| Dozens of dedicated settings tables/controllers | One key/value `settings` store + grouped forms | Add a setting = add a key, not a migration. |
| Currency/payment-method/timezone each a table | key/value (currency, tz) + one `payment_methods` table | Only the list-like concern gets a table. |
| Weekends as fixed columns | `weekend_days` JSON | Any combination without schema change. |

## Service surface (`SettingsService`)

`group(g)` · `get(key, default)` · `getArray(key)` · `setMany(pairs, group)` ·
`timezones()` · `dateFormats()` · `weekDays()`.
