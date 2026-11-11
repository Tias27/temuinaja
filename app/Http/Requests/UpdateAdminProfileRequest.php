<?php

namespace App\Http\Requests;

class UpdateAdminProfileRequest extends AdminAccountRequest
{
    protected $errorBag = 'profile';

    public function rules(): array
    {
        return $this->accountRules($this->user('admin'));
    }

    protected function getRedirectUrl(): string
    {
        return route('admin.settings.index').'#profil';
    }
}
