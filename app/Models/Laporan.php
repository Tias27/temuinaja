<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Laporan extends Model
{
    protected $table = 'laporan';

    protected $primaryKey = 'id_laporan';

    protected $guarded = [];

    protected $hidden = ['kontak_pelapor', 'browser_history_hash'];

    protected $casts = [
        'foto_barang' => 'array',
        'tanggal_kejadian' => 'date',
        'dipublikasikan_pada' => 'datetime',
        'ditarik_pada' => 'datetime',
    ];

    public function getRouteKeyName()
    {
        return 'kode_laporan';
    }

    public function klaim()
    {
        return $this->hasMany(KlaimBarang::class, 'id_laporan');
    }

    public function penyerahan()
    {
        return $this->hasMany(PenyerahanBarang::class, 'id_laporan');
    }

    public function penyelesaian()
    {
        return $this->hasOne(PenyelesaianLaporan::class, 'id_laporan');
    }

    public function reviewer()
    {
        return $this->belongsTo(Admin::class, 'id_admin_pemeriksa');
    }

    public function scopeVisibleToPublic(Builder $query): Builder
    {
        $cutoff = now()->subHours(48);
        $ownerHandover = fn (Builder $handover) => $handover->where('jenis_penyerahan', 'ke_pemilik');

        return $query->whereNull('ditarik_pada')->whereNotNull('dipublikasikan_pada')
            ->where(fn (Builder $visible) => $visible
                ->whereIn('status_laporan', ['dipublikasikan', 'proses_klaim'])
                ->orWhere(fn (Builder $completed) => $completed
                    ->where('status_laporan', 'selesai')
                    ->where(fn (Builder $recent) => $recent
                        ->whereHas('penyelesaian', fn (Builder $completion) => $completion
                            ->where('diselesaikan_pada', '>', $cutoff))
                        // Older reports use their handover time, or last update if no history exists.
                        ->orWhere(fn (Builder $legacy) => $legacy
                            ->whereDoesntHave('penyelesaian')
                            ->where(fn (Builder $history) => $history
                                ->whereHas('penyerahan', fn (Builder $handover) => $ownerHandover($handover)
                                    ->where('tanggal_penyerahan', '>', $cutoff))
                                ->orWhere(fn (Builder $withoutHistory) => $withoutHistory
                                    ->whereDoesntHave('penyerahan', $ownerHandover)
                                    ->where('laporan.updated_at', '>', $cutoff)))))));
    }

    public function isVisibleToPublic(): bool
    {
        return static::query()->whereKey($this->getKey())->visibleToPublic()->exists();
    }

    public function adminStage(): string
    {
        if ($this->ditarik_pada !== null) {
            return 'ditarik';
        }

        return match ($this->status_laporan) {
            'menunggu_pemeriksaan', 'menunggu_penyerahan' => 'menunggu',
            'dipublikasikan', 'proses_klaim' => 'dipublikasikan',
            'selesai' => 'selesai',
            'ditolak' => 'ditolak',
            default => 'menunggu',
        };
    }

    public function belongsToBrowser(mixed $hash): bool
    {
        return $this->ditarik_pada === null && is_string($hash)
            && is_string($this->browser_history_hash) && hash_equals($this->browser_history_hash, $hash);
    }

    public function canBeEditedByReporter(): bool
    {
        return $this->ditarik_pada === null && in_array($this->status_laporan, [
            'menunggu_pemeriksaan', 'menunggu_penyerahan', 'dipublikasikan', 'proses_klaim', 'ditolak',
        ], true);
    }
}
