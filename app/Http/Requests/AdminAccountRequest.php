<?php

namespace App\Http\Requests;

use App\Models\Admin;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

abstract class AdminAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin') instanceof Admin;
    }

    protected function accountRules(?Admin $admin = null): array
    {
        return [
            'nama_admin' => ['required', 'string', 'max:120'],
            'nama_pengguna' => [
                'bail', 'required', 'string', 'min:3', 'max:50', 'regex:/^[a-zA-Z0-9._-]+$/D',
                Rule::unique('admin', 'nama_pengguna')->ignore($admin?->getKey(), 'id_admin'),
            ],
            'current_password' => ['bail', 'required', 'string', 'max:1024', 'current_password:admin'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama_admin.required' => 'Isi nama lengkap admin',
            'nama_admin.max' => 'Nama maksimal 120 karakter',
            'nama_pengguna.required' => 'Isi nama pengguna untuk login',
            'nama_pengguna.min' => 'Nama pengguna minimal 3 karakter',
            'nama_pengguna.max' => 'Nama pengguna maksimal 50 karakter',
            'nama_pengguna.regex' => 'Gunakan huruf, angka, titik, garis bawah, atau tanda hubung',
            'nama_pengguna.unique' => 'Nama pengguna sudah dipakai, pilih nama lain',
            'current_password.required' => 'Masukkan kata sandi akunmu untuk konfirmasi',
            'current_password.current_password' => 'Kata sandi akunmu belum sesuai',
            'password.required' => 'Isi kata sandi baru',
            'password.confirmed' => 'Konfirmasi kata sandi belum sama',
        ];
    }
}
