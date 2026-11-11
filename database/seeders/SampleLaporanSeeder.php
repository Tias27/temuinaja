<?php

namespace Database\Seeders;

use App\Models\Laporan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class SampleLaporanSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'TA-SAMPLE-DOMPET',
                'Dompet lipat kulit warna cokelat',
                'Kantin Gedung A',
                'DOMPET',
                [199, 101, 69],
            ],
            [
                'TA-SAMPLE-TUMBLER',
                'Tumbler termos hitam doff 600ml',
                'Ruang 204 Gedung B',
                'TUMBLER',
                [45, 42, 38],
            ],
            [
                'TA-SAMPLE-KUNCI',
                'Gantungan kunci motor + remote alarm',
                'Parkiran Utama',
                'KUNCI',
                [169, 79, 52],
            ],
            [
                'TA-SAMPLE-KTM',
                'Kartu Tanda Mahasiswa (KTM) Fasilkom',
                'Perpustakaan Pusat Lt. 2',
                'KTM',
                [92, 118, 141],
            ],
            [
                'TA-SAMPLE-EARBUDS',
                'Case TWS putih beserta earbuds',
                'Gazebo Gedung C',
                'EARBUDS',
                [116, 110, 102],
            ],
            [
                'TA-SAMPLE-PAYUNG',
                'Payung lipat warna navy gagang kayu',
                'Lobi Rektorat',
                'PAYUNG',
                [62, 80, 104],
            ],
            [
                'TA-SAMPLE-KACAMATA',
                'Kacamata frame hitam dengan kotak',
                'Laboratorium Komputer 3',
                'KACAMATA',
                [85, 74, 66],
            ],
            [
                'TA-SAMPLE-CHARGER',
                'Adaptor charger laptop USB-C 65W',
                'Coworking Space Kampus',
                'CHARGER',
                [148, 106, 80],
            ],
        ];

        foreach ($items as $index => [$code, $title, $location, $label, $rgb]) {
            $path = "laporan/sample-{$index}.webp";
            $image = imagecreatetruecolor(700, 700);
            imagefill($image, 0, 0, imagecolorallocate($image, 241, 221, 213));
            $primary = imagecolorallocate($image, ...$rgb);
            $accent = imagecolorallocate($image, 199, 101, 69);
            $paper = imagecolorallocate($image, 255, 250, 244);
            imagefilledrectangle($image, 80, 80, 620, 620, $paper);
            imagefilledrectangle($image, 160, 240, 540, 520, $primary);
            imagefilledrectangle($image, 220, 180, 480, 240, $accent);
            ob_start();
            imagewebp($image, null, 85);
            $bytes = ob_get_clean();
            imagedestroy($image);
            Storage::disk('local')->put($path, $bytes);

            Laporan::updateOrCreate(
                ['kode_laporan' => $code],
                [
                    'jenis_laporan' => 'ditemukan',
                    'nama_pelapor' => 'Petugas Akademik',
                    'foto_barang' => [$path],
                    'deskripsi' => $title,
                    'lokasi' => $location,
                    'tanggal_kejadian' => now()
                        ->subDays(count($items) - $index)
                        ->toDateString(),
                    'status_laporan' => 'dipublikasikan',
                    'catatan_admin' => 'Data sampel barang temuan kampus.',
                    'dipublikasikan_pada' => now()->subMinutes(count($items) - $index),
                ],
            );
        }
    }
}
