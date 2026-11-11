<?php

namespace Tests\Feature;

use App\Http\Controllers\PublicLaporanController;
use App\Models\Admin;
use App\Models\Laporan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CompletedReportVisibilityTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): Admin
    {
        return Admin::create([
            'nama_admin' => 'Petugas Arsip',
            'nama_pengguna' => 'petugas-arsip',
            'kata_sandi_hash' => bcrypt('sandi-pengujian'),
        ]);
    }

    private function report(array $attributes = []): Laporan
    {
        return Laporan::create([
            'kode_laporan' => 'TA-'.strtoupper(bin2hex(random_bytes(6))),
            'jenis_laporan' => 'ditemukan',
            'nama_pelapor' => 'Pelapor Privat',
            'foto_barang' => ['laporan/test.webp'],
            'deskripsi' => 'Dompet selesai untuk pengujian',
            'lokasi' => 'Gedung A',
            'tanggal_kejadian' => today()->subWeek(),
            'status_laporan' => 'selesai',
            'dipublikasikan_pada' => now()->subWeek(),
            ...$attributes,
        ]);
    }

    public static function reportTypes(): array
    {
        return [['hilang'], ['ditemukan']];
    }

    #[DataProvider('reportTypes')]
    public function test_completed_report_expires_at_48_hours_but_admin_keeps_history(string $type): void
    {
        $start = now()->startOfSecond();
        $this->travelTo($start);
        Storage::fake('local');
        Storage::disk('local')->put('laporan/test.webp', 'test-photo');
        Route::get('/visibility-mockup', [PublicLaporanController::class, 'mockup'])->name('mockup');
        $admin = $this->admin();
        $report = $this->report(['jenis_laporan' => $type]);
        $active = $this->report([
            'jenis_laporan' => $type,
            'status_laporan' => 'dipublikasikan',
            'deskripsi' => 'Dompet aktif untuk pengujian',
        ]);
        $report->penyelesaian()->create([
            'jenis_penyelesaian' => $type === 'hilang' ? 'ditemukan' : 'diambil',
            'nama_pihak' => 'Pihak Privat',
            'kontak_pihak' => '6281234567890',
            'lokasi_ditemukan' => 'Kantin',
            'catatan_verifikasi' => 'Bukti kepemilikan sesuai',
            'id_admin' => $admin->id_admin,
            'diselesaikan_pada' => $start,
        ]);
        $term = rtrim(strtr(base64_encode('Dompet'), '+/', '-_'), '=');
        $lists = [
            route('laporan.index', $type),
            route('laporan.search.index', ['jenis' => $type, 'term' => $term]),
        ];
        if ($type === 'ditemukan') {
            $lists = [...$lists, '/', '/visibility-mockup'];
        }

        $this->travelTo($start->copy()->addHours(48)->subSecond());
        // Editing a report must not restart the visibility period.
        $report->touch();
        foreach ($lists as $url) {
            $this->get($url)->assertOk()->assertSee($report->deskripsi)
                ->assertViewHas('laporan', fn ($items) => $items->total() === 2);
        }
        $this->get(route('laporan.show', $report))->assertOk()
            ->assertSee('LAPORAN SELESAI')->assertDontSee('detail-contact-action', false)
            ->assertDontSee('Pihak Privat')->assertDontSee('6281234567890');
        $this->get(route('foto.show', [$report, 0]))->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private');
        $this->get(route('laporan.confirmation', $report->kode_laporan))->assertOk();

        $this->travelTo($start->copy()->addHours(48));
        foreach ($lists as $url) {
            $this->get($url)->assertOk()->assertDontSee($report->deskripsi)
                ->assertSee($active->deskripsi)
                ->assertViewHas('laporan', fn ($items) => $items->total() === 1);
        }
        $this->get(route('laporan.show', $report))->assertNotFound();
        $this->get(route('foto.show', [$report, 0]))->assertNotFound();
        $this->get(route('laporan.confirmation', $report->kode_laporan))->assertNotFound();
        $this->get(route('laporan.show', $active))->assertOk();

        $this->travelTo($start->copy()->addHours(49));
        $this->get(route('laporan.show', $report))->assertNotFound();
        $this->actingAs($admin, 'admin');
        $this->get(route('admin.index', ['jenis' => $type, 'status' => 'selesai']))
            ->assertOk()->assertSee($report->kode_laporan);
        $this->get(route('admin.show', $report))->assertOk()
            ->assertSee('Pihak Privat')->assertSee('Petugas Arsip');
        $this->get(route('foto.show', [$report, 0]))->assertOk();
        $this->assertDatabaseHas('laporan', ['id_laporan' => $report->id_laporan, 'status_laporan' => 'selesai']);
        $this->assertDatabaseCount('penyelesaian_laporan', 1);
        Storage::disk('local')->assertExists('laporan/test.webp');
    }

    public function test_legacy_reports_use_handover_time_or_last_update_when_history_is_missing(): void
    {
        $this->travelTo(now()->startOfSecond());
        $admin = $this->admin();
        foreach ([47 => true, 48 => false, 49 => false] as $hours => $visible) {
            $withoutHistory = $this->report(['updated_at' => now()->subHours($hours)]);
            $this->assertSame($visible, $withoutHistory->isVisibleToPublic());

            $withHandover = $this->report();
            $withHandover->penyerahan()->create([
                'jenis_penyerahan' => 'ke_pemilik',
                'nama_penyerah' => 'Petugas',
                'nama_penerima' => 'Pemilik',
                'id_admin' => $admin->id_admin,
                'tanggal_penyerahan' => now()->subHours($hours),
            ]);
            $this->assertSame($visible, $withHandover->isVisibleToPublic());
        }
    }
}
