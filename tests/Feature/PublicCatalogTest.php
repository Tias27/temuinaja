<?php

namespace Tests\Feature;

use App\Http\Controllers\PublicLaporanController;
use App\Models\Laporan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class PublicCatalogTest extends TestCase
{
    use RefreshDatabase;

    private function laporan(array $attributes = []): Laporan
    {
        return Laporan::create(array_merge([
            'kode_laporan' => 'TA-'.strtoupper(bin2hex(random_bytes(6))),
            'jenis_laporan' => 'ditemukan',
            'nama_pelapor' => 'Nama Penemu Rahasia',
            'foto_barang' => ['laporan/test.webp'],
            'deskripsi' => 'Dompet kulit warna cokelat',
            'lokasi' => 'Gedung Akademik',
            'tanggal_kejadian' => now()->toDateString(),
            'status_laporan' => 'dipublikasikan',
            'dipublikasikan_pada' => now(),
            'catatan_admin' => 'Rahasia internal',
        ], $attributes));
    }

    public function test_only_published_reports_appear_and_private_data_stays_hidden(): void
    {
        $this->laporan();
        $this->laporan(['status_laporan' => 'menunggu_pemeriksaan', 'dipublikasikan_pada' => null, 'deskripsi' => 'Laporan yang ditunda']);
        $this->get('/barang/ditemukan')->assertOk()
            ->assertSee('Dompet kulit warna cokelat')
            ->assertDontSee('Laporan yang ditunda')
            ->assertDontSee('Nama Penemu Rahasia')
            ->assertDontSee('Rahasia internal');
    }

    public function test_home_uses_new_design_and_only_shows_published_found_reports(): void
    {
        $this->laporan();
        $this->laporan(['status_laporan' => 'menunggu_pemeriksaan', 'dipublikasikan_pada' => null, 'deskripsi' => 'Laporan yang ditunda']);
        $this->laporan(['jenis_laporan' => 'hilang', 'deskripsi' => 'Barang hilang lain']);

        $this->get('/')->assertOk()
            ->assertSee('Barangkali ini yang kamu cari')
            ->assertSee('Dompet kulit warna cokelat')
            ->assertDontSee('Laporan yang ditunda')
            ->assertDontSee('Barang hilang lain')
            ->assertDontSee('Nama Penemu Rahasia')
            ->assertDontSee('Rahasia internal')
            ->assertDontSee('Beranda aktif belum berubah');
    }

    public function test_mockup_only_shows_published_found_reports(): void
    {
        Route::get('/catalog-mockup-test', [PublicLaporanController::class, 'mockup'])->name('mockup');
        $this->laporan();
        $this->laporan(['status_laporan' => 'menunggu_pemeriksaan', 'dipublikasikan_pada' => null, 'deskripsi' => 'Laporan yang ditunda']);
        $this->laporan(['jenis_laporan' => 'hilang', 'deskripsi' => 'Barang hilang lain']);

        $this->get('/catalog-mockup-test')->assertOk()
            ->assertSee('Dompet kulit warna cokelat')
            ->assertDontSee('Laporan yang ditunda')
            ->assertDontSee('Barang hilang lain')
            ->assertDontSee('Nama Penemu Rahasia')
            ->assertDontSee('Rahasia internal');
    }

    public function test_search_uses_path_and_treats_sql_characters_as_data(): void
    {
        $this->laporan();
        foreach (['Dompet' => true, '%' => false, "' OR 1=1" => false] as $search => $matches) {
            $response = $this->post('/barang/ditemukan/cari', ['q' => $search]);
            $response->assertRedirect();
            $path = parse_url($response->headers->get('Location'), PHP_URL_PATH);
            $this->assertStringNotContainsString('?', $response->headers->get('Location'));
            $result = $this->get($path)->assertOk();
            $matches ? $result->assertSee('Dompet kulit') : $result->assertDontSee('Dompet kulit');
        }
        $response = $this->post('/barang/hilang/cari', ['q' => 'Dompet']);
        $this->get(parse_url($response->headers->get('Location'), PHP_URL_PATH))->assertOk()->assertDontSee('Dompet kulit');
        $this->post('/barang/ditemukan/cari', ['q' => ['test']])->assertSessionHasErrors('q');
        $this->post('/barang/ditemukan/cari', ['q' => str_repeat('a', 101)])->assertSessionHasErrors('q');
        $this->get('/barang/ditemukan/cari/invalid!')->assertNotFound();
        $this->get('/barang/tidak-ada')->assertNotFound();
    }

    public function test_pagination_has_no_query_string(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->laporan();
        }
        $this->get('/barang/ditemukan')->assertOk()->assertSee('/barang/ditemukan/halaman/2', false)->assertDontSee('?page=');
        $this->get('/barang/ditemukan/halaman/2')->assertOk();
    }

    public function test_desktop_and_mobile_use_expected_page_sizes(): void
    {
        for ($i = 1; $i <= 9; $i++) {
            $this->laporan(['deskripsi' => "Barang nomor {$i}", 'dipublikasikan_pada' => now()->subSeconds($i)]);
        }

        $desktop = $this->get('/barang/ditemukan')->assertOk();
        $desktop->assertSee('Barang nomor 1')->assertSee('Barang nomor 8')->assertDontSee('Barang nomor 9');
        $this->get('/')->assertOk()->assertSee('/halaman/2#laporan-terbaru', false)->assertDontSee('?page=');

        $mobileHeaders = ['User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) Mobile'];
        $mobile = $this->withHeaders($mobileHeaders)->get('/barang/ditemukan')->assertOk();
        $mobile->assertSee('Barang nomor 1')->assertSee('Barang nomor 6')->assertDontSee('Barang nomor 7');
        $this->withHeaders($mobileHeaders)->get('/')->assertOk()->assertSee('/halaman/2#laporan-terbaru', false);
    }

    public function test_mobile_found_and_lost_catalogs_paginate_six_items_including_search(): void
    {
        foreach (['ditemukan', 'hilang'] as $type) {
            for ($i = 1; $i <= 8; $i++) {
                $this->laporan([
                    'jenis_laporan' => $type,
                    'deskripsi' => "Barang {$type} nomor {$i}",
                    'dipublikasikan_pada' => now()->subSeconds($i),
                ]);
            }
        }

        $this->withHeaders(['User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) Mobile']);
        foreach (['ditemukan', 'hilang'] as $type) {
            $this->get(route('laporan.index', $type))->assertOk()
                ->assertViewHas('laporan', fn ($items) => $items->count() === 6 && $items->perPage() === 6)
                ->assertSee("Barang {$type} nomor 6")
                ->assertDontSee("Barang {$type} nomor 7")
                ->assertSee(route('laporan.page', ['jenis' => $type, 'page' => 2]), false);

            $this->get(route('laporan.page', ['jenis' => $type, 'page' => 2]))->assertOk()
                ->assertViewHas('laporan', fn ($items) => $items->count() === 2)
                ->assertSee("Barang {$type} nomor 7")
                ->assertSee("Barang {$type} nomor 8")
                ->assertDontSee("Barang {$type} nomor 1");

            $term = rtrim(strtr(base64_encode("Barang {$type}"), '+/', '-_'), '=');
            foreach ([1 => 6, 2 => 2] as $page => $count) {
                $this->get(route('laporan.search.index', compact('term', 'page') + ['jenis' => $type]))
                    ->assertOk()->assertViewHas('laporan', fn ($items) => $items->count() === $count);
            }
        }
    }

    public function test_private_reports_cannot_be_opened_or_have_their_photos_fetched(): void
    {
        $laporan = $this->laporan(['status_laporan' => 'menunggu_pemeriksaan', 'dipublikasikan_pada' => null]);
        $this->get(route('laporan.show', $laporan))->assertNotFound();
        $this->get(route('foto.show', [$laporan, 0]))->assertNotFound();
    }
}
