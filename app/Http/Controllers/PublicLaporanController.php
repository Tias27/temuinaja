<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLaporanRequest;
use App\Models\Laporan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PublicLaporanController extends Controller
{
    public function home(Request $request, ?string $page = null)
    {
        return $this->renderHome($request, false, $page);
    }

    public function mockup(Request $request, ?string $page = null)
    {
        return $this->renderHome($request, true, $page);
    }

    private function renderHome(Request $request, bool $preview, ?string $page = null)
    {
        $pageNumber = $page === null ? 1 : (int) $page;
        abort_unless($pageNumber >= 1 && $pageNumber <= 10000, 404);
        $perPage = $this->itemsPerPage($request);
        $laporan = Laporan::query()
            ->visibleToPublic()
            ->where('jenis_laporan', 'ditemukan')
            ->latest('dipublikasikan_pada')
            ->paginate($perPage, ['*'], 'page', $pageNumber);

        return view('home.index', compact('laporan', 'preview'));
    }

    private function itemsPerPage(Request $request): int
    {
        return preg_match('/Android|iPhone|iPad|iPod|Mobile/i', $request->userAgent() ?? '')
            ? 6
            : 8;
    }

    public function search(Request $request, string $jenis)
    {
        $data = $request->validate(['q' => 'nullable|string|max:100']);
        $q = trim($data['q'] ?? '');
        if ($q === '') {
            return redirect()->route('laporan.index', $jenis);
        }
        $term = rtrim(strtr(base64_encode($q), '+/', '-_'), '=');

        return redirect()->route('laporan.search.index', ['jenis' => $jenis, 'term' => $term]);
    }

    public function page(Request $request, string $jenis, string $page)
    {
        return $this->index($request, $jenis, null, $page);
    }

    public function index(
        Request $request,
        string $jenis,
        ?string $term = null,
        ?string $page = null,
    ) {
        abort_unless(in_array($jenis, ['ditemukan', 'hilang'], true), 404);
        $q = '';
        if ($term !== null) {
            abort_unless(preg_match('/^[A-Za-z0-9_-]{2,534}$/D', $term) === 1, 404);
            $decoded = base64_decode(strtr($term, '-_', '+/'), true);
            abort_unless(
                $decoded !== false &&
                    mb_check_encoding($decoded, 'UTF-8') &&
                    mb_strlen($decoded) <= 100 &&
                    trim($decoded) !== '',
                404,
            );
            abort_unless(rtrim(strtr(base64_encode($decoded), '+/', '-_'), '=') === $term, 404);
            $q = $decoded;
        }
        $pageNumber = $page === null ? 1 : (int) $page;
        abort_unless($pageNumber >= 1 && $pageNumber <= 10000, 404);
        $perPage = $this->itemsPerPage($request);
        $pattern = '%'.addcslashes($q, '%_\\').'%';
        $laporan = Laporan::query()
            ->visibleToPublic()
            ->where('jenis_laporan', $jenis)
            ->when(
                $q !== '',
                fn ($query) => $query->where(
                    fn ($query) => $query
                        ->where('deskripsi', 'like', $pattern)
                        ->orWhere('lokasi', 'like', $pattern),
                ),
            )
            ->latest('dipublikasikan_pada')
            ->paginate($perPage, ['*'], 'page', $pageNumber);

        return view('public.index', compact('laporan', 'jenis', 'q', 'term'));
    }

    public function create(string $jenis)
    {
        abort_unless(in_array($jenis, ['hilang', 'ditemukan']), 404);

        return view('public.create', compact('jenis'));
    }

    public function store(StoreLaporanRequest $request, string $jenis)
    {
        if (! function_exists('imagecreatefromstring') || ! function_exists('imagewebp')) {
            throw ValidationException::withMessages([
                'foto_barang' => 'Pemrosesan foto sedang tidak tersedia. Hubungi petugas dan coba lagi nanti',
            ]);
        }

        $paths = [];
        try {
            foreach ($request->file('foto_barang') as $file) {
                $image = @imagecreatefromstring(file_get_contents($file->getRealPath()));
                if (! $image) {
                    throw ValidationException::withMessages([
                        'foto_barang' => 'Foto tidak dapat diproses.',
                    ]);
                }
                $relative = 'laporan/'.Str::uuid().'.webp';
                ob_start();
                try {
                    $encoded = imagewebp($image, null, 85);
                    $bytes = ob_get_contents();
                } finally {
                    ob_end_clean();
                    imagedestroy($image);
                    unset($image);
                }
                if (! $encoded || ! $bytes) {
                    throw new \RuntimeException('Gagal memproses foto');
                }
                if (! Storage::disk('local')->put($relative, $bytes)) {
                    throw new \RuntimeException('Gagal menyimpan foto');
                }
                $paths[] = $relative;
            }
            $laporan = Laporan::create([
                ...$request->safe()->except('foto_barang'),
                'jenis_laporan' => $jenis,
                'foto_barang' => $paths,
                'kode_laporan' => 'TA-'.strtoupper(Str::random(12)),
                'status_laporan' => 'menunggu_pemeriksaan',
            ]);

            return redirect()->route('laporan.confirmation', $laporan->kode_laporan);
        } catch (\Throwable $e) {
            foreach ($paths as $path) {
                Storage::disk('local')->delete($path);
            }
            throw $e;
        }
    }

    public function show(Laporan $laporan)
    {
        abort_unless($laporan->isVisibleToPublic(), 404);

        return response()->view('public.show', compact('laporan'))
            ->header('Cache-Control', 'private, no-store');
    }

    public function confirmation(string $kode)
    {
        $laporan = Laporan::where('kode_laporan', $kode)->firstOrFail();
        abort_if($laporan->status_laporan === 'selesai' && ! $laporan->isVisibleToPublic(), 404);

        return view('public.confirmation', compact('laporan'));
    }

    public function foto(Laporan $laporan, int $index)
    {
        abort_unless(auth('admin')->check() || $laporan->isVisibleToPublic(), 404);
        $path = $laporan->foto_barang[$index] ?? null;
        abort_unless(is_string($path) && preg_match('~^laporan/[a-zA-Z0-9_-]+\.webp$~D', $path), 404);
        $root = realpath(Storage::disk('local')->path('laporan'));
        $file = realpath(Storage::disk('local')->path($path));
        abort_unless($root && $file && is_file($file) && str_starts_with($file, $root.DIRECTORY_SEPARATOR), 404);

        return response()->file($file, [
            'Content-Type' => 'image/webp',
            'Cache-Control' => auth('admin')->check() || $laporan->status_laporan === 'selesai' ? 'private, no-store' : 'private, max-age=3600',
            'X-Content-Type-Options' => 'nosniff',
        ])->setPrivate();
    }
}
