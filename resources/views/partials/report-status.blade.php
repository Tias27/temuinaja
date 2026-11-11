@if (str_starts_with($item->kode_laporan, 'TA-SAMPLE-'))
    Contoh barang temuan
@elseif ($item->status_laporan === 'selesai')
    Selesai
@elseif ($item->jenis_laporan === 'hilang')
    Sedang dicari
@else
    Temuan dilaporkan
@endif
