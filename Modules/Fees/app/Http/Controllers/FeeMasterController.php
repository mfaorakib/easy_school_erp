<?php

namespace Modules\Fees\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AcademicCore\Models\SchoolClass;
use Modules\Fees\Models\FeeGroup;
use Modules\Fees\Models\FeeMaster;
use Modules\Fees\Models\FeeType;

class FeeMasterController extends Controller
{
    public function index()
    {
        $masters = FeeMaster::with(['group', 'type', 'schoolClass'])->latest()->get();

        return view('fees::masters.index', compact('masters'));
    }

    public function create()
    {
        return view('fees::masters.form', $this->formData(new FeeMaster));
    }

    public function store(Request $request)
    {
        FeeMaster::create($this->validated($request));

        return redirect()->route('fees.masters.index')->with('status', 'Fee created.');
    }

    public function edit(FeeMaster $master)
    {
        return view('fees::masters.form', $this->formData($master));
    }

    public function update(Request $request, FeeMaster $master)
    {
        $master->update($this->validated($request));

        return redirect()->route('fees.masters.index')->with('status', 'Fee updated.');
    }

    public function destroy(FeeMaster $master)
    {
        $master->delete();

        return redirect()->route('fees.masters.index')->with('status', 'Fee deleted.');
    }

    private function formData(FeeMaster $master): array
    {
        return [
            'master'  => $master,
            'groups'  => FeeGroup::where('is_active', true)->orderBy('name')->get(),
            'types'   => FeeType::where('is_active', true)->orderBy('name')->get(),
            'classes' => SchoolClass::active()->orderBy('name')->get(),
        ];
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'fee_group_id' => ['required', 'integer', 'exists:fee_groups,id'],
            'fee_type_id'  => ['required', 'integer', 'exists:fee_types,id'],
            'class_id'     => ['nullable', 'integer', 'exists:classes,id'],
            'amount'       => ['required', 'numeric', 'min:0'],
            'due_date'     => ['nullable', 'date'],
            'fine_type'    => ['required', 'in:none,percentage,fixed'],
            'fine_value'   => ['nullable', 'numeric', 'min:0'],
        ]);
    }
}
