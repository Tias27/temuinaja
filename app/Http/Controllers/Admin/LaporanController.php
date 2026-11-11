<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompleteLaporanRequest;
use App\Models\Laporan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $page = $request->query('page', '1');
        abort_unless(is_scalar($page) && preg_match('/^[1-9][0-9]{0,4}$/D', (string) $page) && (int) $page <= 10000, 404);
        $jenis = in_array($request->query('jenis'), ['ditemukan', 'hilang'], true)
            ? $request->query('jenis')
            : 'ditemukan';
        $status = in_array($request->query('status'), ['semua', 'menunggu', 'dipublikasikan', 'selesai', 'ditolak'], true)
            ? $request->query('status')
            : 'menunggu';
        $perPage = preg_match('/Android|iPhone|iPad|iPod|Mobile/i', $request->userAgent() ?? '') ? 5 : 10;
        $counts = Laporan::query()
            ->selectRaw('jenis_laporan, COUNT(*) as total')
            ->groupBy('jenis_laporan')
            ->pluck('total', 'jenis_laporan');
        $laporan = Laporan::query()
            ->with('penyelesaian.admin')
            ->where('jenis_laporan', $jenis)
            ->when($status === 'menunggu', fn ($query) => $query->whereIn('status_laporan', ['menunggu_pemeriksaan', 'menunggu_penyerahan']))
            ->when($status === 'dipublikasikan', fn ($query) => $query->whereIn('status_laporan', ['dipublikasikan', 'proses_klaim']))
            ->when(in_array($status, ['selesai', 'ditolak'], true), fn ($query) => $query->where('status_laporan', $status))
            ->latest()
            ->orderByDesc('id_laporan')
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.index', compact('laporan', 'status', 'jenis', 'counts'));
    }

    public function show(Laporan $laporan)
    {
        $laporan->load(['penyelesaian.admin', 'klaim', 'penyerahan.admin', 'reviewer']);

        return view('admin.show', compact('laporan'));
    }

    public function review(Request $request, Laporan $laporan)
    {
        Gate::authorize('manage-laporan');
        $data = $request->validate([
            'aksi' => 'required|in:terima,tolak',
            'catatan_admin' => 'nullable|string|max:1000',
        ]);

        return DB::transaction(function () use ($laporan, $data) {
            $current = Laporan::query()->whereKey($laporan->getKey())->lockForUpdate()->firstOrFail();
            if ($current->adminStage() !== 'menunggu') {
                return back()->withErrors('Laporan ini sudah diproses.');
            }

            $accepted = $data['aksi'] === 'terima';
            $current->update([
                'status_laporan' => $accepted ? 'dipublikasikan' : 'ditolak',
                'dipublikasikan_pada' => $accepted ? now() : null,
                'catatan_admin' => $data['catatan_admin'] ?? null,
                'id_admin_pemeriksa' => Auth::guard('admin')->id(),
            ]);
            Log::info('security.report_reviewed', [
                'report_id' => $current->getKey(), 'admin_id' => Auth::guard('admin')->id(),
                'status' => $current->status_laporan,
            ]);

            return back()->with('sukses', $accepted ? 'Laporan sudah dipublikasikan.' : 'Laporan ditolak.');
        });
    }

    public function complete(CompleteLaporanRequest $request, Laporan $laporan)
    {
        Gate::authorize('manage-laporan');
        $data = $request->validated();

        return DB::transaction(function () use ($laporan, $data) {
            $current = Laporan::query()->whereKey($laporan->getKey())->lockForUpdate()->firstOrFail();
            if ($current->adminStage() !== 'dipublikasikan' || str_starts_with($current->kode_laporan, 'TA-SAMPLE-')) {
                return back()->withErrors('Laporan ini tidak dapat diselesaikan lagi.');
            }

            $current->penyelesaian()->create([
                'jenis_penyelesaian' => $current->jenis_laporan === 'hilang' ? 'ditemukan' : 'diambil',
                'nama_pihak' => $data['nama_pihak'],
                'kontak_pihak' => $data['kontak_pihak'],
                'lokasi_ditemukan' => $current->jenis_laporan === 'hilang' ? $data['lokasi_ditemukan'] : null,
                'catatan_verifikasi' => $data['catatan_verifikasi'] ?? null,
                'id_admin' => Auth::guard('admin')->id(),
                'diselesaikan_pada' => now(),
            ]);
            $current->update(['status_laporan' => 'selesai']);
            Log::info('security.report_completed', [
                'report_id' => $current->getKey(), 'admin_id' => Auth::guard('admin')->id(),
            ]);

            return back()->with('sukses', 'Laporan selesai dan masuk ke riwayat.');
        });
    }
}
