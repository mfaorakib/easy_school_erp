<?php

namespace Modules\Chat\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Modules\Chat\Services\ChatService;

class ChatController extends Controller
{
    public function index(ChatService $chat)
    {
        $threads = $chat->directThreads(auth()->id());

        return view('chat::index', compact('threads'));
    }

    public function show(User $user, ChatService $chat)
    {
        $me = auth()->id();

        $chat->markConversationRead($me, $user->id);
        $messages = $chat->conversation($me, $user->id);
        $threads = $chat->directThreads($me);

        return view('chat::conversation', compact('user', 'messages', 'threads'));
    }

    public function send(Request $request, User $user, ChatService $chat)
    {
        $request->validate([
            'body'        => ['required_without:attachment', 'nullable', 'string'],
            'attachment'  => ['nullable', 'file', 'max:5120'],
            'reply_to_id' => ['nullable', 'integer'],
        ]);

        $data = [
            'body'         => $request->input('body'),
            'reply_to_id'  => $request->input('reply_to_id'),
            'message_type' => 'text',
        ];

        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('chat', 'public');
            $data['message_type']    = 'file';
            $data['attachment_path'] = $path;
            $data['attachment_name'] = $request->file('attachment')->getClientOriginalName();
        }

        try {
            $chat->sendDirect(auth()->id(), $user->id, $data);
        } catch (ValidationException $e) {
            throw $e;
        }

        return redirect()->route('chat.show', $user);
    }
}
