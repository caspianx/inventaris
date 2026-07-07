<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        $user = $request->user();

        return view('profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'has_cash_drawer' => ['nullable', 'boolean'],
            'auto_open_cash_drawer' => ['nullable', 'boolean'],
        ]);

        $user->has_cash_drawer = $request->boolean('has_cash_drawer');
        $user->auto_open_cash_drawer = $request->boolean('auto_open_cash_drawer');
        $user->save();

        return redirect()->route('profile.edit')->with('success', 'Pengaturan profil berhasil diperbarui.');
    }

    public function testCashDrawer(Request $request)
    {
        $user = $request->user();

        $store = \App\Models\StoreSetting::current();
        if (! $store->cash_drawer_address) {
            return redirect()->route('profile.edit')->with('error', 'Alamat cash drawer toko belum dikonfigurasi. Silakan isi di Pengaturan Toko.');
        }

        $fakeSale = (object) [
            'id' => 0,
            'invoice_number' => 'TEST-'.now()->format('YmdHis'),
            'total' => 0,
        ];

        $result = app(\App\Services\CashDrawerService::class)->open($fakeSale);

        if (is_array($result)) {
            if (! empty($result['success'])) {
                $msg = 'Percobaan membuka cash drawer terkirim.';
                if (! empty($result['status'])) {
                    $msg .= ' HTTP status: '.$result['status'].'.';
                }
                if (! empty($result['body'])) {
                    $msg .= ' Response: '.mb_strimwidth($result['body'], 0, 400, '...');
                }

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => true, 'message' => $msg], 200);
                }

                return redirect()->route('profile.edit')->with('success', $msg);
            }

            $err = $result['error'] ?? ($result['body'] ?? 'Unknown error');
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Percobaan membuka cash drawer gagal: '.$err], 500);
            }

            return redirect()->route('profile.edit')->with('error', 'Percobaan membuka cash drawer gagal: '.$err);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => (bool) $result, 'message' => 'Percobaan membuka cash drawer '.($result ? 'terkirim' : 'gagal')], $result ? 200 : 500);
        }

        if ($result) {
            return redirect()->route('profile.edit')->with('success', 'Percobaan membuka cash drawer terkirim.');
        }

        return redirect()->route('profile.edit')->with('error', 'Percobaan membuka cash drawer gagal.');
    }
}
