<?php

namespace App\Http\Requests;

use App\Rules\WhatsAppNumber;
use App\Support\WhatsApp;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompleteLaporanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('admin')->check();
    }

    protected function prepareForValidation(): void
    {
        $contact = $this->input('kontak_pihak');
        if (is_string($contact) && ($normalized = WhatsApp::normalize($contact)) !== null) {
            $this->merge(['kontak_pihak' => $normalized]);
        }
    }

    public function rules(): array
    {
        $isLost = $this->route('laporan')?->jenis_laporan === 'hilang';

        return [
            'nama_pihak' => ['required', 'string', 'max:120'],
            'kontak_pihak' => ['bail', 'required', 'string', new WhatsAppNumber],
            'lokasi_ditemukan' => [Rule::requiredIf($isLost), 'nullable', 'string', 'max:180'],
            'catatan_verifikasi' => [Rule::requiredIf(! $isLost), 'nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'kontak_pihak.required' => 'Isi nomor WhatsApp penemu atau penerima',
            'kontak_pihak.string' => 'Masukkan nomor WhatsApp yang valid',
            'lokasi_ditemukan.required' => 'Isi lokasi barang ditemukan',
            'catatan_verifikasi.required' => 'Catat bukti kepemilikan sebelum menyelesaikan laporan',
        ];
    }
}
