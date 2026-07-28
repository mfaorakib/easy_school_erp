<?php

namespace Modules\Chat\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Modules\AcademicCore\Models\SchoolClass;
use Modules\AcademicCore\Models\Section;
use Modules\AcademicCore\Models\Subject;
use Modules\Chat\Models\ChatGroup;
use Modules\Chat\Services\ChatService;

class GroupManageController extends Controller
{
    public function create()
    {
        return view('chat::groups.create', [
            'classes'  => SchoolClass::active()->orderBy('name')->get(),
            'sections' => Section::active()->orderBy('name')->get(),
            'subjects' => Subject::active()->orderBy('name')->get(),
            'users'    => User::where('is_active', true)
                ->where('id', '!=', auth()->id())
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(Request $request, ChatService $chat)
    {
        $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'type'        => ['required', 'in:open,admin_only'],
            'class_id'    => ['nullable', 'exists:classes,id'],
            'section_id'  => ['nullable', 'exists:sections,id'],
            'subject_id'  => ['nullable', 'exists:subjects,id'],
            'members'     => ['array'],
            'members.*'   => ['exists:users,id'],
        ]);

        $group = $chat->createGroup(
            $request->only('name', 'description', 'type', 'class_id', 'section_id', 'subject_id'),
            auth()->id(),
            $request->input('members', [])
        );

        return redirect()->route('chat.groups.show', $group)->with('status', 'Group created.');
    }

    public function members(ChatGroup $group, ChatService $chat)
    {
        abort_unless($chat->isMember($group, auth()->id()), 403);

        $group->load('members.user');
        $me = $chat->membership($group, auth()->id());
        $isAdmin = $me && $me->isAdmin();

        $candidates = User::where('is_active', true)
            ->whereNotIn('id', $group->members->pluck('user_id'))
            ->orderBy('name')
            ->get();

        return view('chat::groups.members', compact('group', 'isAdmin', 'candidates'));
    }

    public function addMember(Request $request, ChatGroup $group, ChatService $chat)
    {
        abort_unless(optional($chat->membership($group, auth()->id()))->isAdmin(), 403);

        $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ]);

        $chat->addMember($group, (int) $request->user_id);

        return redirect()->route('chat.groups.members', $group)->with('status', 'Member added.');
    }

    public function setRole(Request $request, ChatGroup $group, User $user, ChatService $chat)
    {
        abort_unless(optional($chat->membership($group, auth()->id()))->isAdmin(), 403);

        $request->validate([
            'role' => ['required', 'in:admin,member'],
        ]);

        $chat->setRole($group, $user->id, $request->role);

        return redirect()->route('chat.groups.members', $group)->with('status', 'Role updated.');
    }

    public function removeMember(ChatGroup $group, User $user, ChatService $chat)
    {
        abort_unless(optional($chat->membership($group, auth()->id()))->isAdmin(), 403);

        $chat->removeMember($group, $user->id);

        return redirect()->route('chat.groups.members', $group)->with('status', 'Member removed.');
    }
}
