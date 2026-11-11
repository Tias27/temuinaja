<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAdminRequest;
use App\Http\Requests\UpdateAdminPasswordRequest;
use App\Http\Requests\UpdateAdminProfileRequest;
use App\Models\Admin;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class SettingsController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('tab') === 'admins' ? 'admins' : 'profile';
        $admin = $request->user('admin');
        $accounts = null;
        if ($tab === 'admins') {
            $page = $request->query('page', '1');
            abort_unless(is_scalar($page) && preg_match('/^[1-9][0-9]{0,4}$/D', (string) $page) && (int) $page <= 10000, 404);
            $perPage = preg_match('/Android|iPhone|iPad|iPod|Mobile/i', $request->userAgent() ?? '') ? 5 : 10;
            $accounts = Admin::query()->select(['id_admin', 'nama_admin', 'nama_pengguna', 'created_at'])
                ->orderByDesc('id_admin')->paginate($perPage)->appends(['tab' => 'admins']);
        }

        return view('admin.settings.index', compact('tab', 'admin', 'accounts'));
    }

    public function updateProfile(UpdateAdminProfileRequest $request)
    {
        $admin = $request->user('admin');
        try {
            $admin->update($request->safe()->only(['nama_admin', 'nama_pengguna']));
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['nama_pengguna' => 'Nama pengguna sudah dipakai, pilih nama lain'])
                ->errorBag('profile')->redirectTo(route('admin.settings.index').'#profil');
        }
        Log::info('security.admin_profile_updated', ['admin_id' => $admin->getKey()]);

        return redirect()->route('admin.settings.index')->with('sukses', 'Profilmu berhasil diperbarui');
    }

    public function updatePassword(UpdateAdminPasswordRequest $request)
    {
        $admin = $request->user('admin');
        DB::transaction(function () use ($request, $admin) {
            $admin->update(['kata_sandi_hash' => Hash::make($request->validated('password'))]);
            if (config('session.driver') === 'database') {
                DB::connection(config('session.connection'))->table(config('session.table'))
                    ->where('user_id', $admin->getKey())->where('id', '!=', $request->session()->getId())->delete();
            }
        });
        $request->session()->regenerate();
        Log::notice('security.admin_password_changed', ['admin_id' => $admin->getKey()]);

        return redirect()->route('admin.settings.index')->with('sukses', 'Kata sandi berhasil diganti, silakan gunakan kata sandi baru saat login berikutnya');
    }

    public function storeAdmin(StoreAdminRequest $request)
    {
        try {
            $account = Admin::create([
                ...$request->safe()->only(['nama_admin', 'nama_pengguna']),
                'kata_sandi_hash' => Hash::make($request->validated('password')),
                'peran' => 'petugas',
            ]);
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['nama_pengguna' => 'Nama pengguna sudah dipakai, pilih nama lain'])
                ->errorBag('createAdmin')->redirectTo(route('admin.settings.index', ['tab' => 'admins']).'#tambah-admin');
        }
        Log::notice('security.admin_created', [
            'admin_id' => $request->user('admin')->getKey(), 'created_admin_id' => $account->getKey(),
        ]);

        return redirect()->route('admin.settings.index', ['tab' => 'admins'])->with('sukses', 'Akun admin baru berhasil dibuat dan sudah bisa login');
    }
}
