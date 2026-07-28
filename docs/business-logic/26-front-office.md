# Front Office — Business-Logic Spec

> The reference system's front desk is a set of independent logbooks under
> AdminSection / StudentInfo: admission query + follow-ups, visitor book, phone
> call log, postal dispatch/receive, and complaints (+ types). EasySchool gathers
> them into one **FrontOffice** module.

## Entities & tables (all academic-year scoped)

| # | Table | Model | Purpose |
|---|---|---|---|
| 1 | `admission_enquiries` | `AdmissionEnquiry` | A prospective-student lead: contact, interested class, source, assignee, `status` active/won/lost, next-follow-up date. |
| 2 | `enquiry_followups` | `EnquiryFollowup` | A dated follow-up contact on an enquiry (response + next date). |
| 3 | `visitors` | `Visitor` | Visitor logbook: who, to meet, in/out time. `out_time` null = still in. |
| 4 | `postal_records` | `PostalRecord` | Postal `dispatch` or `receive` (one typed table): title, party (to/from), ref no, attachment. |
| 5 | `complaint_types` | `ComplaintType` | Complaint categories. |
| 6 | `complaints` | `Complaint` | A logged complaint: type, complainant, assignee, `status` open/in_progress/resolved, action taken. |
| 7 | `phone_call_logs` | `PhoneCallLog` | Incoming/outgoing call log with follow-up date. |

## Business rules

- **Enquiry follow-ups thread.** Adding a follow-up records the contact **and rolls
  the enquiry's `next_follow_up_date` forward** to the follow-up's next date
  (`FrontOfficeService::addFollowup`) — so the enquiry list always shows the true
  next action. An enquiry moves active → won/lost as it converts.
- **Visitor check-out.** A visitor is "in premises" until `out_time` is stamped;
  `checkOut` records the exit time. The list distinguishes In vs Checked-out.
- **Postal is one typed table** for dispatch and receive; the index filters by type
  and the form preselects it. `party` holds the recipient (dispatch) or sender
  (receive).
- **Complaints have a lifecycle** open → in_progress → resolved, an optional
  assignee (staff) and an action-taken note.
- Every logbook is **academic-year scoped**, so a year's front-desk activity is
  isolated.

## Divergence from the reference (deliberate)

| Reference | EasySchool | Why |
|---|---|---|
| Separate `sm_postal_dispatches` + `sm_postal_receives` (near-identical) | One `postal_records` with a `type` | Half the tables/controllers; one list to filter. |
| Enquiry status as free integer codes | `status` enum active/won/lost | Explicit lead lifecycle. |
| Complaint status implicit | `status` enum open/in_progress/resolved | A clear, trackable lifecycle. |
| Five scattered controllers/areas | one FrontOffice module (6 features) | Cohesive front-desk home. |

## Service surface (`FrontOfficeService`)

`addFollowup(enquiry, data)` (also advances the enquiry's next-follow-up) ·
`checkOut(visitor, time?)`. Everything else is plain CRUD.
