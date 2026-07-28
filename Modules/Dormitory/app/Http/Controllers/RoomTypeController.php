<?php

namespace Modules\Dormitory\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Dormitory\Models\RoomType;

class RoomTypeController extends Controller
{
    public function index()
    {
        return view('dormitory::room_types.index', ['roomTypes' => RoomType::orderBy('name')->get()]);
    }

    public function create()
    {
        return view('dormitory::room_types.form', ['roomType' => new RoomType]);
    }

    public function store(Request $request)
    {
        RoomType::create($this->validated($request));

        return redirect()->route('dormitory.room-types.index')->with('status', 'Room type added.');
    }

    public function edit(RoomType $roomType)
    {
        return view('dormitory::room_types.form', ['roomType' => $roomType]);
    }

    public function update(Request $request, RoomType $roomType)
    {
        $roomType->update($this->validated($request));

        return redirect()->route('dormitory.room-types.index')->with('status', 'Room type updated.');
    }

    public function destroy(RoomType $roomType)
    {
        $roomType->delete();

        return redirect()->route('dormitory.room-types.index')->with('status', 'Room type deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
        ]);
    }
}
