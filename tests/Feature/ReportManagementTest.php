<?php

namespace Tests\Feature;

use App\Http\Middleware\BrowserReportHistory;
use App\Models\Admin;
use App\Models\Laporan;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReportManagementTest extends TestCase
{
    use RefreshDatabase;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::disk('local')->put('laporan/original.webp', 'original-image');
        $this->token = bin2hex(random_bytes(32));
        $this->withCookie(BrowserReportHistory::COOKIE, $this->token);
        $this->withCredentials();
    }

    private function report(string $status = 'dipublikasikan', string $type = 'hilang'): Laporan
    {
        return Laporan::create([
            'kode_laporan' => 'TA-'.strtoupper(bin2hex(random_bytes(6))),
            'jenis_laporan' => $type, 'nama_pelapor' => 'Nama Pemilik',
            'kontak_pelapor' => '6281234567890', 'foto_barang' => ['laporan/original.webp'],
            'deskripsi' => 'Deskripsi asli barang pribadi', 'lokasi' => 'Kantin',
            'tanggal_kejadian' => today(), 'status_laporan' => $status,
            'dipublikasikan_pada' => now(), 'browser_history_hash' => hash('sha256', $this->token),
        ]);
    }

    private function fields(): array
    {
        return [
            'nama_pelapor' => 'Nama Diperbarui', 'kontak_pelapor' => '081211112222',
            'deskripsi' => 'Deskripsi diperbarui oleh pelapor', 'lokasi' => 'Gedung B',
            'tanggal_kejadian' => today()->toDateString(),
        ];
    }

    public function test_owner_edits_both_types_keeps_photos_and_requires_review_again(): void
    {
        foreach (['hilang', 'ditemukan'] as $type) {
            $report = $this->report('dipublikasikan', $type);
            $this->get(route('laporan.history.edit', $report))->assertOk()
                ->assertSee('Nama Pemilik')->assertSee('6281234567890')
                ->assertSee(route('laporan.history.photo', [$report, 0]))
                ->assertSee('Simpan perubahan')->assertHeader('Cache-Control', 'no-store, private');
            $this->patchJson(route('laporan.history.update', $report), [
                ...$this->fields(), 'status_laporan' => 'selesai', 'jenis_laporan' => 'palsu',
                'browser_history_hash' => str_repeat('a', 64), 'id_admin_pemeriksa' => 999,
                'kode_laporan' => 'TA-FAKE', 'ditarik_pada' => now()->toDateTimeString(),
            ])->assertOk()->assertExactJson(['redirect' => route('laporan.history')]);
            $report->refresh();
            $this->assertSame('menunggu_pemeriksaan', $report->status_laporan);
            $this->assertSame($type, $report->jenis_laporan);
            $this->assertSame('6281211112222', $report->kontak_pelapor);
            $this->assertSame(hash('sha256', $this->token), $report->browser_history_hash);
            $this->assertNull($report->dipublikasikan_pada);
            $this->assertNull($report->id_admin_pemeriksa);
            $this->assertNull($report->ditarik_pada);
            $this->assertSame(['laporan/original.webp'], $report->foto_barang);
            Storage::disk('local')->assertExists('laporan/original.webp');
            $this->get(route('laporan.show', $report))->assertNotFound();
            $this->get(route('laporan.history'))->assertSee($report->kode_laporan)->assertSee('Edit laporan');
        }
    }

    public function test_failed_edit_preserves_data_and_successful_photo_replacement_removes_old_file(): void
    {
        $report = $this->report();
        $this->patchJson(route('laporan.history.update', $report), [
            ...$this->fields(), 'kontak_pelapor' => '08232323',
            'foto_barang' => [UploadedFile::fake()->image('new.png')],
        ])->assertUnprocessable()->assertJsonValidationErrors('kontak_pelapor');
        $this->assertSame('dipublikasikan', $report->fresh()->status_laporan);
        $this->assertSame(['laporan/original.webp'], Storage::disk('local')->allFiles());
        $this->patchJson(route('laporan.history.update', $report), [
            ...$this->fields(), 'foto_barang' => [UploadedFile::fake()->createWithContent('bad.jpg', 'bad')],
        ])->assertUnprocessable()->assertJsonValidationErrors('foto_barang.0');
        Storage::disk('local')->assertExists('laporan/original.webp');

        $this->post(route('laporan.history.update', $report), [
            ...$this->fields(), '_method' => 'PATCH',
            'foto_barang' => [UploadedFile::fake()->image('new.png')],
        ], ['Accept' => 'application/json'])->assertOk();
        $report->refresh();
        $this->assertCount(1, $report->foto_barang);
        Storage::disk('local')->assertMissing('laporan/original.webp');
        Storage::disk('local')->assertExists($report->foto_barang[0]);
        $this->get(route('laporan.history.photo', [$report, 0]))->assertOk()->assertHeader('Content-Type', 'image/webp');
    }

    public function test_other_browser_cannot_read_edit_or_delete_even_with_owner_hash_in_input(): void
    {
        $report = $this->report();
        foreach (['', bin2hex(random_bytes(32))] as $cookie) {
            $this->withCookie(BrowserReportHistory::COOKIE, $cookie);
            $this->get(route('laporan.history.edit', $report))->assertNotFound()->assertDontSee('Nama Pemilik');
            $this->patchJson(route('laporan.history.update', $report), [
                ...$this->fields(), 'browser_history_hash' => $report->browser_history_hash,
            ])->assertNotFound();
            $this->delete(route('laporan.history.destroy', $report), ['konfirmasi' => '1'])->assertNotFound();
        }
        $this->assertSame('dipublikasikan', $report->fresh()->status_laporan);
        $this->assertNull($report->fresh()->ditarik_pada);
    }

    public function test_delete_requires_confirmation_hides_report_and_keeps_admin_archive(): void
    {
        $report = $this->report();
        $this->delete(route('laporan.history.destroy', $report))->assertSessionHasErrors('konfirmasi');
        $this->assertNull($report->fresh()->ditarik_pada);
        $this->delete(route('laporan.history.destroy', $report), ['konfirmasi' => '1'])
            ->assertRedirect(route('laporan.history'));
        $report->refresh();
        $this->assertNotNull($report->ditarik_pada);
        $this->get(route('laporan.history'))->assertDontSee($report->kode_laporan);
        $this->get(route('laporan.index', 'hilang'))->assertDontSee($report->kode_laporan);
        $this->get(route('laporan.show', $report))->assertNotFound();
        $this->get(route('laporan.confirmation', $report->kode_laporan))->assertNotFound();
        $this->get(route('foto.show', [$report, 0]))->assertNotFound();
        $this->get(route('laporan.history.photo', [$report, 0]))->assertNotFound();
        $this->patchJson(route('laporan.history.update', $report), $this->fields())->assertNotFound();
        $this->delete(route('laporan.history.destroy', $report), ['konfirmasi' => '1'])->assertNotFound();
        Storage::disk('local')->assertExists('laporan/original.webp');

        $admin = Admin::create(['nama_admin' => 'Petugas', 'nama_pengguna' => 'petugas-test', 'kata_sandi_hash' => bcrypt('test-password')]);
        $this->actingAs($admin, 'admin');
        $this->get(route('admin.index', ['jenis' => 'hilang', 'status' => 'ditarik']))->assertOk()->assertSee($report->kode_laporan);
        $this->get(route('admin.index', ['jenis' => 'hilang', 'status' => 'dipublikasikan']))->assertDontSee($report->kode_laporan);
        $this->get(route('admin.show', $report))->assertOk()->assertSee('ditarik oleh pelapor')->assertDontSee('Publikasikan laporan');
        $this->get(route('foto.show', [$report, 0]))->assertOk();
        $this->post(route('admin.review', $report), ['aksi' => 'terima'])->assertSessionHasErrors();
        $this->post(route('admin.unpublish', $report), ['konfirmasi' => '1'])->assertSessionHasErrors();
        $this->post(route('admin.complete', $report), [
            'nama_pihak' => 'Penemu', 'kontak_pihak' => '081234567890', 'lokasi_ditemukan' => 'Kantin',
        ])->assertSessionHasErrors();
        $this->assertDatabaseCount('penyelesaian_laporan', 0);
    }

    public function test_completed_report_cannot_be_edited_but_can_be_withdrawn(): void
    {
        $report = $this->report('selesai');
        $this->get(route('laporan.history.edit', $report))->assertForbidden();
        $this->patchJson(route('laporan.history.update', $report), $this->fields())
            ->assertUnprocessable()->assertJsonValidationErrors('deskripsi');
        $this->assertSame('Deskripsi asli barang pribadi', $report->fresh()->deskripsi);
        $this->delete(route('laporan.history.destroy', $report), ['konfirmasi' => '1'])->assertRedirect();
        $this->assertSame('selesai', $report->fresh()->status_laporan);
    }

    public function test_edit_and_delete_require_csrf_even_with_valid_cookie(): void
    {
        $this->app->bind(ValidateCsrfToken::class, HistoryCsrfToken::class);
        $report = $this->report();
        $this->patchJson(route('laporan.history.update', $report), $this->fields())->assertStatus(419);
        $this->deleteJson(route('laporan.history.destroy', $report), ['konfirmasi' => '1'])->assertStatus(419);
        $this->withSession(['_token' => 'history-csrf'])->delete(route('laporan.history.destroy', $report), [
            'konfirmasi' => '1', '_token' => 'history-csrf',
        ])->assertRedirect(route('laporan.history'));
    }

    public function test_management_requests_are_rate_limited(): void
    {
        $report = $this->report();
        for ($i = 0; $i < 15; $i++) {
            $this->deleteJson(route('laporan.history.destroy', $report))->assertUnprocessable();
        }
        $this->deleteJson(route('laporan.history.destroy', $report), ['konfirmasi' => '1'])->assertStatus(429);
        $this->assertNull($report->fresh()->ditarik_pada);
    }
}

class HistoryCsrfToken extends ValidateCsrfToken
{
    protected function runningUnitTests()
    {
        return false;
    }
}
