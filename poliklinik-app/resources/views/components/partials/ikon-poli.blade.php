@php
    /*
     * Ikon + warna per poli. Poli Umum (dan poli lain yang belum terdaftar di sini)
     * memakai ikon rumah sakit. Cocokkan berdasarkan kata kunci pada nama poli.
     *
     * Parameter:
     *   $nama  : nama poli
     *   $besar : true = kotak 40px (banner), false = 32px (tabel)   [default false]
     *   $gelap : true = gaya untuk latar gelap (banner)             [default false]
     */
    $besar = $besar ?? false;
    $gelap = $gelap ?? false;

    $daftarIkon = [
        // kata kunci => [ikon, gradien awal, gradien akhir, warna ikon (terang), rgb (gelap), warna ikon (gelap)]
        'gigi'      => ['fa-tooth',          '#cffafe', '#a5f3fc', '#0891b2', '6,182,212',   '#67e8f9'],
        'anak'      => ['fa-baby',           '#fce7f3', '#fbcfe8', '#db2777', '236,72,153',  '#f9a8d4'],
        'kandungan' => ['fa-person-pregnant','#ffe4e6', '#fecdd3', '#e11d48', '244,63,94',   '#fda4af'],
        'mata'      => ['fa-eye',            '#d1fae5', '#a7f3d0', '#059669', '16,185,129',  '#6ee7b7'],
        'tht'       => ['fa-ear-listen',     '#fef3c7', '#fde68a', '#d97706', '245,158,11',  '#fcd34d'],
    ];

    // Default = Poli Umum
    $ikon = ['fa-hospital', '#e0e7ff', '#c7d2fe', '#4f46e5', '99,102,241', '#a5b4fc'];

    $namaKecil = \Illuminate\Support\Str::lower((string) $nama);
    foreach ($daftarIkon as $kataKunci => $nilai) {
        if (\Illuminate\Support\Str::contains($namaKecil, $kataKunci)) {
            $ikon = $nilai;
            break;
        }
    }

    $kotak = $besar ? 'w-10 h-10 rounded-xl' : 'w-8 h-8 rounded-lg';
    $gaya  = $gelap
        ? "background: rgba({$ikon[4]},0.25); border:1px solid rgba({$ikon[4]},0.35);"
        : "background: linear-gradient(135deg,{$ikon[1]},{$ikon[2]});";
    $warnaIkon = $gelap ? $ikon[5] : $ikon[3];
    $ukuranIkon = $besar ? '' : 'font-size:.75rem;';
@endphp
<div class="{{ $kotak }} flex items-center justify-center flex-shrink-0" style="{{ $gaya }}">
    <i class="fas {{ $ikon[0] }}" style="color: {{ $warnaIkon }}; {{ $ukuranIkon }}"></i>
</div>