<?php

namespace Modules\Chat\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Modules\Chat\Services\ChatService;

class ContactController extends Controller
{
    public function index(Request $request, ChatService $chat)
    {
        $search = $request->input('q');

        $users = User::where('id', '!=', auth()->id())
            ->where('is_active', true)
            ->when($search, fn ($q) => $q->where('name', 'like', '%' . $search . '%')
                ->orWhere('email', 'like', '%' . $search . '%'))
            ->orderBy('name')
            ->get();

        $blockedIds = $chat->blockedByMe(auth()->id());

        return view('chat::contacts', compact('users', 'blockedIds', 'search'));
    }

    public function block(User $user, ChatService $chat)
    {
        $chat->block(auth()->id(), $user->id);

        return redirect()->route('chat.contacts')->with('status', 'User blocked.');
    }

    public function unblock(User $user, ChatService $chat)
    {
        $chat->unblock(auth()->id(), $user->id);

        return redirect()->route('chat.contacts')->with('status', 'User unblocked.');
    }
}
