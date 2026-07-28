<?php

namespace Modules\Accounting\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Accounting\Models\AccountHead;

class AccountHeadController extends Controller
{
    public function index()
    {
        $heads = AccountHead::orderBy('type')->orderBy('name')->get();

        return view('accounting::heads.index', compact('heads'));
    }

    public function create()
    {
        return view('accounting::heads.form', ['head' => new AccountHead]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['is_active'] = $request->boolean('is_active');

        AccountHead::create($data);

        return redirect()->route('accounting.heads.index')->with('status', 'Head saved.');
    }

    public function edit(AccountHead $head)
    {
        return view('accounting::heads.form', compact('head'));
    }

    public function update(Request $request, AccountHead $head)
    {
        $data = $this->validated($request);
        $data['is_active'] = $request->boolean('is_active');

        $head->update($data);

        return redirect()->route('accounting.heads.index')->with('status', 'Head saved.');
    }

    public function destroy(AccountHead $head)
    {
        if ($head->transactions()->exists()) {
            return back()->with('error', 'This head has transactions recorded against it — deactivate it instead of deleting.');
        }

        $head->delete();

        return redirect()->route('accounting.heads.index')->with('status', 'Head deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name'        => ['required', 'string', 'max:200'],
            'type'        => ['required', 'in:income,expense'],
            'description' => ['nullable', 'string'],
        ]);
    }
}
