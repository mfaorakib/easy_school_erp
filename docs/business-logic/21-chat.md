# Chat / Messaging — Business-Logic Spec

> The reference Chat module is a large real-time (Pusher) messenger with 1-to-1 conversations,
> groups (including academic class/section/subject groups), per-recipient read receipts, block,
> invitations, stories/status and forwarding. EasySchool rebuilds the **core, server-rendered**
> messenger faithfully and drops the real-time transport and the peripheral features (stories,
> invitations, forwarding) which aren't needed for a single school.

## Entities & tables

| # | Table | Model | Grain / purpose |
|---|---|---|---|
| 1 | `chat_messages` | `ChatMessage` | A direct 1-to-1 message. `sender_id`, `recipient_id`, `body`, `message_type` (text/file), `attachment_path/name`, `reply_to_id`, `read_at`. **Not** year-scoped. |
| 2 | `chat_groups` | `ChatGroup` | A group. `name`, `description`, `type` (open/admin_only), `created_by`, optional `class_id/section_id/subject_id` (academic group). Year-scoped. |
| 3 | `chat_group_members` | `ChatGroupMember` | Membership. `role` (admin/member), `last_read_at`. Unique per (group, user). |
| 4 | `chat_group_messages` | `ChatGroupMessage` | A message posted to a group. |
| 5 | `chat_blocks` | `ChatBlock` | `blocker_id` → `blocked_id`. |

## Business rules

**Direct messaging**
- A message needs a body **or** an attachment. Attachments store on the `public` disk under `chat/`.
- Read state is `read_at` on the message; opening a conversation marks all incoming messages read.
- Thread list = distinct conversation partners, each with the last message + this user's unread count, newest-activity first.

**Blocking**
- A block in **either** direction stops direct messaging between the two users (`isBlocked` is symmetric). `sendDirect` throws a validation error when blocked, and you can't message yourself.

**Groups**
- `open` → any member may post; `admin_only` → only members with role `admin` may post (`canPost`).
- The **creator** is automatically an admin. Admins manage membership (add/remove, promote/demote).
- Group read tracking is **per-member** via `last_read_at` (not a row per member per message like the
  reference's `group_message_recipients`): unread = group messages after the member's `last_read_at`
  that they didn't send. Posting marks the sender read.
- Academic groups optionally carry class/section/subject so a class group is a first-class concept.

## Divergence from the reference (deliberate)

| Reference | EasySchool | Why |
|---|---|---|
| Real-time via Pusher / echo | Server-rendered pages (+ optional polling) | No realtime infra dependency for a single school; faithful data model, simpler transport. |
| `chat_group_message_recipients` — one row per (member, message) for read receipts | `chat_group_members.last_read_at` | Read state derives from one timestamp; O(members) not O(members × messages) rows. |
| UUID group ids, `message_type` int codes (0–4), soft-deletes, forward/initial flags, stories/status, invitations | bigint ids, `message_type` string (text/file), block + reply kept; peripheral features dropped | Keeps the messaging core faithful; drops what a single-school deploy doesn't use. |
| Mutable per-message `status` int | `read_at` timestamp (direct) + derived group unread | Timestamps carry more info and can't drift out of a small enum. |

## Service surface (`ChatService`)

Direct: `sendDirect`, `conversation`, `markConversationRead`, `directThreads`, `directUnreadTotal`.
Block: `isBlocked`, `block`, `unblock`, `blockedByMe`.
Group: `createGroup`, `addMember`, `removeMember`, `setRole`, `membership`, `isMember`, `canPost`,
`sendGroup`, `groupMessages`, `markGroupRead`, `groupUnread`, `userGroups`.
