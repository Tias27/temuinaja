<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenyelesaianLaporan extends Model
{
    protected $table = 'penyelesaian_laporan';

    protected $primaryKey = 'id_penyelesaian';

    protected $guarded = [];

    protected $casts = ['diselesaikan_pada' => 'datetime'];

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'id_admin');
    }
}
