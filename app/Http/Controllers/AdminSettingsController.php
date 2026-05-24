<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminSettingsController extends Controller
{
    public function index()
    {
        return view('admin.settings.index', [
            'admin' => auth()->user(),
            'paymentMethods' => [
                'bank' => 'Transfer Bank / Rekening',
                'ovo' => 'OVO',
                'dana' => 'DANA',
                'gopay' => 'GoPay',
                'shopeepay' => 'ShopeePay',
            ],
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'community_name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users')->ignore($request->user()->id)],
            'payment_method' => ['nullable', 'string', Rule::in(['bank', 'ovo', 'dana', 'gopay', 'shopeepay'])],
            'payment_account' => ['nullable', 'string', 'max:255'],
        ]);

        $request->user()->update($validated);

        return redirect()->route('admin.settings.index')->with('success', 'Profil admin berhasil diperbarui.');
    }
}
