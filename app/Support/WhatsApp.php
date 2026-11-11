<?php

namespace App\Support;

class WhatsApp
{
    public static function normalize(string $number): ?string
    {
        $number = trim($number);
        if (! preg_match('/^\+?[0-9\s()\-]+$/D', $number)) {
            return null;
        }

        $number = preg_replace('/[^0-9]/', '', $number);
        if (str_starts_with($number, '0')) {
            $number = '62'.substr($number, 1);
        }

        return preg_match('/^628[0-9]{7,12}$/D', $number) ? $number : null;
    }

    public static function url(string $number, string $message = ''): ?string
    {
        $number = self::normalize($number);

        return $number === null ? null : 'https://wa.me/'.$number.($message !== '' ? '?text='.rawurlencode($message) : '');
    }
}
