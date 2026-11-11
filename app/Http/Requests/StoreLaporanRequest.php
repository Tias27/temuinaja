<?php

namespace App\Http\Requests;

use App\Rules\WhatsAppNumber;
use App\Support\WhatsApp;
use Illuminate\Foundation\Http\FormRequest;

class StoreLaporanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->route('jenis'), ['hilang', 'ditemukan'], true);
    }

    public function rules(): array
    {
        return [
            'nama_pelapor' => 'required|string|max:120',
            'kontak_pelapor' => ['bail', 'required', 'string', new WhatsAppNumber],
            'foto_barang' => 'required|array|min:1|max:3',
            'foto_barang.*' => 'bail|required|image|mimes:jpg,jpeg,png,webp|max:5120|dimensions:max_width=4096,max_height=4096',
            'deskripsi' => 'required|string|min:10|max:2000',
            'lokasi' => 'required|string|max:180',
            'tanggal_kejadian' => 'required|date|before_or_equal:today',
        ];
    }

    protected function prepareForValidation(): void
    {
        $contact = $this->input('kontak_pelapor');
        if (is_string($contact) && ($normalized = WhatsApp::normalize($contact)) !== null) {
            $this->merge(['kontak_pelapor' => $normalized]);
        }
    }

    public function messages(): array
    {
        return [
            'kontak_pelapor.required' => 'Isi nomor WhatsApp agar petugas bisa menghubungimu',
            'kontak_pelapor.string' => 'Masukkan nomor WhatsApp yang valid',
            'foto_barang.*.dimensions' => 'Gunakan foto valid dengan dimensi maksimal 4096 x 4096 piksel',
        ];
    }
}
