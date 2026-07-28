<?php

namespace Modules\Builder\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Builder\Models\Message;

/** Admin inbox for Contact / Newsletter submissions. */
class MessageController extends Controller
{
    public function index()
    {
        $messages = Message::latest()->paginate(20);

        // Mark the current page of messages as read.
        Message::whereIn('id', $messages->pluck('id'))->where('is_read', false)->update(['is_read' => true]);

        return view('builder::messages.index', compact('messages'));
    }

    public function destroy(Message $message)
    {
        $message->delete();

        return redirect()->route('builder.messages.index')->with('status', 'Message deleted.');
    }
}
