<?php

namespace App\Console\Commands;

use App\Models\Admin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class ChangeAdminPassword extends Command
{
    protected $signature = 'admin:password {username}';

    protected $description = 'Ganti sandi admin melalui prompt tersembunyi dan cabut sesi aktif';

    public function handle(): int
    {
        $admin = Admin::where('nama_pengguna', $this->argument('username'))->first();
        if (! $admin) {
            $this->error('Akun admin tidak ditemukan');

            return self::FAILURE;
        }
        $password = $this->secret('Kata sandi baru (minimal 12 karakter)');
        if (! is_string($password) || strlen($password) < 12 || strlen($password) > 72) {
            $this->error('Gunakan kata sandi antara 12 dan 72 byte');

            return self::FAILURE;
        }
        if ($password !== $this->secret('Ulangi kata sandi baru')) {
            $this->error('Konfirmasi kata sandi tidak sama');

            return self::FAILURE;
        }
        DB::transaction(function () use ($admin, $password) {
            $admin->update(['kata_sandi_hash' => Hash::make($password)]);
            if (config('session.driver') === 'database') {
                DB::connection(config('session.connection'))->table(config('session.table'))
                    ->where('user_id', $admin->getKey())->delete();
            }
        });
        Log::notice('security.admin_password_changed', ['admin_id' => $admin->getKey()]);
        $this->info('Kata sandi diperbarui; sesi database akun ini dicabut');

        return self::SUCCESS;
    }
}
