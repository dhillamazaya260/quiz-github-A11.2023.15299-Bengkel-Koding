@php
    $rp        = fn ($n) => 'Rp ' . number_format((int) $n, 0, ',', '.');
    $pasien    = $periksa->daftarPoli->pasien;
    $jadwal    = $periksa->daftarPoli->jadwalPeriksa;
    $dokter    = $jadwal?->dokter;
    $details   = $periksa->detailPeriksas;
    $totalObat = (int) $details->sum('harga');
    $noStruk   = 'STR-' . $periksa->tgl_periksa->format('Ymd') . '-' . str_pad($periksa->id, 5, '0', STR_PAD_LEFT);
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Struk {{ $noStruk }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0; padding: 24px 12px; background: #f1f5f9; color: #1e293b;
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 14px;
        }
        .toolbar {
            max-width: 520px; margin: 0 auto 12px; display: flex; gap: 8px; justify-content: space-between;
        }
        .btn {
            display: inline-flex; align-items: center; gap: 6px; padding: 9px 16px; border-radius: 10px;
            border: 0; font-size: 13px; font-weight: 600; cursor: pointer; text-decoration: none;
        }
        .btn-print { background: #2d4499; color: #fff; }
        .btn-back  { background: #fff; color: #475569; border: 1px solid #e2e8f0; }
        .struk {
            max-width: 520px; margin: 0 auto; background: #fff; border: 1px solid #e2e8f0;
            border-radius: 14px; padding: 24px 22px;
        }
        .head { text-align: center; padding-bottom: 14px; border-bottom: 2px dashed #cbd5e1; }
        .head img { width: 48px; height: 48px; border-radius: 10px; object-fit: cover; }
        .head h1 { margin: 8px 0 2px; font-size: 18px; }
        .head p  { margin: 0; font-size: 12px; color: #64748b; }
        .lunas {
            display: inline-block; margin-top: 10px; padding: 3px 14px; border: 2px solid #16a34a;
            color: #16a34a; border-radius: 8px; font-weight: 800; letter-spacing: 2px; font-size: 13px;
        }
        dl { margin: 14px 0; }
        .row { display: flex; justify-content: space-between; gap: 12px; padding: 3px 0; }
        .row dt { color: #64748b; }
        .row dd { margin: 0; text-align: right; font-weight: 600; }
        .blok { margin: 14px 0; padding-top: 12px; border-top: 2px dashed #cbd5e1; }
        .blok h2 { margin: 0 0 8px; font-size: 11px; text-transform: uppercase; letter-spacing: .05em; color: #64748b; }
        .blok p { margin: 0 0 8px; line-height: 1.5; white-space: pre-line; }
        .blok p small { display: block; color: #64748b; font-size: 11px; margin-bottom: 1px; }
        ol.resep { margin: 0; padding-left: 20px; }
        ol.resep li { padding: 3px 0; font-weight: 600; }
        ol.resep li span { font-weight: 400; color: #64748b; }
        table { width: 100%; border-collapse: collapse; margin-top: 4px; }
        th { text-align: left; font-size: 11px; text-transform: uppercase; letter-spacing: .05em; color: #64748b;
             padding: 8px 0; border-top: 2px dashed #cbd5e1; border-bottom: 1px solid #e2e8f0; }
        th:last-child, td:last-child { text-align: right; }
        td { padding: 6px 0; vertical-align: top; }
        .sub td { color: #64748b; border-top: 1px solid #e2e8f0; }
        .total td { font-weight: 800; font-size: 16px; padding-top: 10px; border-top: 2px dashed #cbd5e1; }
        .foot { margin-top: 18px; padding-top: 12px; border-top: 2px dashed #cbd5e1; text-align: center;
                font-size: 12px; color: #64748b; }
        @media print {
            body { background: #fff; padding: 0; }
            .toolbar { display: none; }
            .struk { border: 0; border-radius: 0; max-width: none; padding: 0; }
            @page { margin: 14mm; }
        }
    </style>
</head>
<body>

    <div class="toolbar">
        <a href="{{ route('pasien.riwayat') }}" class="btn btn-back">&larr; Kembali</a>
        <button type="button" class="btn btn-print" onclick="window.print()">&#128424; Cetak Struk</button>
    </div>

    <div class="struk">
        <div class="head">
            <img src="{{ asset('images/logo-bengkot.png') }}" alt="Logo">
            <h1>Poliklinik</h1>
            <p>Struk Pembayaran Pemeriksaan</p>
            <span class="lunas">LUNAS</span>
        </div>

        <dl>
            <div class="row"><dt>No. Struk</dt><dd>{{ $noStruk }}</dd></div>
            <div class="row"><dt>Pasien</dt><dd>{{ $pasien->nama ?? '-' }}</dd></div>
            @if ($pasien?->no_rm)
                <div class="row"><dt>No. RM</dt><dd>{{ $pasien->no_rm }}</dd></div>
            @endif
            <div class="row"><dt>Dokter</dt><dd>{{ $dokter?->nama ?? '-' }}</dd></div>
            <div class="row"><dt>Poli</dt><dd>{{ $dokter?->poli?->nama_poli ?? '-' }}</dd></div>
            <div class="row"><dt>No. Antrian</dt><dd>{{ str_pad($periksa->daftarPoli->no_antrian, 3, '0', STR_PAD_LEFT) }}</dd></div>
            <div class="row"><dt>Tgl. Periksa</dt><dd>{{ $periksa->tgl_periksa->format('d/m/Y H:i') }}</dd></div>
            @if ($periksa->tgl_verifikasi)
                <div class="row"><dt>Tgl. Lunas</dt><dd>{{ $periksa->tgl_verifikasi->format('d/m/Y H:i') }}</dd></div>
            @endif
        </dl>

        {{-- ===== Hasil pemeriksaan & resep obat ===== --}}
        <div class="blok">
            <h2>Hasil Pemeriksaan</h2>
            <p>
                <small>Keluhan</small>
                {{ $periksa->daftarPoli->keluhan ?: '-' }}
            </p>
            <p>
                <small>Catatan Dokter</small>
                {{ $periksa->catatan ?: '-' }}
            </p>
        </div>

        <div class="blok">
            <h2>Resep Obat</h2>
            @if ($details->isNotEmpty())
                <ol class="resep">
                    @foreach ($details as $detail)
                        <li>
                            {{ $detail->obat?->nama_obat ?? 'Obat (sudah dihapus)' }}
                            @if ($detail->obat?->kemasan)
                                <span>&middot; {{ $detail->obat->kemasan }}</span>
                            @endif
                        </li>
                    @endforeach
                </ol>
            @else
                <p style="color:#94a3b8">Tidak ada obat yang diresepkan</p>
            @endif
            <div style="margin-top:6px;font-size:11px;color:#64748b">
                Diresepkan oleh {{ $dokter?->nama ?? '-' }}
            </div>
        </div>

        <h2 style="margin:14px 0 0;font-size:11px;text-transform:uppercase;letter-spacing:.05em;color:#64748b">Rincian Biaya</h2>
        <table>
            <thead>
                <tr><th>Item</th><th>Harga</th></tr>
            </thead>
            <tbody>
                <tr><td>Jasa Dokter</td><td>{{ $rp($periksa->biaya_jasa) }}</td></tr>
                @forelse ($details as $detail)
                    <tr>
                        <td>
                            {{ $detail->obat?->nama_obat ?? 'Obat (sudah dihapus)' }}
                            @if ($detail->obat?->kemasan)
                                <span style="color:#94a3b8">({{ $detail->obat->kemasan }})</span>
                            @endif
                        </td>
                        <td>{{ $rp($detail->harga) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="2" style="color:#94a3b8">Tidak ada obat yang diresepkan</td></tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="sub"><td>Subtotal Obat ({{ $details->count() }} item)</td><td>{{ $rp($totalObat) }}</td></tr>
                <tr class="total"><td>Total Dibayar</td><td>{{ $rp($periksa->biaya_periksa) }}</td></tr>
            </tfoot>
        </table>

        <div class="foot">
            Pembayaran telah diverifikasi oleh admin.<br>
            Terima kasih dan semoga lekas sembuh.<br>
            <span style="font-size:11px">Dicetak {{ now()->format('d/m/Y H:i') }}</span>
        </div>
    </div>

</body>
</html>