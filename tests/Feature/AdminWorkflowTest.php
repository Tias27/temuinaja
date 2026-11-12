<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Laporan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): Admin
    {
        return Admin::create([
            'nama_admin' => 'Tias Petugas',
            'nama_pengguna' => 'tias-pengujian',
            'kata_sandi_hash' => bcrypt('sandi-pengujian'),
        ]);
    }

    private function report(string $type, string $status = 'menunggu_pemeriksaan'): Laporan
    {
        return Laporan::create([
            'kode_laporan' => 'TA-'.strtoupper(bin2hex(random_bytes(6))),
            'jenis_laporan' => $type,
            'nama_pelapor' => 'Pelapor Rahasia',
            'kontak_pelapor' => '6281234567890',
            'foto_barang' => ['laporan/contoh.webp'],
            'deskripsi' => 'Dompet kulit cokelat dengan resleting kecil',
            'lokasi' => 'Gedung B',
            'tanggal_kejadian' => today(),
            'status_laporan' => $status,
            'dipublikasikan_pada' => in_array($status, ['dipublikasikan', 'proses_klaim', 'selesai']) ? now() : null,
        ]);
    }

    public function test_admin_list_separates_report_type_and_stage(): void
    {
        $this->actingAs($this->admin(), 'admin');
        $foundPending = $this->report('ditemukan');
        $lostPending = $this->report('hilang');
        $foundPublished = $this->report('ditemukan', 'dipublikasikan');

        $this->get(route('admin.index'))->assertOk()
            ->assertSee($foundPending->kode_laporan)
            ->assertDontSee($lostPending->kode_laporan)
            ->assertDontSee($foundPublished->kode_laporan);
        $this->get(route('admin.index', ['jenis' => 'hilang']))->assertOk()
            ->assertSee($lostPending->kode_laporan)
            ->assertDontSee($foundPending->kode_laporan);
        $this->get(route('admin.index', ['jenis' => 'ditemukan', 'status' => 'dipublikasikan']))
            ->assertOk()->assertSee($foundPublished->kode_laporan)
            ->assertDontSee($foundPending->kode_laporan);
    }

    public function test_admin_pagination_uses_ten_desktop_or_five_mobile_reports_and_keeps_filters(): void
    {
        $this->actingAs($this->admin(), 'admin');
        foreach (['hilang', 'ditemukan'] as $type) {
            for ($i = 0; $i < 11; $i++) {
                $this->report($type, 'dipublikasikan');
            }
            $this->report($type);
        }

        foreach (['Mozilla/5.0 (Windows NT 10.0; Win64; x64)' => 10, 'Mozilla/5.0 (iPhone) Mobile' => 5] as $agent => $perPage) {
            $this->withHeaders(['User-Agent' => $agent]);
            foreach (['hilang', 'ditemukan'] as $type) {
                $url = route('admin.index', ['jenis' => $type, 'status' => 'dipublikasikan']);
                $seen = [];
                $page = 1;
                do {
                    $response = $this->get($url)->assertOk();
                    $items = $response->viewData('laporan');
                    $this->assertSame($perPage, $items->perPage());
                    $this->assertSame(min($perPage, 11 - count($seen)), $items->count());
                    $this->assertSame($page++, $items->currentPage());
                    foreach ($items as $item) {
                        $this->assertSame($type, $item->jenis_laporan);
                        $this->assertSame('dipublikasikan', $item->status_laporan);
                        $this->assertNotContains($item->id_laporan, $seen);
                        $seen[] = $item->id_laporan;
                    }
                    $url = $items->nextPageUrl();
                    if ($url) {
                        $response->assertSee($url)->assertSee('Berikutnya');
                    } else {
                        $response->assertDontSee('Berikutnya');
                    }
                } while ($url);
                $this->assertCount(11, $seen);
            }
        }
    }

    public function test_review_publishes_both_types_immediately_and_rejects_unwanted_report(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin, 'admin');
        foreach (['hilang', 'ditemukan'] as $type) {
            $report = $this->report($type);
            $this->post(route('admin.review', $report), ['aksi' => 'terima'])->assertRedirect();
            $report->refresh();
            $this->assertSame('dipublikasikan', $report->status_laporan);
            $this->assertNotNull($report->dipublikasikan_pada);
            $this->assertSame($admin->id_admin, $report->id_admin_pemeriksa);
            $this->assertDatabaseCount('penyerahan_barang', 0);
            $this->get(route('laporan.index', $type))->assertSee($report->deskripsi);
            $this->post(route('admin.review', $report), ['aksi' => 'terima'])
                ->assertSessionHasErrors();
        }

        $rejected = $this->report('hilang');
        $this->post(route('admin.review', $rejected), [
            'aksi' => 'tolak', 'catatan_admin' => 'Foto tidak menunjukkan barang',
        ])->assertRedirect();
        $this->assertSame('ditolak', $rejected->fresh()->status_laporan);
        $this->assertNull($rejected->fresh()->dipublikasikan_pada);
        $this->get(route('laporan.index', 'hilang'))->assertDontSee($rejected->kode_laporan);
    }

    public function test_admin_can_remove_publication_without_deleting_report_and_publish_it_again(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin, 'admin');

        foreach (['hilang', 'ditemukan'] as $type) {
            $report = $this->report($type, 'dipublikasikan');
            $this->get(route('admin.show', $report))->assertOk()
                ->assertSee('Hapus publikasi');
            $this->post(route('admin.unpublish', $report), [])
                ->assertSessionHasErrors('konfirmasi');
            $this->assertSame('dipublikasikan', $report->fresh()->status_laporan);

            $this->post(route('admin.unpublish', $report), ['konfirmasi' => '1'])
                ->assertRedirect(route('admin.show', $report));
            $report->refresh();
            $this->assertSame('menunggu_pemeriksaan', $report->status_laporan);
            $this->assertNull($report->dipublikasikan_pada);
            $this->assertDatabaseHas('laporan', ['id_laporan' => $report->id_laporan]);
            $this->get(route('laporan.index', $type))->assertDontSee($report->kode_laporan);
            $this->get(route('laporan.show', $report))->assertNotFound();
            $this->get(route('admin.show', $report))->assertOk()
                ->assertSee('Publikasikan laporan')->assertDontSee('Hapus publikasi');
            $this->post(route('admin.unpublish', $report), ['konfirmasi' => '1'])
                ->assertSessionHasErrors();

            $this->post(route('admin.review', $report), ['aksi' => 'terima'])
                ->assertRedirect();
            $this->assertSame('dipublikasikan', $report->fresh()->status_laporan);
            $this->get(route('laporan.index', $type))->assertSee($report->kode_laporan);
        }
    }

    public function test_completed_report_cannot_have_its_publication_removed(): void
    {
        $this->actingAs($this->admin(), 'admin');
        $report = $this->report('ditemukan', 'selesai');

        $this->get(route('admin.show', $report))->assertOk()->assertDontSee('Hapus publikasi');
        $this->post(route('admin.unpublish', $report), ['konfirmasi' => '1'])
            ->assertSessionHasErrors();
        $this->assertSame('selesai', $report->fresh()->status_laporan);
    }

    public function test_lost_report_completion_records_finder_location_admin_and_time_once(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin, 'admin');
        $report = $this->report('hilang', 'dipublikasikan');

        $this->get(route('admin.show', $report))->assertOk()
            ->assertSee('Barang sudah ditemukan?')
            ->assertSee('Nama penemu')
            ->assertDontSee('Catatan verifikasi kepemilikan');
        $this->post(route('admin.complete', $report), [
            'nama_pihak' => 'Dina Penemu',
            'kontak_pihak' => '0812-1111-2222',
            'lokasi_ditemukan' => 'Kantin Gedung A',
        ])->assertRedirect();

        $report->refresh();
        $completion = $report->penyelesaian;
        $this->assertSame('selesai', $report->status_laporan);
        $this->assertSame('ditemukan', $completion->jenis_penyelesaian);
        $this->assertSame('6281211112222', $completion->kontak_pihak);
        $this->assertSame('Kantin Gedung A', $completion->lokasi_ditemukan);
        $this->assertSame($admin->id_admin, $completion->id_admin);
        $this->assertNotNull($completion->diselesaikan_pada);
        $this->get(route('admin.show', $report))->assertOk()
            ->assertSee('Dina Penemu')->assertSee('Tias Petugas')
            ->assertSee('Tanggal penyelesaian')->assertSee($report->kode_laporan);
        $this->get(route('laporan.show', $report))->assertOk()
            ->assertDontSee('Dina Penemu')->assertDontSee('6281211112222')
            ->assertDontSee('Saya menemukan barang ini');
        $this->post(route('admin.complete', $report), [
            'nama_pihak' => 'Orang Lain', 'kontak_pihak' => '081211112222',
            'lokasi_ditemukan' => 'Gedung B',
        ])->assertSessionHasErrors();
        $this->assertDatabaseCount('penyelesaian_laporan', 1);
    }

    public function test_found_report_requires_verification_note_before_completion(): void
    {
        $this->actingAs($this->admin(), 'admin');
        $report = $this->report('ditemukan', 'dipublikasikan');

        $this->get(route('admin.show', $report))->assertOk()
            ->assertSee('Barang sudah diambil?')
            ->assertSee('Catatan verifikasi kepemilikan');
        $payload = ['nama_pihak' => 'Budi Pemilik', 'kontak_pihak' => '0812 3333 4444'];
        $this->post(route('admin.complete', $report), $payload)
            ->assertSessionHasErrors('catatan_verifikasi');
        $this->assertSame('dipublikasikan', $report->fresh()->status_laporan);

        $this->post(route('admin.complete', $report), [
            ...$payload, 'catatan_verifikasi' => 'Warna dan isi dompet cocok dengan bukti pemilik',
        ])->assertRedirect();
        $report->refresh();
        $this->assertSame('selesai', $report->status_laporan);
        $this->assertSame('diambil', $report->penyelesaian->jenis_penyelesaian);
        $this->assertSame('6281233334444', $report->penyelesaian->kontak_pihak);
        $this->get(route('admin.show', $report))->assertOk()
            ->assertSee('Budi Pemilik')
            ->assertSee('Warna dan isi dompet cocok');
        $this->get(route('laporan.show', $report))->assertOk()
            ->assertDontSee('Budi Pemilik')->assertDontSee('6281233334444');
    }

    public function test_legacy_stages_remain_actionable_and_old_history_remains_visible(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin, 'admin');
        $waiting = $this->report('ditemukan', 'menunggu_penyerahan');
        $published = $this->report('ditemukan', 'proses_klaim');
        $published->klaim()->create([
            'nama_pengklaim' => 'Pengklaim Lama',
            'bukti_kepemilikan' => 'Ciri khusus barang',
            'status_klaim' => 'menunggu',
            'id_admin_pemeriksa' => $admin->id_admin,
        ]);

        $this->get(route('admin.index'))->assertSee($waiting->kode_laporan)
            ->assertDontSee($published->kode_laporan);
        $this->get(route('admin.index', ['status' => 'dipublikasikan']))
            ->assertSee($published->kode_laporan);
        $this->get(route('admin.show', $published))->assertOk()
            ->assertSee('Riwayat proses sebelumnya')->assertSee('Pengklaim Lama')
            ->assertSee('Selesaikan laporan');
        $this->post(route('admin.review', $waiting), ['aksi' => 'terima'])->assertRedirect();
        $this->assertSame('dipublikasikan', $waiting->fresh()->status_laporan);
        $this->assertDatabaseCount('klaim_barang', 1);
    }

    public function test_short_contact_explains_length_and_full_number_can_complete_report(): void
    {
        $this->actingAs($this->admin(), 'admin');
        $report = $this->report('ditemukan', 'dipublikasikan');
        $payload = [
            'nama_pihak' => 'Penerima Pengujian',
            'kontak_pihak' => '08565656',
            'catatan_verifikasi' => 'Ciri barang cocok dengan bukti penerima',
        ];

        $this->post(route('admin.complete', $report), $payload)
            ->assertSessionHasErrors([
                'kontak_pihak' => 'Nomor terlalu pendek: baru 8 digit, minimal 9 digit untuk awalan 08. Isi nomor WhatsApp lengkap',
            ]);
        $this->assertSame('dipublikasikan', $report->fresh()->status_laporan);
        $this->assertDatabaseCount('penyelesaian_laporan', 0);

        $this->post(route('admin.complete', $report), [
            ...$payload, 'kontak_pihak' => '0856 5656 1234',
        ])->assertSessionHasNoErrors();
        $this->assertSame('selesai', $report->fresh()->status_laporan);
        $this->assertSame('6285656561234', $report->fresh()->penyelesaian->kontak_pihak);
    }

    public function test_guest_cannot_publish_or_complete_reports(): void
    {
        $pending = $this->report('hilang');
        $published = $this->report('hilang', 'dipublikasikan');
        $this->post(route('admin.review', $pending), ['aksi' => 'terima'])
            ->assertRedirect(route('admin.login'));
        $this->post(route('admin.complete', $published), [
            'nama_pihak' => 'Penemu', 'kontak_pihak' => '081234567890',
            'lokasi_ditemukan' => 'Gedung B',
        ])->assertRedirect(route('admin.login'));
        $this->post(route('admin.unpublish', $published), ['konfirmasi' => '1'])
            ->assertRedirect(route('admin.login'));
        $this->assertSame('menunggu_pemeriksaan', $pending->fresh()->status_laporan);
        $this->assertSame('dipublikasikan', $published->fresh()->status_laporan);
    }
}
