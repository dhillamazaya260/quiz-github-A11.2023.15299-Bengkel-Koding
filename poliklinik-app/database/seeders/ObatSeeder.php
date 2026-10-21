<?php

namespace Database\Seeders;

use App\Models\Obat;
use Illuminate\Database\Seeder;

class ObatSeeder extends Seeder
{
    public function run(): void
    {
        // Format: [nama obat, kemasan, harga (rupiah)]
        $obats = [
            ['Paracetamol 500 mg',            'Strip (10 tablet)',  5000],
            ['Amoxicillin 500 mg',            'Strip (10 kapsul)',  12000],
            ['Ibuprofen 400 mg',              'Strip (10 tablet)',  9000],
            ['Cetirizine 10 mg',              'Strip (10 tablet)',  8000],
            ['Loratadine 10 mg',              'Strip (10 tablet)',  11000],
            ['Omeprazole 20 mg',              'Strip (10 kapsul)',  15000],
            ['Antasida DOEN',                 'Strip (10 tablet)',  4000],
            ['Ambroxol 30 mg',                'Strip (10 tablet)',  7000],
            ['Dextromethorphan 15 mg',        'Strip (10 tablet)',  6000],
            ['Vitamin C 500 mg',              'Botol (30 tablet)',  25000],
            ['Vitamin B Kompleks',            'Botol (30 tablet)',  18000],
            ['Metformin 500 mg',              'Strip (10 tablet)',  8500],
            ['Amlodipine 5 mg',               'Strip (10 tablet)',  9500],
            ['Simvastatin 10 mg',             'Strip (10 tablet)',  14000],
            ['Salbutamol 2 mg',               'Strip (10 tablet)',  6500],
            ['Oralit',                        'Sachet',             2500],
            ['Sirup Paracetamol 120 mg/5 ml', 'Botol 60 ml',        16000],
            ['Zinc 20 mg',                    'Strip (10 tablet)',  20000],
            ['Povidone Iodine 10%',           'Botol 30 ml',        12000],
            ['Salep Gentamicin 0,1%',         'Tube 5 g',           15000],
        ];

        foreach ($obats as [$nama, $kemasan, $harga]) {
            Obat::updateOrCreate(
                ['nama_obat' => $nama],
                ['kemasan' => $kemasan, 'harga' => $harga]
            );
        }
    }
}