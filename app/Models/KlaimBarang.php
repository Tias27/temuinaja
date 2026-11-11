<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KlaimBarang extends Model
{
    protected $table = 'klaim_barang';

    protected $primaryKey = 'id_klaim';

    protected $guarded = [];

    protected $casts = ['diverifikasi_pada' => 'datetime'];
}
