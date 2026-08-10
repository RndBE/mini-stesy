<?php

namespace App\Support;

class LoggerDisplayOrder
{
    /**
     * Urutan tampil logger di beranda web maupun API mobile, mengikuti
     * daftar Data Logger:
     * Sukoharjo (Reservoir Mojolaban) lalu Surakarta (Reservoir Plesungan).
     * Dipakai id_logger karena nama_logger tidak konsisten ("DMA 12 OUTLET"
     * vs "INLET DMA 12"). Logger di luar daftar tampil setelahnya, urut nama.
     */
    public const ORDER = [
        // Sukoharjo — Reservoir Mojolaban
        '10373', // DMA 1 Mojolaban
        '10374', // DMA 3 Mojolaban
        '10375', // DMA 5 Mojolaban
        '10376', // DMA 6 Mojolaban
        // Surakarta — Reservoir Plesungan
        '10378', // DMA 12 Inlet
        '10370', // DMA 12 Outlet
        '10377', // DMA 9 Inlet
        '10368', // DMA 9 Outlet
        '10369', // DMA 11 Outlet
        '10379', // DMA 15 Inlet
        '10371', // DMA 15 Outlet
        '10372', // DMA 16 Outlet
    ];

    /**
     * Posisi logger pada daftar; logger tanpa urutan eksplisit dapat rank
     * terbesar supaya jatuh ke belakang.
     */
    public static function rank($idLogger): int
    {
        $index = array_search((string) $idLogger, self::ORDER, true);

        return $index === false ? PHP_INT_MAX : $index;
    }

    /**
     * Urutkan koleksi logger: daftar eksplisit dulu, sisanya urut nama.
     */
    public static function sort($loggers)
    {
        return $loggers
            ->sort(function ($a, $b) {
                return [self::rank($a->id_logger), strtolower((string) $a->nama_logger)]
                    <=> [self::rank($b->id_logger), strtolower((string) $b->nama_logger)];
            })
            ->values();
    }
}
