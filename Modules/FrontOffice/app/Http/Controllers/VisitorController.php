<?php

namespace Modules\FrontOffice\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\FrontOffice\Models\Visitor;
use Modules\FrontOffice\Services\FrontOfficeService;

class VisitorController extends Controller
{
    public function index()
    {
        $visitors = Visitor::latest('visit_date')->latest('id')->get();

        return view('frontoffice::visitors.index', compact('visitors'));
    }

    public function create()
    {
        return view('frontoffice::visitors.form', ['visitor' => new Visitor]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        Visitor::create($data);

        return redirect()->route('frontoffice.visitors.index')->with('status', 'Visitor logged.');
    }

    public function edit(Visitor $visitor)
    {
        return view('frontoffice::visitors.form', compact('visitor'));
    }

    public function update(Request $request, Visitor $visitor)
    {
        $visitor->update($this->validated($request));

        return redirect()->route('frontoffice.visitors.index')->with('status', 'Visitor updated.');
    }

    public function destroy(Visitor $visitor)
    {
        $visitor->delete();

        return redirect()->route('frontoffice.visitors.index')->with('status', 'Visitor deleted.');
    }

    public function checkout(Visitor $visitor, FrontOfficeService $frontOffice)
    {
        $frontOffice->checkOut($visitor);

        return redirect()->route('frontoffice.visitors.index')->with('status', 'Checked out.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'phone'        => ['nullable', 'string', 'max:40'],
            'purpose'      => ['nullable', 'string', 'max:255'],
            'to_meet'      => ['nullable', 'string', 'max:255'],
            'id_card'      => ['nullable', 'string', 'max:100'],
            'no_of_person' => ['nullable', 'integer', 'min:1'],
            'visit_date'   => ['required', 'date'],
            'in_time'      => ['nullable'],
            'out_time'     => ['nullable'],
            'note'         => ['nullable', 'string', 'max:255'],
        ]);

        if (empty($data['no_of_person'])) {
            $data['no_of_person'] = 1;
        }

        return $data;
    }
}
