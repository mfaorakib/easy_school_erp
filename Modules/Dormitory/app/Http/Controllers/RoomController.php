<?php

namespace Modules\Dormitory\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Dormitory\Models\Dormitory;
use Modules\Dormitory\Models\DormitoryRoom;
use Modules\Dormitory\Models\RoomType;

class RoomController extends Controller
{
    public function index()
    {
        $rooms = DormitoryRoom::with(['dormitory', 'type'])->latest()->get();

        return view('dormitory::rooms.index', compact('rooms'));
    }

    public function create()
    {
        return view('dormitory::rooms.form', $this->formData(new DormitoryRoom));
    }

    public function store(Request $request)
    {
        DormitoryRoom::create($this->validated($request));

        return redirect()->route('dormitory.rooms.index')->with('status', 'Room added.');
    }

    public function edit(DormitoryRoom $room)
    {
        return view('dormitory::rooms.form', $this->formData($room));
    }

    public function update(Request $request, DormitoryRoom $room)
    {
        $room->update($this->validated($request));

        return redirect()->route('dormitory.rooms.index')->with('status', 'Room updated.');
    }

    public function destroy(DormitoryRoom $room)
    {
        $room->delete();

        return redirect()->route('dormitory.rooms.index')->with('status', 'Room deleted.');
    }

    private function formData(DormitoryRoom $room): array
    {
        return [
            'room'        => $room,
            'dormitories' => Dormitory::active()->orderBy('name')->get(),
            'roomTypes'   => RoomType::where('is_active', true)->orderBy('name')->get(),
        ];
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'dormitory_id' => ['required', 'exists:dormitories,id'],
            'room_type_id' => ['nullable', 'exists:room_types,id'],
            'room_no'      => ['required', 'string', 'max:50'],
            'capacity'     => ['required', 'integer', 'min:1'],
            'cost'         => ['nullable', 'numeric', 'min:0'],
        ]);
    }
}
