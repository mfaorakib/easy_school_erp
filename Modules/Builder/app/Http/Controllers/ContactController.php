<?php

namespace Modules\Builder\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Builder\Models\Message;

/** Captures public Contact / Newsletter section submissions. No auth. */
class ContactController extends Controller
{
    public function submit(Request $request)
    {
        $type = $request->input('type') === 'subscribe' ? 'subscribe' : 'contact';

        if ($type === 'subscribe') {
            $data = $request->validate(['email' => ['required', 'email', 'max:255']]);
            Message::create(['type' => 'subscribe', 'email' => $data['email']]);

            return back()->with('cms_sent', __('ui.subscribed_ok'));
        }

        $data = $request->validate([
            'name'    => ['required', 'string', 'max:255'],
            'email'   => ['required', 'email', 'max:255'],
            'subject' => ['nullable', 'string', 'max:255'],
            'body'    => ['required', 'string', 'max:5000'],
        ]);

        Message::create($data + ['type' => 'contact']);

        return back()->with('cms_sent', __('ui.message_sent_ok'));
    }
}
