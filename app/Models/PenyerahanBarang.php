<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenyerahanBarang extends Model
{
    protected $table = 'penyerahan_barang';

    protected $primaryKey = 'id_penyerahan';

    protected $guarded = [];

    protected $casts = ['tanggal_penyerahan' => 'datetime'];

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'id_admin');
    }
}
