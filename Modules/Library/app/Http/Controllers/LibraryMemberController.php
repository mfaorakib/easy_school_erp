<?php

namespace Modules\Library\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Modules\Access\Enums\Role;
use Modules\Library\Models\LibraryMember;

class LibraryMemberController extends Controller
{
    public function index()
    {
        $memberUserIds = LibraryMember::pluck('user_id');

        return view('library::members.index', [
            'members' => LibraryMember::with('user')->latest()->get(),
            'users'   => User::where('is_active', true)->whereNotIn('id', $memberUserIds)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id', 'unique:library_members,user_id'],
        ]);

        $user = User::findOrFail($data['user_id']);
        $type = $user->hasRole(Role::Student->value) ? 'student' : 'staff';

        $member = LibraryMember::create([
            'user_id'   => $user->id,
            'type'      => $type,
            'joined_on' => now()->toDateString(),
            'is_active' => true,
        ]);
        $member->update(['member_no' => 'LM-'.str_pad((string) $member->id, 4, '0', STR_PAD_LEFT)]);

        return redirect()->route('library.members.index')->with('status', 'Member added.');
    }

    public function destroy(LibraryMember $member)
    {
        $member->delete();

        return redirect()->route('library.members.index')->with('status', 'Member removed.');
    }
}
