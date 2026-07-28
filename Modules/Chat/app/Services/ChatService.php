<?php

namespace Modules\Chat\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Chat\Models\ChatBlock;
use Modules\Chat\Models\ChatGroup;
use Modules\Chat\Models\ChatGroupMember;
use Modules\Chat\Models\ChatGroupMessage;
use Modules\Chat\Models\ChatMessage;

/**
 * Chat engine: direct 1-to-1 messaging, group messaging, blocking and read
 * tracking. Direct read state lives on the message (read_at); group read state
 * lives per-member (chat_group_members.last_read_at) so unread is derived, not
 * duplicated per message.
 */
class ChatService
{
    // ------------------------------------------------------------- Blocking

    /** True if either user has blocked the other. */
    public function isBlocked(int $a, int $b): bool
    {
        return ChatBlock::where(fn ($q) => $q->where('blocker_id', $a)->where('blocked_id', $b))
            ->orWhere(fn ($q) => $q->where('blocker_id', $b)->where('blocked_id', $a))
            ->exists();
    }

    public function block(int $byId, int $toId): void
    {
        if ($byId !== $toId) {
            ChatBlock::firstOrCreate(['blocker_id' => $byId, 'blocked_id' => $toId]);
        }
    }

    public function unblock(int $byId, int $toId): void
    {
        ChatBlock::where('blocker_id', $byId)->where('blocked_id', $toId)->delete();
    }

    /** IDs the given user has blocked (outgoing blocks only). */
    public function blockedByMe(int $userId): array
    {
        return ChatBlock::where('blocker_id', $userId)->pluck('blocked_id')->all();
    }

    // --------------------------------------------------------------- Direct

    /** @param array{body?:?string, message_type?:string, attachment_path?:?string, attachment_name?:?string, reply_to_id?:?int} $data */
    public function sendDirect(int $senderId, int $recipientId, array $data): ChatMessage
    {
        if ($senderId === $recipientId) {
            throw ValidationException::withMessages(['recipient' => 'You cannot message yourself.']);
        }
        if ($this->isBlocked($senderId, $recipientId)) {
            throw ValidationException::withMessages(['recipient' => 'Messaging is blocked between you and this user.']);
        }

        return ChatMessage::create([
            'sender_id'       => $senderId,
            'recipient_id'    => $recipientId,
            'body'            => $data['body'] ?? null,
            'message_type'    => $data['message_type'] ?? 'text',
            'attachment_path' => $data['attachment_path'] ?? null,
            'attachment_name' => $data['attachment_name'] ?? null,
            'reply_to_id'     => $data['reply_to_id'] ?? null,
        ]);
    }

    /** Full ordered thread between two users. */
    public function conversation(int $userId, int $otherId): Collection
    {
        return ChatMessage::between($userId, $otherId)
            ->with(['sender', 'replyTo'])
            ->orderBy('id')
            ->get();
    }

    /** Mark every message from $otherId to $userId as read. */
    public function markConversationRead(int $userId, int $otherId): void
    {
        ChatMessage::where('sender_id', $otherId)
            ->where('recipient_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * Conversation partners with the last message and unread count, newest first.
     * @return Collection<int,array{user:\App\Models\User, last:ChatMessage, unread:int}>
     */
    public function directThreads(int $userId): Collection
    {
        $messages = ChatMessage::where('sender_id', $userId)
            ->orWhere('recipient_id', $userId)
            ->with(['sender', 'recipient'])
            ->orderByDesc('id')
            ->get();

        return $messages
            ->groupBy(fn ($m) => $m->sender_id === $userId ? $m->recipient_id : $m->sender_id)
            ->map(function ($group, $otherId) use ($userId) {
                $last = $group->first(); // already desc
                $other = $last->sender_id === $userId ? $last->recipient : $last->sender;

                return [
                    'user'   => $other,
                    'last'   => $last,
                    'unread' => $group->where('recipient_id', $userId)->whereNull('read_at')->count(),
                ];
            })
            ->filter(fn ($row) => $row['user'] !== null)
            ->sortByDesc(fn ($row) => $row['last']->id)
            ->values();
    }

    public function directUnreadTotal(int $userId): int
    {
        return ChatMessage::where('recipient_id', $userId)->whereNull('read_at')->count();
    }

    // ---------------------------------------------------------------- Groups

    /**
     * Create a group, making the creator an admin and adding the given members.
     * @param array{name:string, description?:?string, type?:string, class_id?:?int, section_id?:?int, subject_id?:?int, photo_path?:?string} $data
     */
    public function createGroup(array $data, int $creatorId, array $memberIds = []): ChatGroup
    {
        return DB::transaction(function () use ($data, $creatorId, $memberIds) {
            $group = ChatGroup::create([
                'name'        => $data['name'],
                'description' => $data['description'] ?? null,
                'type'        => $data['type'] ?? ChatGroup::TYPE_OPEN,
                'photo_path'  => $data['photo_path'] ?? null,
                'created_by'  => $creatorId,
                'class_id'    => $data['class_id'] ?? null,
                'section_id'  => $data['section_id'] ?? null,
                'subject_id'  => $data['subject_id'] ?? null,
            ]);

            $this->addMember($group, $creatorId, ChatGroupMember::ROLE_ADMIN);
            foreach (array_unique($memberIds) as $uid) {
                if ((int) $uid !== $creatorId) {
                    $this->addMember($group, (int) $uid);
                }
            }

            return $group;
        });
    }

    public function addMember(ChatGroup $group, int $userId, string $role = ChatGroupMember::ROLE_MEMBER): ChatGroupMember
    {
        return ChatGroupMember::firstOrCreate(
            ['group_id' => $group->id, 'user_id' => $userId],
            ['role' => $role],
        );
    }

    public function removeMember(ChatGroup $group, int $userId): void
    {
        ChatGroupMember::where('group_id', $group->id)->where('user_id', $userId)->delete();
    }

    public function setRole(ChatGroup $group, int $userId, string $role): void
    {
        ChatGroupMember::where('group_id', $group->id)->where('user_id', $userId)->update(['role' => $role]);
    }

    public function membership(ChatGroup $group, int $userId): ?ChatGroupMember
    {
        return ChatGroupMember::where('group_id', $group->id)->where('user_id', $userId)->first();
    }

    public function isMember(ChatGroup $group, int $userId): bool
    {
        return $this->membership($group, $userId) !== null;
    }

    /** A member may post if the group is open, or if they are an admin of an admin-only group. */
    public function canPost(ChatGroup $group, int $userId): bool
    {
        $member = $this->membership($group, $userId);
        if (! $member) {
            return false;
        }

        return ! $group->isAdminOnly() || $member->isAdmin();
    }

    /** @param array{body?:?string, message_type?:string, attachment_path?:?string, attachment_name?:?string} $data */
    public function sendGroup(ChatGroup $group, int $senderId, array $data): ChatGroupMessage
    {
        if (! $this->canPost($group, $senderId)) {
            throw ValidationException::withMessages(['group' => 'You are not allowed to post in this group.']);
        }

        $message = ChatGroupMessage::create([
            'group_id'        => $group->id,
            'sender_id'       => $senderId,
            'body'            => $data['body'] ?? null,
            'message_type'    => $data['message_type'] ?? 'text',
            'attachment_path' => $data['attachment_path'] ?? null,
            'attachment_name' => $data['attachment_name'] ?? null,
        ]);

        // The sender has implicitly read up to their own message.
        $this->markGroupRead($group, $senderId);

        return $message;
    }

    public function groupMessages(ChatGroup $group): Collection
    {
        return $group->messages()->with('sender')->orderBy('id')->get();
    }

    public function markGroupRead(ChatGroup $group, int $userId): void
    {
        ChatGroupMember::where('group_id', $group->id)->where('user_id', $userId)
            ->update(['last_read_at' => now()]);
    }

    /** Group messages after the member's last_read_at, not sent by them. */
    public function groupUnread(ChatGroup $group, int $userId): int
    {
        $member = $this->membership($group, $userId);
        if (! $member) {
            return 0;
        }

        return $group->messages()
            ->where('sender_id', '!=', $userId)
            ->when($member->last_read_at, fn ($q) => $q->where('created_at', '>', $member->last_read_at))
            ->count();
    }

    /**
     * Groups the user belongs to, with last message + unread count, newest activity first.
     * @return Collection<int,array{group:ChatGroup, last:?ChatGroupMessage, unread:int}>
     */
    public function userGroups(int $userId): Collection
    {
        $groupIds = ChatGroupMember::where('user_id', $userId)->pluck('group_id');

        return ChatGroup::whereIn('id', $groupIds)
            ->with(['messages' => fn ($q) => $q->latest('id')->limit(1)])
            ->get()
            ->map(fn ($group) => [
                'group'  => $group,
                'last'   => $group->messages->first(),
                'unread' => $this->groupUnread($group, $userId),
            ])
            ->sortByDesc(fn ($row) => optional($row['last'])->id ?? 0)
            ->values();
    }
}
