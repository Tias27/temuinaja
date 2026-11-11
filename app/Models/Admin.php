<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable
{
    protected $table = 'admin';

    protected $primaryKey = 'id_admin';

    const CREATED_AT = 'created_at';

    const UPDATED_AT = 'updated_at';

    protected $fillable = ['nama_admin', 'nama_pengguna', 'kata_sandi_hash', 'peran'];

    protected $hidden = ['kata_sandi_hash'];

    public function getAuthPasswordName()
    {
        return 'kata_sandi_hash';
    }

    public function getAuthPassword()
    {
        return $this->kata_sandi_hash;
    }
}
