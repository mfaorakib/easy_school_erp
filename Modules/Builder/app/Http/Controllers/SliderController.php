<?php

namespace Modules\Builder\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Builder\Models\Slider;

class SliderController extends Controller
{
    public function index()
    {
        $sliders = Slider::orderBy('position')->orderBy('id')->get();

        return view('builder::sliders.index', compact('sliders'));
    }

    public function create()
    {
        return view('builder::sliders.form', ['slider' => new Slider]);
    }

    public function edit(Slider $slider)
    {
        return view('builder::sliders.form', compact('slider'));
    }

    public function store(Request $request)
    {
        Slider::create($this->data($request));

        return redirect()->route('builder.sliders.index')->with('status', 'Slider created.');
    }

    public function update(Request $request, Slider $slider)
    {
        $slider->update($this->data($request, $slider));

        return redirect()->route('builder.sliders.index')->with('status', 'Slider updated.');
    }

    public function destroy(Slider $slider)
    {
        $slider->delete();

        return redirect()->route('builder.sliders.index')->with('status', 'Slider deleted.');
    }

    private function data(Request $request, ?Slider $slider = null): array
    {
        $request->validate([
            'title'      => ['nullable', 'string', 'max:255'],
            'subtitle'   => ['nullable', 'string', 'max:255'],
            'link_url'   => ['nullable', 'string', 'max:255'],
            'link_label' => ['nullable', 'string', 'max:120'],
            'position'   => ['nullable', 'integer', 'min:0'],
            'image'      => ['nullable', 'image', 'max:4096'],
        ]);

        $data = $request->only('title', 'subtitle', 'link_url', 'link_label');
        $data['position'] = (int) $request->input('position', 0);
        $data['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('builder', 'public');
        }

        return $data;
    }
}
