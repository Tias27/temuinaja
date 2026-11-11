<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function create()
    {
        return view('admin.login');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_pengguna' => 'bail|required|string|max:255',
            'password' => 'bail|required|string|max:1024',
        ]);
        if (! Auth::guard('admin')->attempt($data)) {
            Log::warning('security.admin_login_failed', ['ip' => $request->ip()]);
            throw ValidationException::withMessages([
                'nama_pengguna' => 'Kredensial tidak sesuai.',
            ]);
        }
        $request->session()->regenerate();
        Log::info('security.admin_login', ['admin_id' => Auth::guard('admin')->id(), 'ip' => $request->ip()]);

        return redirect()->route('admin.index');
    }

    public function destroy(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
