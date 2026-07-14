<?php

namespace Database\Seeders;

use App\Models\PipaPoint;
use Illuminate\Database\Seeder;

class PipaPointSeeder extends Seeder
{
    public function run(): void
    {
        $points = [
            ['scheme' => 'mojolaban', 'logger_id' => '10373', 'label' => 'DMA 1',  'kind' => 'outlet', 'x' => 55.732, 'y' => 84.495, 'pressure_display' => 'pressure_1'],
            ['scheme' => 'mojolaban', 'logger_id' => '10374', 'label' => 'DMA 3',  'kind' => 'outlet', 'x' => 85.652, 'y' => 68.834, 'pressure_display' => 'pressure_1'],
            ['scheme' => 'mojolaban', 'logger_id' => '10375', 'label' => 'DMA 5',  'kind' => 'outlet', 'x' => 50.979, 'y' => 38.011, 'pressure_display' => 'pressure_1'],
            ['scheme' => 'mojolaban', 'logger_id' => '10376', 'label' => 'DMA 6',  'kind' => 'outlet', 'x' => 14.627, 'y' => 9.051, 'pressure_display' => 'pressure_1'],
            ['scheme' => 'mojolaban', 'logger_id' => null,    'label' => 'RESERVOIR MOJOLABAN', 'kind' => 'reservoir', 'x' => 66.548, 'y' => 83.891, 'pressure' => 3.4, 'flowrate' => 148.0, 'totalizer' => 984320, 'pressure_display' => 'auto'],

            ['scheme' => 'plesungan', 'logger_id' => '10372', 'label' => 'DMA 16 OUTLET', 'kind' => 'outlet', 'x' => 68.67, 'y' => 9.16, 'pressure_display' => 'pressure_2'],
            ['scheme' => 'plesungan', 'logger_id' => null,    'label' => 'RESERVOIR PLESUNGAN', 'kind' => 'reservoir', 'x' => 86.141, 'y' => 16.058, 'pressure' => 3.4, 'flowrate' => 148.0, 'totalizer' => 984320, 'pressure_display' => 'auto'],
            ['scheme' => 'plesungan', 'logger_id' => '10379', 'label' => 'DMA 15 INLET',  'kind' => 'inlet',  'x' => 76.249, 'y' => 41.214, 'pressure_display' => 'pressure_1'],
            ['scheme' => 'plesungan', 'logger_id' => '10370', 'label' => 'DMA 12 OUTLET', 'kind' => 'outlet', 'x' => 35.028, 'y' => 69.747, 'pressure_display' => 'pressure_2'],
            ['scheme' => 'plesungan', 'logger_id' => '10371', 'label' => 'DMA 15 OUTLET', 'kind' => 'outlet', 'x' => 66.558, 'y' => 70.564, 'pressure_display' => 'pressure_2'],
            ['scheme' => 'plesungan', 'logger_id' => '10377', 'label' => 'DMA 9 INLET',   'kind' => 'inlet',  'x' => 34.649, 'y' => 25.042, 'pressure_display' => 'pressure_1'],
            ['scheme' => 'plesungan', 'logger_id' => '10369', 'label' => 'DMA 11 OUTLET', 'kind' => 'outlet', 'x' => 8.985, 'y' => 28.731, 'pressure_display' => 'pressure_2'],
            ['scheme' => 'plesungan', 'logger_id' => '10368', 'label' => 'DMA 9 OUTLET',  'kind' => 'outlet', 'x' => 35.513, 'y' => 32.148, 'pressure_display' => 'pressure_2'],
            ['scheme' => 'plesungan', 'logger_id' => '10378', 'label' => 'DMA 12 INLET',  'kind' => 'inlet',  'x' => 39.725, 'y' => 54.509, 'pressure_display' => 'pressure_1'],
        ];

        foreach ($points as $i => $p) {
            // Idempotent: kunci pada scheme + label supaya seed ulang tidak dobel.
            PipaPoint::updateOrCreate(
                ['scheme' => $p['scheme'], 'label' => $p['label']],
                array_merge($p, ['sort_order' => $i]),
            );
        }
    }
}
