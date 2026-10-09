<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pengaturan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Tampilkan halaman login admin.
     * Halaman admin tetap terbuka — login hanya sebagai penanda sesi via tombol footer.
     */
    public function showLogin(Request $request)
    {
        if ($request->session()->get('admin_logged_in')) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.login');
    }

    /**
     * Proses login admin — kredensial dari Pengaturan (tanpa database user).
     * Default awal: admin / admin123 (segera ganti di Pengaturan).
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string', 'max:100'],
            'password' => ['required', 'string', 'max:255'],
        ]);

        $username = Pengaturan::get('admin_username', 'admin');
        $hash = Pengaturan::get('admin_password_hash');

        // Seed default bila belum pernah diatur.
        if (!$hash) {
            $hash = Hash::make('admin123');
            Pengaturan::set('admin_username', 'admin');
            Pengaturan::set('admin_password_hash', $hash);
            $username = 'admin';
        }

        if (
            hash_equals((string) $username, (string) $request->input('username')) &&
            Hash::check((string) $request->input('password'), $hash)
        ) {
            $request->session()->put('admin_logged_in', true);
            $request->session()->put('admin_username', $username);

            return redirect()->route('admin.dashboard');
        }

        return back()
            ->withErrors(['username' => 'Username atau kata sandi salah.'])
            ->withInput($request->only('username'));
    }

    /**
     * Keluar dari sesi admin.
     */
    public function logout(Request $request)
    {
        $request->session()->forget(['admin_logged_in', 'admin_username']);

        return redirect()->route('ppdb.index');
    }
}
