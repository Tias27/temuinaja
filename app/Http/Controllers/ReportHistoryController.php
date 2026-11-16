<?php

namespace App\Http\Controllers;

use App\Models\Laporan;
use App\Support\ReportPhoto;
use Illuminate\Http\Request;

class ReportHistoryController extends Controller
{
    public function index(Request $request, string $page = '1')
    {
        abort_unless(preg_match('/^[1-9][0-9]{0,4}$/D', $page) && (int) $page <= 10000, 404);
        abort_unless(is_string($request->attributes->get('browser_history_hash')), 404);
        $perPage = preg_match('/Android|iPhone|iPad|iPod|Mobile/i', $request->userAgent() ?? '') ? 5 : 10;
        $laporan = Laporan::query()
            ->where('browser_history_hash', $request->attributes->get('browser_history_hash'))
            ->select(['id_laporan', 'kode_laporan', 'jenis_laporan', 'deskripsi', 'foto_barang', 'lokasi', 'tanggal_kejadian', 'status_laporan', 'created_at'])
            ->latest()->orderByDesc('id_laporan')
            ->paginate($perPage, ['*'], 'page', (int) $page);

        return view('public.history', compact('laporan'));
    }

    public function photo(Request $request, Laporan $laporan, int $index)
    {
        $browserHash = $request->attributes->get('browser_history_hash');
        abort_unless(is_string($browserHash) && is_string($laporan->browser_history_hash)
            && hash_equals($laporan->browser_history_hash, $browserHash), 404);

        return ReportPhoto::response($laporan, $index);
    }
}
