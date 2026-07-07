<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'name' => ['required', 'string'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended($this->firstAccessibleUrl($request));
        }

        return back()->withErrors([
            'name' => 'Nama atau password salah.',
        ])->onlyInput('name');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function firstAccessibleUrl(Request $request): string
    {
        $user = $request->user();

        $routes = [
            'dashboard.view' => 'dashboard',
            'items.view' => 'items.index',
            'sales.view' => 'sales.index',
            'sales.create' => 'sales.create',
            'stock_movements.view' => 'stock-movements.index',
            'categories.manage' => 'categories.index',
            'suppliers.manage' => 'suppliers.index',
            'purchase_orders.view' => 'purchase-orders.index',
            'store_settings.manage' => 'store-settings.edit',
            'users.manage' => 'users.index',
            'role_permissions.manage' => 'role-permissions.edit',
        ];

        foreach ($routes as $permission => $route) {
            if ($user->canAccess($permission)) {
                return route($route);
            }
        }

        return route('no-access');
    }
}
