<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Support\PaymentFee;
use Illuminate\Http\Request;

/**
 * Saklar global biaya admin pembayaran online. Default mati (fase early user).
 * Perubahan hanya berlaku untuk pendaftaran baru; tagihan yang sudah dibuat tidak diubah.
 */
class SuperAdminAdminFeeController extends Controller
{
    public function index()
    {
        return view('superadmin.admin-fee.index', ['enabled' => PaymentFee::enabled()]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'enabled' => ['required', 'boolean'],
        ]);

        $enabled = (bool) $validated['enabled'];
        AppSetting::set(PaymentFee::SETTING_KEY, $enabled ? '1' : '0');

        return back()->with('success', $enabled
            ? 'Biaya admin dinyalakan. Pendaftaran baru akan dikenai biaya admin.'
            : 'Biaya admin dimatikan. Pendaftaran baru tidak dikenai biaya admin.');
    }
}
