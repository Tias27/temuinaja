<?php

namespace App\Rules;

use App\Support\WhatsApp;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class WhatsAppNumber implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail('Masukkan nomor WhatsApp yang valid');

            return;
        }

        if (WhatsApp::normalize($value) !== null) {
            return;
        }

        if (! preg_match('/^\+?[0-9\s()\-]+$/D', trim($value))) {
            $fail('Nomor WhatsApp tidak boleh berisi huruf atau simbol selain +, spasi, tanda kurung, dan tanda hubung');

            return;
        }

        $digits = preg_replace('/[^0-9]/', '', $value);
        $local = str_starts_with($digits, '08');
        if (! $local && ! str_starts_with($digits, '628')) {
            $fail('Awali nomor WhatsApp dengan 08, 628, atau +628');

            return;
        }

        $count = strlen($digits);
        $minimum = $local ? 9 : 10;
        $maximum = $local ? 14 : 15;
        $prefix = $local ? '08' : '628';
        $fail($count < $minimum
            ? "Nomor terlalu pendek: baru {$count} digit, minimal {$minimum} digit untuk awalan {$prefix}. Isi nomor WhatsApp lengkap"
            : "Nomor terlalu panjang: {$count} digit, maksimal {$maximum} digit untuk awalan {$prefix}");
    }
}
