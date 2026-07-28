# Fee Payments, Discounts & Guardian Portal — Business-Logic Spec

> Multi-channel fee payment (Stripe, bKash, Nagad, or manual cash/bank/mobile
> banking) plus fee discounts/waivers/scholarships that school administration
> can actually apply — and the guardian-facing portal that makes online
> payment possible in the first place.

## Why a Guardian Portal was required

Before this phase, **no guardian/parent-facing surface existed at all** — every
role landed on the same whole-school admin dashboard, with no route
differentiation. Online payment can't work without somewhere for a guardian
to click "Pay Now," so a minimal, guardian-scoped portal was a prerequisite,
not scope creep. It deliberately does **not** duplicate the full future
"Student Portal" (attendance/homework/etc.) — just fees.

## Entities & tables

| # | Table | Model | Purpose |
|---|---|---|---|
| 1 | `payment_methods` (extended) | `Settings\PaymentMethod` | Now has `driver` (manual\|stripe\|bkash\|nagad) + `config` (encrypted JSON: API keys/sandbox flag). Manual = cash/bank/any unintegrated channel; the other three drive a real online checkout. |
| 2 | `fee_assignment_discount` (extended) | pivot on `FeeAssignment::discounts()` | Now carries `reason`, `applied_by`, `applied_at` — turns a bare pivot into an auditable adjustment record. **This is the piece that was previously completely missing**: `FeeDiscount` existed but nothing ever attached one to a student. |
| 3 | `fee_payment_intents` | `Fees\FeePaymentIntent` | One tracked online-checkout attempt: pending → completed/failed. Created the moment a guardian clicks "Pay Now"; resolves via gateway return or manual staff confirmation. |
| 4 | `fee_payments` (extended) | `Fees\FeePayment` | Now has `receipt_no` (admin-configurable format, same `IdPattern` helper used for admission unique IDs). |
| 5 | `students` (extended, from Admission phase) | — | `unique_id` already existed; unrelated to this phase but the receipt/ID-format pattern is shared infrastructure. |

## Business rules

- **One collection path for everyone.** Whether a cashier types in a cash
  payment or a guardian pays online, both end up calling
  `FeeService::collect()` — no parallel/duplicate payment-recording logic.
  `FeePaymentIntentService::complete()` is the only bridge between an online
  attempt and a real `FeePayment`.
- **Manual channels need human confirmation.** Cash/Bank/Rocket (any
  `driver=manual` method) never auto-completes: the guardian submits a
  transaction reference, the intent stays `pending`, and a staff member
  reviews + confirms it at `/fees/intents` (`FeePaymentIntentService::complete()`
  or `::fail()`).
- **Real gateways auto-complete on return.** Stripe/bKash/Nagad each
  implement `PaymentGatewayDriver::checkout()`/`verify()`
  (`Modules\Fees\Services\Gateways\Drivers\*`); the driver is read-only —
  `FeePaymentIntentService::resolveReturn()` is the single place that turns a
  verify() result into a state change, so completion logic never gets
  duplicated per-gateway.
- **Discounts/adjustments now actually apply.** `FeeService::attachDiscount()`
  requires a `reason` and records the approving user — re-attaching the same
  discount updates the reason rather than duplicating the row.
  `FeeDiscount` covers both reusable types (Sibling %, Merit) and one-off
  named adjustments (staff creates a specifically-named discount for a single
  situation).
- **Receipts are formatted, not raw ids.** `FeeService::generateReceiptNo()`
  reads the `fees.receipt_format` setting (default `RCT-{YYYY}-{SEQ:5}`) —
  same `Foundation\Support\IdPattern` templating used for admission IDs.
- **Guardian data access is hard-scoped.** `GuardianPortalService::assertOwnsChild()`
  is the one gate every student-specific portal action must pass through — it
  403s if the student isn't this guardian's own child. The `/portal/*` route
  group is additionally gated by Spatie's built-in `role:parent` middleware
  (registered in `bootstrap/app.php`), so a non-parent account can't reach the
  portal even by guessing a URL. Verified in testing: a plain role-mismatch
  gets a 403 at the middleware layer; a cross-family student id gets a 403 at
  the service layer.
- **Login routes guardians to their portal automatically** —
  `Access\LoginController` checks `hasRole(Role::Parents)` post-login and
  redirects to `/portal` instead of `/dashboard`; every other role is
  unaffected.
- **Gateway failures degrade gracefully.** A misconfigured/placeholder
  gateway (e.g. demo Stripe keys) never crashes the guardian's browser —
  `PortalPaymentController` catches driver exceptions and redirects back with
  a friendly message, logging the real error server-side.

## Payment gateway honesty note

Stripe's integration uses the official `stripe/stripe-php` SDK and is
genuinely functional once real (or Stripe test-mode) keys are entered — this
was verified to correctly attempt a live API call and fail with
`AuthenticationException: Invalid API Key` against the seeded placeholder
key, confirming the code path is real, not a stub. bKash and Nagad were built
against their **publicly documented** sandbox contracts (grant/create/execute
for bKash; init/complete/verify with RSA signing for Nagad), researched via
web search rather than assumed from training data — but neither was run
against a live sandbox from this environment, so a school should verify
against the current official integration guide before going live. Rocket has
no widely-available public sandbox, so it ships as a manual-reference
channel like Cash/Bank, behind the same `PaymentGatewayDriver` interface —
swapping in a real API later needs no changes outside one new driver class.

## Screens

**Admin:** Fee ledger (`/fees/collect`) — now with a real payment-method
dropdown + discount attach/detach with reason. Payment confirmations
(`/fees/intents`) — approve/reject pending manual payments. Payment receipt
(`/fees/payments/{id}/receipt`). Payment Methods settings — driver select +
per-gateway config fields.
**Guardian Portal** (`/portal`, role:parent only): Dashboard (children +
due/paid at a glance) → child fee detail + Pay Now → gateway checkout or
manual-reference page → payment history → receipt.

## Service surface

`FeeService` (extended): `attachDiscount()` · `detachDiscount()` ·
`generateReceiptNo()` · `collect()` (now returns the `FeePayment`).
`FeePaymentIntentService`: `start()` · `resolveReturn()` · `complete()` ·
`fail()`. `PaymentGatewayManager::driver()`. `GuardianPortalService`:
`children()` · `assertOwnsChild()` · `feeLedger()` · `paymentHistory()`.
