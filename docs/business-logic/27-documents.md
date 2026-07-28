# Documents — ID Cards & Certificates — Business-Logic Spec

> The reference system prints ID cards, certificates, marksheets and fee invoices
> through `SmStudentIdCardController`, `SmStudentCertificate`, `BulkPrint` and
> invoice settings. EasySchool starts with the two most-used documents — **ID
> cards** and **certificates** — as designable, template-driven printables.

## Entities & tables (NOT year-scoped — templates are reusable config)

| # | Table | Model | Purpose |
|---|---|---|---|
| 1 | `id_card_templates` | `IdCardTemplate` | A designable card: holder type (student/staff), colours, logo/bg/signature images, which detail rows to print (`fields` JSON), footer, orientation. |
| 2 | `certificate_templates` | `CertificateTemplate` | A certificate: heading + HTML `body` with `{{placeholders}}`, header/background images, two signatures, orientation. |

No generated documents are stored — generation renders a **print-optimised HTML
page** on demand (browser Print → PDF). An issued-log can be added later.

## How generation works

1. Pick a **template**; the template's `holder_type` decides the recipient list
   (students filtered by class/section, or active staff).
2. Select recipients (checkboxes, select-all).
3. Generate opens a **print view in a new tab**:
   - **ID cards** → `DocumentService::idCardData(template, holder)` resolves each
     holder into `{name, subtitle, photo, id_label, id_no, rows}` where `rows`
     contains only the template's chosen `fields` that have a value. Rendered as a
     grid of styled cards (gradient band, circular photo, detail rows, signature,
     footer), one per holder, `page-break-inside: avoid`.
   - **Certificates** → `DocumentService::resolveCertificate(template, holder)`
     fills `{{name}} {{class}} {{section}} {{roll}} {{admission_no}} {{staff_no}}
     {{designation}} {{session}} {{date}} {{school_name}}` in the body. Rendered
     one full-page certificate per recipient (ornate frame, serif heading, dual
     signatures), `page-break-after: always`. A `general` certificate needs no
     recipient (placeholders that don't apply resolve to blank).

School name/logo come from the Builder `SiteSetting`; session from the current
academic year. Image values resolve via `DocumentService::media()` (upload path or
external URL). The theme is **print-first**: a screen toolbar (Print / Back) that
`@media print` hides.

## Divergence from the reference (deliberate)

| Reference | EasySchool | Why |
|---|---|---|
| Fixed card/certificate markup per school theme | **Template-driven** (colours, fields, images, placeholders) configurable in-app | Design without code; many templates. |
| Separate student vs staff ID controllers | One `IdCardTemplate.holder_type` + one flow | Half the surface. |
| Stored generated files | On-demand print render | No storage bloat; always current data. |

## Service surface (`DocumentService`)

`idCardData(template, holder)` · `placeholders()` · `resolveCertificate(template,
holder?)` · `media(path)` · `schoolName()` · `session()`.

> Note: a subtle bug was caught in build — building the card rows with an arrow
> function captured `$rows` by value (PHP arrow-fn semantics), so no rows appeared;
> fixed by an explicit `pickRows()` helper.
