<?php

namespace App\Http\Requests;

use App\Rules\AdminPassword;

class StoreAdminRequest extends AdminAccountRequest
{
    protected $errorBag = 'createAdmin';

    public function rules(): array
    {
        return [
            ...$this->accountRules(),
            'password' => ['bail', 'required', 'string', 'confirmed', new AdminPassword],
        ];
    }

    protected function getRedirectUrl(): string
    {
        return route('admin.settings.index', ['tab' => 'admins']).'#tambah-admin';
    }
}
