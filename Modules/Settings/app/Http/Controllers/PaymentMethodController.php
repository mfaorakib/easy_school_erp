<?php

namespace Modules\Settings\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Settings\Models\PaymentMethod;

class PaymentMethodController extends Controller
{
    public function index()
    {
        $methods = PaymentMethod::orderBy('position')->orderBy('id')->get();

        return view('settings::payment-methods.index', compact('methods'));
    }

    public function create()
    {
        return view('settings::payment-methods.form', ['method' => new PaymentMethod]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        PaymentMethod::create($data);

        return redirect()->route('settings.paymentMethods.index')->with('status', 'Saved.');
    }

    public function edit(PaymentMethod $paymentMethod)
    {
        return view('settings::payment-methods.form', ['method' => $paymentMethod]);
    }

    public function update(Request $request, PaymentMethod $paymentMethod)
    {
        $paymentMethod->update($this->validated($request));

        return redirect()->route('settings.paymentMethods.index')->with('status', 'Saved.');
    }

    public function destroy(PaymentMethod $paymentMethod)
    {
        $paymentMethod->delete();

        return redirect()->route('settings.paymentMethods.index')->with('status', 'Saved.');
    }

    private function validated(Request $request): array
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:80'],
            'position' => ['nullable', 'integer', 'min:0'],
            'driver'   => ['required', 'string', 'in:manual,stripe,bkash,nagad'],
        ]);

        $data = $request->only('name', 'position', 'driver');
        $data['position'] = (int) $request->input('position', 0);
        $data['is_active'] = $request->boolean('is_active');

        $data['config'] = match ($request->input('driver')) {
            'stripe' => $request->only(['secret_key', 'publishable_key', 'webhook_secret', 'currency']),
            'bkash'  => ['app_key' => $request->input('app_key'), 'app_secret' => $request->input('app_secret'), 'username' => $request->input('username'), 'password' => $request->input('password'), 'sandbox' => $request->boolean('sandbox')],
            'nagad'  => ['merchant_id' => $request->input('merchant_id'), 'merchant_private_key' => $request->input('merchant_private_key'), 'pg_public_key' => $request->input('pg_public_key'), 'sandbox' => $request->boolean('sandbox')],
            default  => null,
        };

        return $data;
    }
}
