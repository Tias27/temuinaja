<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ReportImages
{
    public static function store(array $files): array
    {
        if (! function_exists('imagecreatefromstring') || ! function_exists('imagewebp')) {
            throw ValidationException::withMessages([
                'foto_barang' => 'Pemrosesan foto sedang tidak tersedia. Hubungi petugas dan coba lagi nanti',
            ]);
        }

        $paths = [];
        try {
            foreach ($files as $file) {
                $image = @imagecreatefromstring(file_get_contents($file->getRealPath()));
                if (! $image) {
                    throw ValidationException::withMessages(['foto_barang' => 'Foto tidak dapat diproses.']);
                }
                $relative = 'laporan/'.Str::uuid().'.webp';
                ob_start();
                try {
                    $encoded = imagewebp($image, null, 85);
                    $bytes = ob_get_contents();
                } finally {
                    ob_end_clean();
                    imagedestroy($image);
                    unset($image);
                }
                if (! $encoded || ! $bytes) {
                    throw new \RuntimeException('Gagal memproses foto');
                }
                if (! Storage::disk('local')->put($relative, $bytes)) {
                    throw new \RuntimeException('Gagal menyimpan foto');
                }
                $paths[] = $relative;
            }

            return $paths;
        } catch (\Throwable $e) {
            self::discard($paths);
            throw $e;
        }
    }

    public static function discard(array $paths): void
    {
        foreach ($paths as $path) {
            if (! is_string($path) || ! preg_match('~^laporan/[a-zA-Z0-9_-]+\.webp$~D', $path)) {
                continue;
            }
            try {
                if (! Storage::disk('local')->delete($path)) {
                    Log::warning('report.photo_cleanup_failed');
                }
            } catch (\Throwable) {
                Log::warning('report.photo_cleanup_failed');
            }
        }
    }
}
