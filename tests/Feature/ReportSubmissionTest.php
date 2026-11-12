<?php

namespace Tests\Feature;

use App\Models\Laporan;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ReportSubmissionTest extends TestCase
{
    use RefreshDatabase;

    private function fields(): array
    {
        return [
            'nama_pelapor' => 'Pelapor Pengujian',
            'kontak_pelapor' => '081234567890',
            'deskripsi' => 'Dompet cokelat dengan tanda kecil di depan',
            'lokasi' => 'Kantin Gedung A',
            'tanggal_kejadian' => today()->toDateString(),
        ];
    }

    public static function reportTypes(): array
    {
        return [['hilang'], ['ditemukan']];
    }

    #[DataProvider('reportTypes')]
    public function test_reports_save_three_photos_and_show_confirmation(string $jenis): void
    {
        Storage::fake('local');

        $response = $this->post(route('laporan.store', $jenis), [
            ...$this->fields(),
            'foto_barang' => [
                UploadedFile::fake()->image('depan.jpg', 60, 40),
                UploadedFile::fake()->image('belakang.png', 40, 60),
                UploadedFile::fake()->image('detail.webp', 50, 50),
            ],
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseCount('laporan', 1);
        $report = Laporan::firstOrFail();
        $this->assertSame($jenis, $report->jenis_laporan);
        $this->assertSame('6281234567890', $report->kontak_pelapor);
        $this->assertSame('menunggu_pemeriksaan', $report->status_laporan);
        $this->assertNull($report->dipublikasikan_pada);
        $this->assertCount(3, $report->foto_barang);
        foreach ($report->foto_barang as $path) {
            Storage::disk('local')->assertExists($path);
            $this->assertSame('image/webp', getimagesizefromstring(Storage::disk('local')->get($path))['mime']);
        }
        $response->assertRedirect(route('laporan.confirmation', $report->kode_laporan));
        $this->get($response->headers->get('Location'))->assertOk()->assertSee($report->kode_laporan)
            ->assertDontSee('6281234567890');
        $this->get(route('foto.show', [$report, 0]))->assertNotFound();
    }

    public function test_corrupt_photo_is_rejected_before_saving_any_upload(): void
    {
        Storage::fake('local');

        $this->from(route('laporan.create', 'hilang'))
            ->post(route('laporan.store', 'hilang'), [
                ...$this->fields(),
                'foto_barang' => [
                    UploadedFile::fake()->image('valid.png'),
                    UploadedFile::fake()->createWithContent('corrupt.jpg', 'not an image'),
                ],
            ])
            ->assertRedirect(route('laporan.create', 'hilang'))
            ->assertSessionHasErrors('foto_barang.1');

        $this->assertDatabaseCount('laporan', 0);
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public static function invalidContacts(): array
    {
        return [[null], ['123'], ['0812abc345678'], ['0812 3456 7890'], ['0812-3456-7890'], ['+6281234567890'], ['javascript:6281234567890'], [['081234567890']], ['6281234567890123456']];
    }

    #[DataProvider('invalidContacts')]
    public function test_invalid_contact_is_rejected_without_saving_a_report(mixed $contact): void
    {
        Storage::fake('local');
        $this->from(route('laporan.create', 'hilang'))
            ->post(route('laporan.store', 'hilang'), [
                ...$this->fields(),
                'kontak_pelapor' => $contact,
                'foto_barang' => [UploadedFile::fake()->image('foto.png')],
            ])->assertRedirect(route('laporan.create', 'hilang'))->assertSessionHasErrors('kontak_pelapor');

        $this->assertDatabaseCount('laporan', 0);
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_international_contact_digits_are_accepted(): void
    {
        Storage::fake('local');
        $this->post(route('laporan.store', 'ditemukan'), [
            ...$this->fields(),
            'kontak_pelapor' => '6281234567890',
            'foto_barang' => [UploadedFile::fake()->image('foto.png')],
        ])->assertSessionHasNoErrors()->assertRedirect();

        $this->assertSame('6281234567890', Laporan::firstOrFail()->kontak_pelapor);
    }

    public function test_storage_failure_does_not_save_a_report_with_missing_photos(): void
    {
        $disk = \Mockery::mock(FilesystemAdapter::class);
        Storage::shouldReceive('disk')->with('local')->andReturn($disk);
        $disk->shouldReceive('put')->once()->andReturnFalse();

        $this->post(route('laporan.store', 'ditemukan'), [
            ...$this->fields(),
            'foto_barang' => [UploadedFile::fake()->image('foto.png')],
        ])->assertStatus(500);

        $this->assertDatabaseCount('laporan', 0);
    }

    public function test_missing_image_processor_returns_form_error_instead_of_500(): void
    {
        if (function_exists('imagecreatefromstring') && function_exists('imagewebp')) {
            $this->markTestSkipped('Run with imagecreatefromstring disabled to simulate missing GD');
        }

        Storage::fake('local');
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jRZkAAAAASUVORK5CYII=');
        $this->from(route('laporan.create', 'hilang'))
            ->post(route('laporan.store', 'hilang'), [
                ...$this->fields(),
                'foto_barang' => [UploadedFile::fake()->createWithContent('foto.png', $png)],
            ])
            ->assertRedirect(route('laporan.create', 'hilang'))
            ->assertSessionHasErrors('foto_barang')
            ->assertSessionHasInput('deskripsi', $this->fields()['deskripsi']);

        $this->assertDatabaseCount('laporan', 0);
        $this->assertSame([], Storage::disk('local')->allFiles());
    }
}
