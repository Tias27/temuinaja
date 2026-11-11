<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Laporan;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): Admin
    {
        return Admin::create([
            'nama_admin' => 'Petugas Audit', 'nama_pengguna' => 'security-audit',
            'kata_sandi_hash' => bcrypt('Password-Audit-12345'),
        ]);
    }

    private function report(array $attributes = []): Laporan
    {
        return Laporan::create([
            'kode_laporan' => 'TA-'.strtoupper(bin2hex(random_bytes(6))),
            'jenis_laporan' => 'ditemukan', 'nama_pelapor' => 'Nama Privat',
            'kontak_pelapor' => '6281234567890', 'foto_barang' => ['laporan/test.webp'],
            'deskripsi' => 'Dompet kulit dengan resleting', 'lokasi' => 'Kantin',
            'tanggal_kejadian' => today(), 'status_laporan' => 'dipublikasikan',
            'dipublikasikan_pada' => now(), 'catatan_admin' => 'Catatan Privat',
            ...$attributes,
        ]);
    }

    private function fields(): array
    {
        return [
            'nama_pelapor' => 'Pelapor Audit', 'kontak_pelapor' => '081234567890',
            'deskripsi' => 'Dompet cokelat dengan resleting', 'lokasi' => 'Kantin',
            'tanggal_kejadian' => today()->toDateString(),
        ];
    }

    public function test_guest_cannot_read_or_modify_admin_reports_including_head_requests(): void
    {
        $report = $this->report(['status_laporan' => 'menunggu_pemeriksaan', 'dipublikasikan_pada' => null]);
        foreach ([route('admin.index'), route('admin.show', $report)] as $url) {
            $this->get($url)->assertRedirect(route('admin.login'))->assertDontSee('Nama Privat');
            $this->head($url)->assertRedirect(route('admin.login'));
        }
        foreach (['admin.review', 'admin.complete'] as $route) {
            $this->post(route($route, $report), ['aksi' => 'terima', 'id_admin' => 1])
                ->assertRedirect(route('admin.login'));
            $this->postJson(route($route, $report), [])->assertUnauthorized();
            $this->get(route($route, $report))->assertStatus(405);
        }
        $this->get(route('laporan.show', $report))->assertNotFound();
        $this->get(route('foto.show', [$report, 0]))->assertNotFound();
        $this->assertSame('menunggu_pemeriksaan', $report->fresh()->status_laporan);
    }

    public function test_csrf_is_enforced_for_login_submission_and_admin_actions(): void
    {
        $this->app->bind(ValidateCsrfToken::class, EnforcedCsrfToken::class);
        $report = $this->report();
        foreach ([route('admin.login.submit'), route('laporan.store', 'hilang')] as $url) {
            $this->post($url, [])->assertStatus(419);
            $this->postJson($url, [])->assertStatus(419);
        }
        $this->actingAs($this->admin(), 'admin');
        foreach (['admin.review', 'admin.complete'] as $route) {
            $this->post(route($route, $report), ['aksi' => 'terima'])->assertStatus(419);
        }
        $this->post(route('admin.logout'))->assertStatus(419);
        $this->withSession(['_token' => 'security-test-token'])
            ->post(route('admin.logout'), ['_token' => 'security-test-token'])
            ->assertRedirect(route('admin.login'));
        $this->assertGuest('admin');
        $this->assertDatabaseCount('laporan', 1);
    }

    public function test_login_rejects_sql_injection_and_limits_attempts_by_ip_and_account(): void
    {
        $this->admin();
        $this->post(route('admin.login.submit'), [
            'nama_pengguna' => "' OR 1=1 --", 'password' => 'wrong',
        ])->assertSessionHasErrors('nama_pengguna');
        $this->assertGuest('admin');
        for ($i = 0; $i < 4; $i++) {
            $this->post(route('admin.login.submit'), [
                'nama_pengguna' => 'security-audit', 'password' => 'wrong',
            ])->assertSessionHasErrors('nama_pengguna');
        }
        $this->post(route('admin.login.submit'), [
            'nama_pengguna' => 'security-audit', 'password' => 'Password-Audit-12345',
        ])->assertStatus(429);
        $this->assertGuest('admin');

        for ($i = 1; $i <= 6; $i++) {
            $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.'.$i])
                ->post(route('admin.login.submit'), [
                    'nama_pengguna' => 'security-audit', 'password' => 'wrong',
                ])->assertSessionHasErrors('nama_pengguna');
        }
        $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.50'])
            ->post(route('admin.login.submit'), [
                'nama_pengguna' => 'SECURITY-AUDIT', 'password' => 'wrong',
            ])->assertStatus(429);
    }

    public function test_login_regenerates_session_and_logout_revokes_access(): void
    {
        $this->admin();
        $this->get(route('admin.login'))->assertOk();
        $oldSession = session()->getId();
        $this->post(route('admin.login.submit'), [
            'nama_pengguna' => 'security-audit', 'password' => 'Password-Audit-12345',
        ])->assertRedirect(route('admin.index'));
        $this->assertAuthenticated('admin');
        $this->assertNotSame($oldSession, session()->getId());
        $this->get(route('admin.index'))->assertOk()->assertHeader('Cache-Control', 'no-store, private');
        $this->post(route('admin.logout'))->assertRedirect(route('admin.login'));
        $this->assertGuest('admin');
        $this->get(route('admin.index'))->assertRedirect(route('admin.login'));
    }

    public function test_stored_and_reflected_html_is_escaped_in_public_and_admin_views(): void
    {
        $payload = '<img src=x onerror=alert(1)>';
        $report = $this->report([
            'nama_pelapor' => $payload, 'deskripsi' => $payload,
            'lokasi' => $payload, 'catatan_admin' => $payload,
        ]);
        foreach (['/', route('laporan.index', 'ditemukan'), route('laporan.show', $report)] as $url) {
            $this->get($url)->assertOk()->assertSee(e($payload), false)->assertDontSee($payload, false);
        }
        $term = rtrim(strtr(base64_encode($payload), '+/', '-_'), '=');
        $this->get(route('laporan.search.index', ['jenis' => 'ditemukan', 'term' => $term]))
            ->assertOk()->assertSee(e($payload), false)->assertDontSee($payload, false);
        $this->actingAs($this->admin(), 'admin');
        $this->get(route('admin.show', $report))->assertOk()
            ->assertSee(e($payload), false)->assertDontSee($payload, false);
    }

    public function test_submission_cannot_set_status_admin_or_storage_path_and_reencodes_upload(): void
    {
        Storage::fake('local');
        $png = UploadedFile::fake()->image('image.png', 20, 20);
        $payload = file_get_contents($png->getRealPath()).'<?php echo "UNSAFE_UPLOAD"; ?>';
        $this->post(route('laporan.store', 'hilang'), [
            ...$this->fields(), 'status_laporan' => 'dipublikasikan',
            'id_admin_pemeriksa' => 100, 'dipublikasikan_pada' => now()->toDateTimeString(),
            'kode_laporan' => 'TA-SPOOFED', 'jenis_laporan' => 'ditemukan',
            'foto_barang' => [UploadedFile::fake()->createWithContent('../../payload.php.png', $payload)],
        ])->assertSessionHasNoErrors()->assertRedirect();
        $report = Laporan::firstOrFail();
        $this->assertSame('menunggu_pemeriksaan', $report->status_laporan);
        $this->assertSame('hilang', $report->jenis_laporan);
        $this->assertNull($report->id_admin_pemeriksa);
        $this->assertNull($report->dipublikasikan_pada);
        $this->assertNotSame('TA-SPOOFED', $report->kode_laporan);
        $path = $report->foto_barang[0];
        $this->assertMatchesRegularExpression('~^laporan/[a-f0-9-]{36}\.webp$~', $path);
        $contents = Storage::disk('local')->get($path);
        $this->assertSame('image/webp', getimagesizefromstring($contents)['mime']);
        $this->assertStringNotContainsString('UNSAFE_UPLOAD', $contents);
    }

    public function test_executable_svg_and_oversized_image_uploads_are_rejected(): void
    {
        Storage::fake('local');
        foreach ([
            UploadedFile::fake()->createWithContent('file.php', '<?php echo 1; ?>'),
            UploadedFile::fake()->createWithContent('file.svg', '<svg xmlns="http://www.w3.org/2000/svg" onload="alert(1)"></svg>'),
            UploadedFile::fake()->image('wide.png', 4097, 1),
        ] as $file) {
            $this->post(route('laporan.store', 'hilang'), [
                ...$this->fields(), 'foto_barang' => [$file],
            ])->assertSessionHasErrors('foto_barang.0');
        }
        $this->assertDatabaseCount('laporan', 0);
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_photo_endpoint_blocks_invalid_indexes_and_paths_even_for_admin(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('private-secret.webp', 'SECRET-CONTENT');
        $report = $this->report();
        foreach (['nope', '-1', '3', '99999999999999999999', '0.php'] as $index) {
            $this->get(route('foto.show', [$report, $index]))->assertNotFound();
        }
        $this->actingAs($this->admin(), 'admin');
        foreach (['laporan/../private-secret.webp', '../.env', 'private-secret.webp', 'php://filter/resource=.env', ['path']] as $path) {
            $report->update(['foto_barang' => [$path]]);
            $this->get(route('foto.show', [$report, 0]))->assertNotFound()->assertDontSee('SECRET-CONTENT');
        }
    }

    public function test_host_header_injection_is_blocked_and_forwarded_host_is_not_trusted(): void
    {
        $this->get('http://attacker.invalid/')->assertStatus(400);
        $this->withHeaders(['X-Forwarded-Host' => 'attacker.invalid'])
            ->get('http://localhost/')->assertOk()->assertDontSee('attacker.invalid');
    }

    public function test_html_has_security_headers_and_https_sessions_have_secure_cookie(): void
    {
        $this->get('/')->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('Referrer-Policy', 'same-origin');
        $response = $this->get('/admin/login')->assertOk();
        $policy = $response->headers->get('Content-Security-Policy');
        $this->assertStringContainsString("frame-ancestors 'none'", $policy);
        $this->assertStringContainsString("object-src 'none'", $policy);
        $this->assertStringNotContainsString("'unsafe-eval'", $policy);
        $response = $this->get('https://localhost/admin/login')->assertOk()
            ->assertHeader('Strict-Transport-Security', 'max-age=31536000');
        $cookie = collect($response->headers->getCookies())->first(fn ($cookie) => $cookie->getName() === config('session.cookie'));
        $this->assertNotNull($cookie);
        $this->assertTrue($cookie->isSecure());
        $this->assertTrue($cookie->isHttpOnly());
        $this->assertSame('lax', $cookie->getSameSite());
    }

    public function test_confirmation_does_not_disclose_private_data_and_admin_page_input_is_bounded(): void
    {
        $report = $this->report(['status_laporan' => 'ditolak', 'dipublikasikan_pada' => null]);
        $this->get(route('laporan.confirmation', $report->kode_laporan))->assertOk()
            ->assertDontSee('Nama Privat')->assertDontSee('6281234567890')
            ->assertDontSee('Catatan Privat')->assertDontSee($report->deskripsi);
        $this->get(route('laporan.show', $report))->assertNotFound();
        $this->actingAs($this->admin(), 'admin');
        foreach (['page[]=1', 'page=-1', 'page=999999999999', 'page=1%20OR%201=1'] as $query) {
            $this->get('/admin?'.$query)->assertNotFound();
        }
    }

    public function test_password_command_updates_only_target_account_and_revokes_its_sessions(): void
    {
        config(['session.driver' => 'database']);
        $admin = $this->admin();
        $other = Admin::create([
            'nama_admin' => 'Admin Lain', 'nama_pengguna' => 'other-admin',
            'kata_sandi_hash' => bcrypt('Other-Password-12345'),
        ]);
        foreach ([$admin, $other] as $account) {
            DB::table('sessions')->insert([
                'id' => 'session-'.$account->id_admin, 'user_id' => $account->id_admin,
                'payload' => '', 'last_activity' => time(),
            ]);
        }
        $this->artisan('admin:password', ['username' => 'security-audit'])
            ->expectsQuestion('Kata sandi baru (minimal 12 karakter)', 'Updated-Password-12345')
            ->expectsQuestion('Ulangi kata sandi baru', 'Updated-Password-12345')
            ->assertSuccessful();
        $this->assertTrue(Hash::check('Updated-Password-12345', $admin->fresh()->getAuthPassword()));
        $this->assertTrue(Hash::check('Other-Password-12345', $other->fresh()->getAuthPassword()));
        $this->assertDatabaseMissing('sessions', ['user_id' => $admin->id_admin]);
        $this->assertDatabaseHas('sessions', ['user_id' => $other->id_admin]);
    }

    public function test_password_rehash_updates_custom_password_column(): void
    {
        $admin = $this->admin();
        Hash::driver()->setRounds(5);
        $this->post(route('admin.login.submit'), [
            'nama_pengguna' => 'security-audit', 'password' => 'Password-Audit-12345',
        ])->assertRedirect(route('admin.index'));
        $this->assertFalse(Hash::needsRehash($admin->fresh()->getAuthPassword()));
        $this->assertAuthenticatedAs($admin, 'admin');
    }

    public function test_public_browsing_does_not_consume_report_submission_limit(): void
    {
        Storage::fake('local');
        $report = $this->report();
        Storage::disk('local')->put('laporan/test.webp', 'photo');
        for ($i = 0; $i < 16; $i++) {
            $this->get(route('foto.show', [$report, 0]))->assertOk();
        }
        $this->post(route('laporan.store', 'hilang'), [
            ...$this->fields(), 'foto_barang' => [UploadedFile::fake()->image('photo.png')],
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertDatabaseCount('laporan', 2);
        for ($i = 0; $i < 14; $i++) {
            $this->postJson(route('laporan.store', 'hilang'), [])->assertUnprocessable();
        }
        $this->postJson(route('laporan.store', 'hilang'), [])->assertStatus(429);
        $this->get(route('laporan.index', 'ditemukan'))->assertOk();
    }
}

class EnforcedCsrfToken extends ValidateCsrfToken
{
    protected function runningUnitTests()
    {
        return false;
    }
}
