<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateLaporanRequest;
use App\Models\Laporan;
use App\Support\ReportImages;
use App\Support\ReportPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ReportHistoryController extends Controller
{
    public function index(Request $request, string $page = '1')
    {
        abort_unless(preg_match('/^[1-9][0-9]{0,4}$/D', $page) && (int) $page <= 10000, 404);
        abort_unless(is_string($request->attributes->get('browser_history_hash')), 404);
        $perPage = preg_match('/Android|iPhone|iPad|iPod|Mobile/i', $request->userAgent() ?? '') ? 5 : 10;
        $laporan = Laporan::query()
            ->where('browser_history_hash', $request->attributes->get('browser_history_hash'))
            ->whereNull('ditarik_pada')
            ->select(['id_laporan', 'kode_laporan', 'jenis_laporan', 'deskripsi', 'foto_barang', 'lokasi', 'tanggal_kejadian', 'status_laporan', 'created_at'])
            ->latest()->orderByDesc('id_laporan')
            ->paginate($perPage, ['*'], 'page', (int) $page);

        return view('public.history', compact('laporan'));
    }

    public function photo(Request $request, Laporan $laporan, int $index)
    {
        abort_unless($laporan->belongsToBrowser($request->attributes->get('browser_history_hash')), 404);

        return ReportPhoto::response($laporan, $index);
    }

    public function edit(Request $request, Laporan $laporan)
    {
        abort_unless($laporan->belongsToBrowser($request->attributes->get('browser_history_hash')), 404);
        abort_unless($laporan->canBeEditedByReporter(), 403, 'Laporan selesai tidak dapat diedit');

        return view('public.create', ['jenis' => $laporan->jenis_laporan, 'laporan' => $laporan]);
    }

    public function update(UpdateLaporanRequest $request, Laporan $laporan)
    {
        $paths = [];
        $oldPaths = [];
        try {
            DB::transaction(function () use ($request, $laporan, &$paths, &$oldPaths) {
                $current = Laporan::whereKey($laporan->getKey())->lockForUpdate()->firstOrFail();
                abort_unless($current->belongsToBrowser($request->attributes->get('browser_history_hash')), 404);
                if (! $current->canBeEditedByReporter()) {
                    throw ValidationException::withMessages(['deskripsi' => 'Laporan sudah selesai dan tidak dapat diedit']);
                }
                if ($request->hasFile('foto_barang')) {
                    $paths = ReportImages::store($request->file('foto_barang'));
                    $oldPaths = $current->foto_barang ?? [];
                }
                $previousStatus = $current->status_laporan;
                $current->update([
                    ...$request->safe()->except('foto_barang'),
                    'foto_barang' => $paths ?: $current->foto_barang,
                    'status_laporan' => 'menunggu_pemeriksaan',
                    'dipublikasikan_pada' => null,
                    'id_admin_pemeriksa' => null,
                    'catatan_admin' => null,
                ]);
                Log::info('security.report_edited_by_reporter', [
                    'report_id' => $current->getKey(), 'previous_status' => $previousStatus,
                ]);
            });
        } catch (\Throwable $e) {
            ReportImages::discard($paths);
            throw $e;
        }
        ReportImages::discard($oldPaths);
        $request->session()->flash('sukses', 'Perubahan tersimpan. Laporan menunggu pemeriksaan ulang');

        return $request->expectsJson()
            ? response()->json(['redirect' => route('laporan.history')])
            : redirect()->route('laporan.history');
    }

    public function destroy(Request $request, Laporan $laporan)
    {
        abort_unless($laporan->belongsToBrowser($request->attributes->get('browser_history_hash')), 404);
        $request->validate(['konfirmasi' => 'required|accepted']);
        DB::transaction(function () use ($request, $laporan) {
            $current = Laporan::whereKey($laporan->getKey())->lockForUpdate()->firstOrFail();
            abort_unless($current->belongsToBrowser($request->attributes->get('browser_history_hash')), 404);
            $current->update(['ditarik_pada' => now()]);
            Log::info('security.report_withdrawn_by_reporter', ['report_id' => $current->getKey()]);
        });

        return redirect()->route('laporan.history')->with('sukses', 'Laporan dihapus dari riwayat dan katalog. Arsip tetap tersimpan untuk petugas');
    }
}
