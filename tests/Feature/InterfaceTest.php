<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Laporan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InterfaceTest extends TestCase
{
    use RefreshDatabase;

    private function report(array $attributes = []): Laporan
    {
        return Laporan::create(array_merge([
            'kode_laporan' => 'TA-'.strtoupper(bin2hex(random_bytes(6))),
            'jenis_laporan' => 'ditemukan',
            'nama_pelapor' => 'Nama Privat',
            'kontak_pelapor' => '6281298765432',
            'foto_barang' => ['laporan/foto.webp', 'laporan/foto-lain.webp'],
            'deskripsi' => 'Dompet cokelat dengan goresan kecil',
            'lokasi' => 'Kantin Gedung A',
            'tanggal_kejadian' => today(),
            'status_laporan' => 'dipublikasikan',
            'dipublikasikan_pada' => now(),
            'catatan_admin' => 'Catatan privat',
        ], $attributes));
    }

    public function test_catalog_and_detail_use_sample_art_but_keep_real_report_photos(): void
    {
        $sample = $this->report(['kode_laporan' => 'TA-SAMPLE-DOMPET']);
        $real = $this->report();

        $this->get('/barang/ditemukan')->assertOk()
            ->assertSee('data-artwork="wallet"', false)
            ->assertSee(route('foto.show', [$real, 0]), false)
            ->assertSee(route('laporan.show', $sample), false);

        $this->get(route('laporan.show', $sample))->assertOk()
            ->assertSee('Ilustrasi contoh')
            ->assertSee('bukan barang yang bisa diklaim');

        $this->get(route('laporan.show', $real))->assertOk()
            ->assertSee(route('foto.show', [$real, 1]), false)
            ->assertSee('Siapkan bukti kepemilikan')
            ->assertDontSee('6281298765432')
            ->assertDontSee('Nama Privat')
            ->assertDontSee('Catatan privat');
    }

    public function test_completed_report_does_not_offer_another_claim(): void
    {
        foreach (['ditemukan' => 'Barang sudah diambil', 'hilang' => 'Barang sudah ditemukan'] as $type => $heading) {
            $report = $this->report(['jenis_laporan' => $type, 'status_laporan' => 'selesai']);
            $this->get(route('laporan.show', $report))->assertOk()
                ->assertSee($heading)
                ->assertDontSee('detail-contact-action', false)
                ->assertDontSee('Siapkan bukti kepemilikan');
        }
    }

    public function test_detail_chat_targets_staff_and_includes_the_report_context(): void
    {
        config(['temuin.whatsapp' => '6281112223334']);
        foreach (['hilang', 'ditemukan'] as $type) {
            $report = $this->report(['jenis_laporan' => $type]);
            $response = $this->get(route('laporan.show', $report))->assertOk()
                ->assertSee($type === 'hilang' ? 'Saya menemukan barang ini' : 'Ini sepertinya barang saya')
                ->assertDontSee($report->kontak_pelapor);

            preg_match('/class="button accent detail-contact-action"\s+href="([^"]+)"/', $response->getContent(), $matches);
            $this->assertArrayHasKey(1, $matches);
            $url = html_entity_decode($matches[1]);
            $this->assertSame('wa.me', parse_url($url, PHP_URL_HOST));
            $this->assertSame('/6281112223334', parse_url($url, PHP_URL_PATH));
            parse_str(parse_url($url, PHP_URL_QUERY), $query);
            $this->assertStringContainsString($report->kode_laporan, $query['text']);
            $this->assertStringContainsString(route('laporan.show', $report), $query['text']);
            $this->assertStringContainsString($type === 'hilang' ? 'saya menemukan barang' : 'verifikasi kepemilikannya', $query['text']);
            $this->assertSame('dipublikasikan', $report->fresh()->status_laporan);

            $this->get(route('laporan.index', $type))->assertDontSee($report->kontak_pelapor);
            $this->get(route('laporan.confirmation', $report->kode_laporan))->assertDontSee($report->kontak_pelapor);
            $this->assertArrayNotHasKey('kontak_pelapor', $report->toArray());
        }
        $this->get('/')->assertDontSee('6281298765432');
    }

    public function test_detail_chat_is_hidden_for_sample_reports_and_missing_staff_number(): void
    {
        config(['temuin.whatsapp' => '6281112223334']);
        $sample = $this->report(['kode_laporan' => 'TA-SAMPLE-DOMPET']);
        $this->get(route('laporan.show', $sample))->assertOk()->assertDontSee('detail-contact-action', false);

        $report = $this->report(['kontak_pelapor' => null]);
        config(['temuin.whatsapp' => '']);
        $this->get(route('laporan.show', $report))->assertOk()
            ->assertDontSee('detail-contact-action', false)
            ->assertSee(config('temuin.address'));
    }

    public function test_report_entry_offers_both_types_before_opening_a_form(): void
    {
        $this->get('/')->assertOk()->assertSee('href="'.route('laporan.choose').'"', false);
        $this->get(route('laporan.choose'))->assertOk()
            ->assertSee('Saya kehilangan barang')
            ->assertSee('Saya menemukan barang')
            ->assertSee('href="'.route('laporan.create', 'hilang').'"', false)
            ->assertSee('href="'.route('laporan.create', 'ditemukan').'"', false);
        $this->assertDatabaseCount('laporan', 0);
    }

    public function test_form_validation_preserves_input_for_both_report_types(): void
    {
        foreach (['hilang', 'ditemukan'] as $type) {
            $url = route('laporan.create', $type);
            $this->get($url)->assertOk()->assertSee('multipart/form-data', false)
                ->assertSee('action="'.route('laporan.store', $type).'"', false)
                ->assertSee('Jenis laporan')
                ->assertSee('name="kontak_pelapor"', false)
                ->assertSee('inputmode="numeric" pattern="[0-9]+" data-phone-digits', false)
                ->assertSee('href="'.route('laporan.create', 'hilang').'"', false)
                ->assertSee('href="'.route('laporan.create', 'ditemukan').'"', false);
            $this->from($url)->post(route('laporan.store', $type), [
                'nama_pelapor' => 'Penguji Formulir',
                'kontak_pelapor' => '081298765432',
                'deskripsi' => 'Dompet kulit dengan tanda kecil',
                'lokasi' => 'Kantin',
                'tanggal_kejadian' => today()->toDateString(),
            ])->assertRedirect($url)->assertSessionHasErrors('foto_barang');
            $this->get($url)->assertOk()
                ->assertSee('Penguji Formulir')
                ->assertSee('081298765432')
                ->assertSee('Pilih ulang foto');
        }
        $this->assertDatabaseCount('laporan', 0);
    }

    public function test_confirmation_displays_actual_status_without_private_fields(): void
    {
        $report = $this->report(['status_laporan' => 'menunggu_pemeriksaan', 'dipublikasikan_pada' => null]);
        $this->get(route('laporan.confirmation', $report->kode_laporan))->assertOk()
            ->assertSee($report->kode_laporan)
            ->assertSee('Menunggu pemeriksaan')
            ->assertDontSee('Nama Privat')
            ->assertDontSee('Catatan privat');
    }

    public function test_admin_views_cover_each_stage_and_preserve_filter_selection(): void
    {
        $admin = Admin::create([
            'nama_admin' => 'Petugas Pengujian',
            'nama_pengguna' => 'petugas-pengujian',
            'kata_sandi_hash' => bcrypt('password-pengujian'),
        ]);

        $this->get(route('admin.login'))->assertOk();
        $this->get(route('admin.index'))->assertRedirect(route('admin.login'));
        $this->actingAs($admin, 'admin');
        $this->get(route('admin.login'))->assertRedirect(route('admin.index'));

        foreach (['menunggu_pemeriksaan', 'menunggu_penyerahan', 'dipublikasikan', 'proses_klaim', 'selesai', 'ditolak'] as $status) {
            $report = $this->report(['status_laporan' => $status]);
            if ($status === 'proses_klaim') {
                foreach (['menunggu', 'disetujui'] as $claimStatus) {
                    $report->klaim()->create([
                        'nama_pengklaim' => 'Pengklaim Pengujian',
                        'bukti_kepemilikan' => 'Bukti privat',
                        'status_klaim' => $claimStatus,
                        'id_admin_pemeriksa' => $admin->id_admin,
                    ]);
                }
            }
            $this->get(route('admin.show', $report))->assertOk()->assertSee($report->kode_laporan)
                ->assertSee('WhatsApp pelapor (privat)')
                ->assertSee('https://wa.me/6281298765432', false);
        }

        $legacy = $this->report(['kontak_pelapor' => null]);
        $this->get(route('admin.show', $legacy))->assertOk()->assertSee('Belum dicantumkan');

        $this->get(route('admin.index', ['jenis' => 'ditemukan', 'status' => 'selesai']))
            ->assertOk()
            ->assertSee('Barang ditemukan')
            ->assertSee('aria-label="Jenis laporan"', false)
            ->assertSee('aria-label="Tahap laporan"', false);
    }

    public function test_missing_page_uses_public_navigation_and_keeps_404_status(): void
    {
        $this->get('/halaman-tidak-ada')->assertNotFound()
            ->assertSee('Halaman belum ketemu')
            ->assertSee(route('home').'#cara-kerja', false);
    }
}
