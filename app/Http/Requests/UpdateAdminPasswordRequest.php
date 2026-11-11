<?php

namespace App\Http\Requests;

use App\Rules\AdminPassword;

class UpdateAdminPasswordRequest extends AdminAccountRequest
{
    protected $errorBag = 'password';

    public function rules(): array
    {
        return [
            'current_password' => ['bail', 'required', 'string', 'max:1024', 'current_password:admin'],
            'password' => ['bail', 'required', 'string', 'confirmed', new AdminPassword],
        ];
    }

    protected function getRedirectUrl(): string
    {
        return route('admin.settings.index').'#kata-sandi';
    }
}
