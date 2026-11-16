<?php

namespace App\Support;

use App\Models\Laporan;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportPhoto
{
    // Call only after the controller has authorized access to the report.
    public static function response(Laporan $laporan, int $index, bool $noStore = true): BinaryFileResponse
    {
        $path = $laporan->foto_barang[$index] ?? null;
        abort_unless(is_string($path) && preg_match('~^laporan/[a-zA-Z0-9_-]+\.webp$~D', $path), 404);
        $root = realpath(Storage::disk('local')->path('laporan'));
        $file = realpath(Storage::disk('local')->path($path));
        abort_unless($root && $file && is_file($file) && str_starts_with($file, $root.DIRECTORY_SEPARATOR), 404);

        return response()->file($file, [
            'Content-Type' => 'image/webp',
            'Cache-Control' => $noStore ? 'private, no-store' : 'private, max-age=3600',
            'X-Content-Type-Options' => 'nosniff',
        ])->setPrivate();
    }
}
