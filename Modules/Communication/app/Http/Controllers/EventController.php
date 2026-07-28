<?php

namespace Modules\Communication\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Communication\Models\Event;

class EventController extends Controller
{
    public function index()
    {
        return view('communication::events.index', ['events' => Event::orderBy('start_date')->get()]);
    }

    public function create()
    {
        return view('communication::events.form', ['event' => new Event]);
    }

    public function store(Request $request)
    {
        Event::create($this->validated($request));

        return redirect()->route('communication.events.index')->with('status', 'Event created.');
    }

    public function edit(Event $event)
    {
        return view('communication::events.form', compact('event'));
    }

    public function update(Request $request, Event $event)
    {
        $event->update($this->validated($request));

        return redirect()->route('communication.events.index')->with('status', 'Event updated.');
    }

    public function destroy(Event $event)
    {
        $event->delete();

        return redirect()->route('communication.events.index')->with('status', 'Event deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'location' => ['nullable', 'string', 'max:150'],
        ]);
    }
}
