<?php

namespace Modules\FrontOffice\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\FrontOffice\Models\PostalRecord;

class PostalController extends Controller
{
    public function index(Request $request)
    {
        $type = $this->filterType($request);

        $records = PostalRecord::when($type, fn ($q) => $q->where('type', $type))
            ->latest('postal_date')
            ->latest('id')
            ->get();

        return view('frontoffice::postal.index', compact('records', 'type'));
    }

    public function create(Request $request)
    {
        $type = $this->filterType($request) ?? 'dispatch';

        return view('frontoffice::postal.form', ['record' => new PostalRecord(['type' => $type])]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        PostalRecord::create($data);

        return redirect()
            ->route('frontoffice.postal.index', ['type' => $data['type']])
            ->with('status', ucfirst($data['type']).' saved.');
    }

    public function edit(PostalRecord $postal)
    {
        return view('frontoffice::postal.form', ['record' => $postal]);
    }

    public function update(Request $request, PostalRecord $postal)
    {
        $data = $this->validated($request);

        $postal->update($data);

        return redirect()
            ->route('frontoffice.postal.index', ['type' => $postal->type])
            ->with('status', ucfirst($postal->type).' saved.');
    }

    public function destroy(PostalRecord $postal)
    {
        $type = $postal->type;

        $postal->delete();

        return redirect()
            ->route('frontoffice.postal.index', ['type' => $type])
            ->with('status', ucfirst($type).' deleted.');
    }

    private function filterType(Request $request): ?string
    {
        return in_array($request->input('type'), ['dispatch', 'receive'])
            ? $request->input('type')
            : null;
    }

    private function validated(Request $request): array
    {
        $request->validate([
            'type'         => ['required', 'in:dispatch,receive'],
            'title'        => ['required', 'string', 'max:255'],
            'party'        => ['nullable', 'string', 'max:255'],
            'reference_no' => ['nullable', 'string', 'max:255'],
            'address'      => ['nullable', 'string', 'max:255'],
            'postal_date'  => ['required', 'date'],
            'note'         => ['nullable', 'string'],
            'attachment'   => ['nullable', 'file', 'max:4096'],
        ]);

        $data = $request->only('type', 'title', 'party', 'reference_no', 'address', 'postal_date', 'note');

        if ($request->hasFile('attachment')) {
            $data['attachment_path'] = $request->file('attachment')->store('frontoffice', 'public');
        }

        return $data;
    }
}
