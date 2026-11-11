<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class AdminPassword implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || mb_strlen($value) < 12) {
            $fail('Kata sandi minimal 12 karakter, gunakan gabungan beberapa kata');
        } elseif (strlen($value) > 72) {
            // Bcrypt only uses the first 72 bytes, including multibyte characters.
            $fail('Kata sandi terlalu panjang, gunakan lebih sedikit karakter');
        }
    }
}
