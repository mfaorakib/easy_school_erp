<?php

namespace Modules\Chat\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Chat\Models\ChatGroup;
use Modules\Chat\Services\ChatService;

class GroupChatController extends Controller
{
    public function index(ChatService $chat)
    {
        $groups = $chat->userGroups(auth()->id());

        return view('chat::groups.index', compact('groups'));
    }

    public function show(ChatGroup $group, ChatService $chat)
    {
        $me = auth()->id();

        abort_unless($chat->isMember($group, $me), 403);

        $chat->markGroupRead($group, $me);

        $messages = $chat->groupMessages($group);
        $canPost = $chat->canPost($group, $me);
        $groups = $chat->userGroups($me);

        return view('chat::groups.show', compact('group', 'messages', 'canPost', 'groups'));
    }

    public function send(Request $request, ChatGroup $group, ChatService $chat)
    {
        $request->validate([
            'body'       => ['required_without:attachment', 'nullable', 'string'],
            'attachment' => ['nullable', 'file', 'max:5120'],
        ]);

        $data = [
            'body'         => $request->input('body'),
            'message_type' => 'text',
        ];

        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('chat', 'public');
            $data['message_type'] = 'file';
            $data['attachment_path'] = $path;
            $data['attachment_name'] = $request->file('attachment')->getClientOriginalName();
        }

        $chat->sendGroup($group, auth()->id(), $data);

        return redirect()->route('chat.groups.show', $group);
    }
}
