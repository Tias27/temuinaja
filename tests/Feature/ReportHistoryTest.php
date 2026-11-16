<?php

namespace Tests\Feature;

use App\Http\Middleware\BrowserReportHistory;
use App\Models\Laporan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReportHistoryTest extends TestCase
{
    use RefreshDatabase;

    private function report(?string $token, string $status = 'menunggu_pemeriksaan'): Laporan
    {
        return Laporan::create([
            'kode_laporan' => 'TA-'.strtoupper(bin2hex(random_bytes(6))),
            'jenis_laporan' => 'hilang', 'nama_pelapor' => 'Nama Privat',
            'kontak_pelapor' => '6281234567890', 'foto_barang' => [],
            'deskripsi' => 'Dompet untuk riwayat pribadi', 'lokasi' => 'Kantin',
            'tanggal_kejadian' => today(), 'status_laporan' => $status,
            'catatan_admin' => 'Catatan rahasia petugas',
            'browser_history_hash' => $token ? hash('sha256', $token) : null,
        ]);
    }

    public function test_history_is_scoped_to_cookie_and_survives_public_expiry(): void
    {
        $token = bin2hex(random_bytes(32));
        $mine = [];
        foreach (['menunggu_pemeriksaan', 'dipublikasikan', 'ditolak', 'selesai'] as $status) {
            $mine[] = $this->report($token, $status);
        }
        $other = $this->report(bin2hex(random_bytes(32)));
        $legacy = $this->report(null);
        $this->withCookie(BrowserReportHistory::COOKIE, $token);
        $response = $this->get(route('laporan.history', ['browser_history_hash' => $other->browser_history_hash]))
            ->assertOk()->assertHeader('Cache-Control', 'no-store, private')
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
            ->assertDontSee($other->kode_laporan)->assertDontSee($legacy->kode_laporan)
            ->assertDontSee('Catatan rahasia petugas')->assertDontSee('Nama Privat')
            ->assertDontSee('6281234567890')->assertDontSee($token)->assertDontSee(hash('sha256', $token));
        foreach ($mine as $report) {
            $response->assertSee($report->kode_laporan);
        }
        $this->get(route('laporan.show', $mine[3]))->assertNotFound();
        $this->assertArrayNotHasKey('browser_history_hash', $mine[0]->toArray());

        $this->withCookie(BrowserReportHistory::COOKIE, '');
        $empty = $this->get(route('laporan.history'))->assertOk()->assertSee('Belum ada riwayat');
        foreach ($mine as $report) {
            $empty->assertDontSee($report->kode_laporan);
        }
    }

    public function test_cookie_is_encrypted_http_only_secure_on_https_and_tampering_does_not_grant_access(): void
    {
        config(['app.url' => 'https://localhost']);
        $response = $this->get('https://localhost/lapor/riwayat')->assertOk();
        $cookie = $response->getCookie(BrowserReportHistory::COOKIE);
        $this->assertTrue($cookie->isHttpOnly());
        $this->assertTrue($cookie->isSecure());
        $this->assertSame('lax', $cookie->getSameSite());
        $this->assertNull($cookie->getDomain());
        $this->assertGreaterThan(now()->addMonths(11)->timestamp, $cookie->getExpiresTime());
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/D', $cookie->getValue());
        $this->assertNotSame($cookie->getValue(), $response->getCookie(BrowserReportHistory::COOKIE, false)->getValue());
        $report = $this->report($cookie->getValue());

        $this->withUnencryptedCookie(BrowserReportHistory::COOKIE, $cookie->getValue())
            ->get('https://localhost/lapor/riwayat')->assertOk()->assertDontSee($report->kode_laporan);
    }

    public function test_submissions_use_server_cookie_hash_and_keep_both_types_in_history(): void
    {
        Storage::fake('local');
        $token = $this->get(route('laporan.create', 'hilang'))->assertOk()
            ->getCookie(BrowserReportHistory::COOKIE)->getValue();
        $this->withCookie(BrowserReportHistory::COOKIE, $token);
        foreach (['hilang', 'ditemukan'] as $type) {
            $response = $this->post(route('laporan.store', $type), [
                'nama_pelapor' => 'Penguji', 'kontak_pelapor' => '081234567890',
                'deskripsi' => 'Dompet milik penguji riwayat', 'lokasi' => 'Kantin',
                'tanggal_kejadian' => today()->toDateString(),
                'foto_barang' => [UploadedFile::fake()->image('foto.png')],
                'browser_history_hash' => str_repeat('a', 64),
            ])->assertSessionHasNoErrors()->assertRedirect();
            $this->assertSame($token, $response->getCookie(BrowserReportHistory::COOKIE)->getValue());
        }
        $this->assertSame(2, Laporan::where('browser_history_hash', hash('sha256', $token))->count());
        $history = $this->get(route('laporan.history'))->assertOk();
        foreach (Laporan::all() as $report) {
            $history->assertSee($report->kode_laporan);
        }
    }

    public function test_history_paginates_and_escapes_user_content(): void
    {
        $token = bin2hex(random_bytes(32));
        for ($i = 0; $i < 11; $i++) {
            $this->report($token);
        }
        $report = Laporan::latest('id_laporan')->first();
        $report->update(['deskripsi' => '<script>alert(1)</script>']);
        $this->withCookie(BrowserReportHistory::COOKIE, $token);
        $this->get(route('laporan.history'))->assertOk()
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertViewHas('laporan', fn ($items) => $items->count() === 10);
        $this->withHeader('User-Agent', 'iPhone Mobile')->get(route('laporan.history'))->assertOk()
            ->assertViewHas('laporan', fn ($items) => $items->count() === 5);
        $this->get(route('laporan.history.page', ['page' => 3]))->assertOk()
            ->assertViewHas('laporan', fn ($items) => $items->count() === 1);
        $this->get(route('laporan.history.page', ['page' => 0]))->assertNotFound();
    }
}
