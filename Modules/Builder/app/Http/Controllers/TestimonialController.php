<?php

namespace Modules\Builder\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Builder\Models\Testimonial;

class TestimonialController extends Controller
{
    public function index()
    {
        $testimonials = Testimonial::orderBy('position')->orderBy('id')->get();

        return view('builder::testimonials.index', compact('testimonials'));
    }

    public function create()
    {
        return view('builder::testimonials.form', ['testimonial' => new Testimonial]);
    }

    public function edit(Testimonial $testimonial)
    {
        return view('builder::testimonials.form', compact('testimonial'));
    }

    public function store(Request $request)
    {
        Testimonial::create($this->data($request));

        return redirect()->route('builder.testimonials.index')->with('status', 'Testimonial created.');
    }

    public function update(Request $request, Testimonial $testimonial)
    {
        $testimonial->update($this->data($request, $testimonial));

        return redirect()->route('builder.testimonials.index')->with('status', 'Testimonial updated.');
    }

    public function destroy(Testimonial $testimonial)
    {
        $testimonial->delete();

        return redirect()->route('builder.testimonials.index')->with('status', 'Testimonial deleted.');
    }

    private function data(Request $request, ?Testimonial $testimonial = null): array
    {
        $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'designation'  => ['nullable', 'string', 'max:255'],
            'organization' => ['nullable', 'string', 'max:255'],
            'quote'        => ['required', 'string'],
            'rating'       => ['required', 'integer', 'min:1', 'max:5'],
            'position'     => ['nullable', 'integer', 'min:0'],
            'photo'        => ['nullable', 'image', 'max:4096'],
        ]);

        $data = $request->only('name', 'designation', 'organization', 'quote', 'rating');
        $data['position'] = (int) $request->input('position', 0);
        $data['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('builder', 'public');
        }

        return $data;
    }
}
