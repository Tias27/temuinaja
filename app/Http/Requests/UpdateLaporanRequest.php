<?php

namespace App\Http\Requests;

class UpdateLaporanRequest extends StoreLaporanRequest
{
    public function authorize(): bool
    {
        abort_unless($this->route('laporan')?->belongsToBrowser($this->attributes->get('browser_history_hash')), 404);

        return true;
    }

    public function rules(): array
    {
        return [
            ...parent::rules(),
            'foto_barang' => empty($this->route('laporan')?->foto_barang)
                ? 'required|array|min:1|max:3' : 'sometimes|array|min:1|max:3',
        ];
    }
}
