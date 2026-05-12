<?php

namespace App\Http\Controllers;

use App\Models\t_Logger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SkemaIrigasiController extends Controller
{
    public function index()
    {
        return view('skema.index', [
            'title' => 'Skema Irigasi'
        ]);
    }

    public function asbuiltDrawing()
    {
        $path = base_path('ASBUILT+DRAWING+LEUWIGOONG+AMS19A+BUKU+1.pdf');

        abort_unless(file_exists($path), 404, 'File as built drawing tidak ditemukan.');

        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="asbuilt-drawing-leuwigoong-ams19a-buku-1.pdf"',
        ]);
    }

    public function kontrolPintu($node_id)
    {
        // Dalam implementasi nyata, kita sebaiknya melakukan DB query,
        // tapi karena topologi kita di-generate via array dan sensor diinjeksi via getData():
        $topologyResponse = $this->getData()->getData(true);
        $nodes = $topologyResponse['nodes'] ?? [];
        
        $targetNode = null;
        foreach ($nodes as $node) {
            if ($node['id'] === $node_id) {
                $targetNode = $node;
                break;
            }
        }

        if (!$targetNode) {
            abort(404, 'Node Irigasi tidak ditemukan');
        }

        // Dummy data informatif per node (TMA, debit, info saluran)
        // Dalam sistem nyata diambil dari DB secara live
        $nodeInfoDummy = [
            'WEIR_COPONG' => ['tma_hulu_cm' => 325, 'tma_hilir_cm' => 298, 'debit_m3s' => 12.45, 'saluran' => 'Bendung Copong — Sungai Cimanuk', 'kapasitas_m3s' => 15.00, 'elevasi_m' => 241.5],
            'BGP_3'       => ['tma_hulu_cm' => 215, 'tma_hilir_cm' => 198, 'debit_m3s' =>  6.80, 'saluran' => 'Percabangan Primer Copong',        'kapasitas_m3s' =>  9.00, 'elevasi_m' => 228.3],
            'BPG_0'       => ['tma_hulu_cm' => 180, 'tma_hilir_cm' => 162, 'debit_m3s' =>  3.25, 'saluran' => 'Percabangan Parigi Hulu',           'kapasitas_m3s' =>  5.00, 'elevasi_m' => 215.7],
            'BPG_3'       => ['tma_hulu_cm' => 168, 'tma_hilir_cm' => 152, 'debit_m3s' =>  2.75, 'saluran' => 'Percabangan Parigi — Ranca Ucing',  'kapasitas_m3s' =>  4.50, 'elevasi_m' => 212.4],
            'BPG_7'       => ['tma_hulu_cm' => 148, 'tma_hilir_cm' => 132, 'debit_m3s' =>  2.10, 'saluran' => 'Percabangan Cikananga — Sawah Bera','kapasitas_m3s' =>  3.50, 'elevasi_m' => 208.1],
            'BCP_Ki_1'    => ['tma_hulu_cm' => 185, 'tma_hilir_cm' => 170, 'debit_m3s' =>  2.40, 'saluran' => 'Saluran Copong Kiri',               'kapasitas_m3s' =>  4.00, 'elevasi_m' => 226.1],
            'BCP_Ko_1'    => ['tma_hulu_cm' => 178, 'tma_hilir_cm' => 163, 'debit_m3s' =>  2.15, 'saluran' => 'Saluran Copong Kanan',              'kapasitas_m3s' =>  3.50, 'elevasi_m' => 224.8],
            'BCP_Ko_5'    => ['tma_hulu_cm' => 155, 'tma_hilir_cm' => 140, 'debit_m3s' =>  1.85, 'saluran' => 'Saluran Copong Kanan Hilir',        'kapasitas_m3s' =>  3.00, 'elevasi_m' => 220.4],
            'BCK_1'       => ['tma_hulu_cm' => 142, 'tma_hilir_cm' => 128, 'debit_m3s' =>  1.20, 'saluran' => 'Saluran Cikananga',                 'kapasitas_m3s' =>  2.00, 'elevasi_m' => 210.2],
            'BRU_1'       => ['tma_hulu_cm' => 135, 'tma_hilir_cm' => 120, 'debit_m3s' =>  0.95, 'saluran' => 'Saluran Ranca Ucing',               'kapasitas_m3s' =>  1.50, 'elevasi_m' => 207.6],
            'BCD_20'      => ['tma_hulu_cm' => 158, 'tma_hilir_cm' => 142, 'debit_m3s' =>  2.30, 'saluran' => 'Percabangan Leuw Goong — Ciseureuh','kapasitas_m3s' =>  4.00, 'elevasi_m' => 216.9],
            'BCS_1'       => ['tma_hulu_cm' => 165, 'tma_hilir_cm' => 148, 'debit_m3s' =>  1.65, 'saluran' => 'Saluran Ciseureuh',                 'kapasitas_m3s' =>  2.50, 'elevasi_m' => 218.3],
            'BCS_3'       => ['tma_hulu_cm' => 145, 'tma_hilir_cm' => 130, 'debit_m3s' =>  1.30, 'saluran' => 'Saluran Ciseureuh Tengah',          'kapasitas_m3s' =>  2.00, 'elevasi_m' => 213.8],
            'BLG_1'       => ['tma_hulu_cm' => 138, 'tma_hilir_cm' => 122, 'debit_m3s' =>  1.45, 'saluran' => 'Percabangan Leuw Goong — Kamung Luhuk','kapasitas_m3s'=> 2.50, 'elevasi_m' => 209.5],
        ];

        if (isset($nodeInfoDummy[$node_id])) {
            $targetNode = array_merge($targetNode, $nodeInfoDummy[$node_id]);
        }

        return view('skema.kontrol', [
            'title' => 'Kontrol Pintu Air — ' . ($targetNode['nama_logger'] ?? $node_id),
            'node'  => $targetNode
        ]);
    }

    public static function getRawTopology()
    {
        // Topology modeled after Copong Weir image layout
        return [
            'nodes' => [
                ['id' => 'TITLE', 'label' => "SKEMA PEMBAGIAN AIR IRIGASI\nDAERAH LEUWIGOONG", 'x' => 1000, 'y' => 50, 'type' => 'title'],
                
                // MAIN WEIR — dikembalikan ke weir_large agar tampilan visual ikon tetap seperti desain awal
                ['id' => 'WEIR_COPONG', 'label' => '', 'x' => 200, 'y' => 200, 'type' => 'weir_large', 'source_name' => 'Bendung Copong'],
                // Teks dipisah agar tidak menabrak garis, digeser ke kanan
                ['id' => 'LBL_WEIR_COPONG', 'label' => 'COPONG WEIR', 'x' => 200, 'y' => 150, 'type' => 'label_text'],
                
                // ==============================
                // COPONG MAIN CANAL (Kanan - Horizontal)
                // ==============================
                // ['id' => 'AWLR_MID_1', 'label' => '', 'x' => 260, 'y' => 200, 'type' => 'sensor_awlr', 'source_name' => 'Copong Main Canal'],
                
                ['id' => 'BGP_1', 'label' => '', 'x' => 300, 'y' => 200, 'type' => 'junction', 'source_name' => 'Copong Main Canal', 'status' => 'open'],
                ['id' => 'BGP_2', 'label' => '', 'x' => 400, 'y' => 200, 'type' => 'junction', 'source_name' => 'Copong Main Canal', 'status' => 'open'],
                // AWLR sebelum percabangan besar BGP_3 — mengukur total debit primer (AKTIF)
                ['id' => 'AWLR_MAIN', 'label' => '', 'x' => 450, 'y' => 200, 'type' => 'sensor_awlr', 'source_name' => 'Copong Main Canal'],
                ['id' => 'BGP_3', 'label' => '', 'x' => 500, 'y' => 200, 'type' => 'junction', 'source_name' => 'Copong Main Canal', 'status' => 'open'],

                ['id' => 'BCP_Ki_1', 'label' => '', 'x' => 600, 'y' => 200, 'type' => 'junction', 'source_name' => 'Copong Main Canal', 'status' => 'open'],
                
                ['id' => 'TITLE_CIDUGA', 'label' => 'Ciduga Sec. Canal', 'x' => 1500, 'y' => 250, 'type' => 'label_text'],
                ['id' => 'BCD_1', 'label' => '', 'x' => 700, 'y' => 200, 'type' => 'junction', 'source_name' => 'Copong Main Canal', 'status' => 'open'],
                ['id' => 'BCD_2', 'label' => '', 'x' => 800, 'y' => 200, 'type' => 'junction', 'source_name' => 'Copong Main Canal', 'status' => 'open'],
                ['id' => 'BCD_3', 'label' => '', 'x' => 900, 'y' => 200, 'type' => 'junction', 'source_name' => 'Copong Main Canal', 'status' => 'open'],
                ['id' => 'BCD_4', 'label' => '', 'x' => 1000, 'y' => 200, 'type' => 'junction', 'source_name' => 'Copong Main Canal', 'status' => 'open'],
                ['id' => 'BCD_5', 'label' => '', 'x' => 1100, 'y' => 200, 'type' => 'junction', 'source_name' => 'Copong Main Canal', 'status' => 'open'],
                ['id' => 'BCD_6', 'label' => '', 'x' => 1200, 'y' => 200, 'type' => 'junction', 'source_name' => 'Ciduga Secondary Canal', 'status' => 'open'],
                ['id' => 'BCD_7', 'label' => '', 'x' => 1300, 'y' => 200, 'type' => 'junction', 'source_name' => 'Ciduga Secondary Canal', 'status' => 'open'],
                ['id' => 'BCD_8', 'label' => '', 'x' => 1400, 'y' => 200, 'type' => 'junction', 'source_name' => 'Ciduga Secondary Canal', 'status' => 'open'],
                ['id' => 'BCD_9', 'label' => '', 'x' => 1500, 'y' => 200, 'type' => 'junction', 'source_name' => 'Ciduga Secondary Canal', 'status' => 'open'],
                ['id' => 'BCD_10', 'label' => '', 'x' => 1600, 'y' => 200, 'type' => 'junction', 'source_name' => 'Ciduga Secondary Canal', 'status' => 'open'],
                ['id' => 'BCD_11', 'label' => '', 'x' => 1700, 'y' => 200, 'type' => 'junction', 'source_name' => 'Ciduga Secondary Canal', 'status' => 'open'],
                ['id' => 'BCD_12', 'label' => '', 'x' => 1800, 'y' => 200, 'type' => 'junction', 'source_name' => 'Ciduga Secondary Canal', 'status' => 'open'],
                ['id' => 'BCD_13', 'label' => '', 'x' => 1900, 'y' => 200, 'type' => 'junction', 'source_name' => 'Ciduga Secondary Canal', 'status' => 'open'],
                ['id' => 'BCD_14', 'label' => '', 'x' => 2000, 'y' => 200, 'type' => 'junction', 'source_name' => 'Ciduga Secondary Canal', 'status' => 'open'],
                ['id' => 'BCD_15', 'label' => '', 'x' => 2100, 'y' => 200, 'type' => 'junction', 'source_name' => 'Ciduga Secondary Canal', 'status' => 'open'],
                ['id' => 'BCD_16', 'label' => '', 'x' => 2200, 'y' => 200, 'type' => 'junction', 'source_name' => 'Ciduga Secondary Canal', 'status' => 'open'],
                ['id' => 'CORNER_BCD16', 'label' => 'BCD_17', 'x' => 2300, 'y' => 200, 'type' => 'corner', 'source_name' => 'Ciduga Secondary Canal', 'status' => 'open'],
                
                // Labels for primary canal
                ['id' => 'LBL_BGP1', 'label' => 'BGP.1', 'x' => 300, 'y' => 150, 'type' => 'label_yellow', 'source_name' => 'Ciduga Secondary Canal'],
                ['id' => 'LBL_BGP2', 'label' => 'BGP.2', 'x' => 400, 'y' => 150, 'type' => 'label_yellow', 'source_name' => 'Ciduga Secondary Canal'],
                ['id' => 'LBL_BGP3', 'label' => 'BGP.3', 'x' => 500, 'y' => 150, 'type' => 'label_yellow', 'source_name' => 'Ciduga Secondary Canal'],
                ['id' => 'LBL_BCD16', 'label' => 'BCD.16', 'x' => 2200, 'y' => 150, 'type' => 'label_yellow', 'source_name' => 'Ciduga Secondary Canal'],

                // ==============================
                // COPONG KANAN MAIN CANAL (Bawah - Vertikal)
                // ==============================
                ['id' => 'TITLE_CPG_KANAN', 'label' => 'Copong Kanan Main Canal', 'x' => 320, 'y' => 320, 'type' => 'label_text', 'rotation' => -40],
                // AWLR di intake Copong Kanan — mengukur debit yang diterima dari BGP_3
                // ['id' => 'AWLR_KO',    'label' => '', 'x' => 350, 'y' => 325, 'type' => 'sensor_awlr', 'source_name' => 'Copong Kanan Main Canal'],
                ['id' => 'BCP_Ko_1', 'label' => '', 'x' => 200, 'y' => 450, 'type' => 'junction', 'source_name' => 'Copong Kanan Main Canal', 'status' => 'open'],
                ['id' => 'BCP_Ko_2', 'label' => '', 'x' => 200, 'y' => 550, 'type' => 'junction', 'source_name' => 'Copong Kanan Main Canal', 'status' => 'open'],
                ['id' => 'BCP_Ko_3', 'label' => '', 'x' => 200, 'y' => 650, 'type' => 'junction', 'source_name' => 'Copong Kanan Main Canal', 'status' => 'open'],
                ['id' => 'BCP_Ko_4', 'label' => '', 'x' => 200, 'y' => 750, 'type' => 'junction', 'source_name' => 'Copong Kanan Main Canal', 'status' => 'open'],
                ['id' => 'BCP_Ko_5', 'label' => '', 'x' => 200, 'y' => 850, 'type' => 'junction', 'source_name' => 'Copong Kanan Main Canal', 'status' => 'open'],
                ['id' => 'BCP_Ko_6', 'label' => '', 'x' => 200, 'y' => 950, 'type' => 'junction', 'source_name' => 'Copong Kanan Main Canal', 'status' => 'open'],
                
                ['id' => 'LBL_BCP_Ko_1', 'label' => 'BCP.Ko.1', 'x' => 100, 'y' => 450, 'type' => 'label_yellow', 'source_name' => 'Copong Kanan Main Canal'],
                ['id' => 'LBL_BCP_Ko_5', 'label' => 'BCP.Ko.5', 'x' => 100, 'y' => 850, 'type' => 'label_yellow', 'source_name' => 'Copong Kanan Main Canal'],
                ['id' => 'LBL_BCP_Ko_6', 'label' => 'BCP.Ko.6', 'x' => 100, 'y' => 950, 'type' => 'label_yellow', 'source_name' => 'Copong Kanan Main Canal'],

                // ==============================
                // PARIGI SECONDARY CANAL (Dari BGP.3 Turun ke BPG.0)
                // ==============================
                ['id' => 'LBL_BAGENDIT', 'label' => 'Situ Bagendit', 'x' => 420, 'y' => 555, 'type' => 'label_text'],
                ['id' => 'BAGENDIT', 'label' => '', 'x' => 500, 'y' => 550, 'type' => 'weir_main'],
                // AWLR setelah Bagendit — mengukur debit yang masuk ke jaringan Parigi (AKTIF)
                ['id' => 'AWLR_PARIGI', 'label' => '', 'x' => 550, 'y' => 550, 'type' => 'sensor_awlr', 'source_name' => 'Parigi Secondary Canal'],
                // BPG.0 berhimpit dekat bagendit
                ['id' => 'BPG_0', 'label' => '', 'x' => 600, 'y' => 550, 'type' => 'junction', 'source_name' => 'Bagendit Outlet', 'status' => 'open'],
                
                ['id' => 'TITLE_PARIGI', 'label' => 'Parigi Secondary Canal', 'x' => 1000, 'y' => 590, 'type' => 'label_text'],
                ['id' => 'BPG_1', 'label' => '', 'x' => 700, 'y' => 550, 'type' => 'junction', 'source_name' => 'Parigi Secondary Canal', 'status' => 'open'],
                ['id' => 'BPG_2', 'label' => '', 'x' => 800, 'y' => 550, 'type' => 'junction', 'source_name' => 'Parigi Secondary Canal', 'status' => 'open'],
                ['id' => 'BPG_3', 'label' => '', 'x' => 900, 'y' => 550, 'type' => 'junction', 'source_name' => 'Parigi Secondary Canal', 'status' => 'open'],
                ['id' => 'BPG_4', 'label' => '', 'x' => 1000, 'y' => 550, 'type' => 'junction', 'source_name' => 'Parigi Secondary Canal', 'status' => 'open'],
                ['id' => 'BPG_5', 'label' => '', 'x' => 1100, 'y' => 550, 'type' => 'junction', 'source_name' => 'Parigi Secondary Canal', 'status' => 'open'],
                ['id' => 'BPG_6', 'label' => '', 'x' => 1200, 'y' => 550, 'type' => 'junction', 'source_name' => 'Parigi Secondary Canal', 'status' => 'open'],
                ['id' => 'BPG_7', 'label' => '', 'x' => 1300, 'y' => 550, 'type' => 'junction', 'source_name' => 'Parigi Secondary Canal', 'status' => 'open'],

                ['id' => 'BMPG_0ka2', 'label' => '', 'x' => 600, 'y' => 650, 'type' => 'junction', 'source_name' => 'Parigi Secondary Canal'],
                ['id' => 'BMPG_2ka1', 'label' => '', 'x' => 800, 'y' => 650, 'type' => 'junction', 'source_name' => 'Parigi Secondary Canal'],

                ['id' => 'TITLE_RANCAUCING', 'label' => 'Ranca Ucing Secondary Canal', 'x' => 870, 'y' => 700, 'type' => 'label_text', 'rotation' => -90],
                ['id' => 'BRU_1', 'label' => '', 'x' => 900, 'y' => 700, 'type' => 'junction', 'source_name' => 'Parigi Secondary Canal', 'status' => 'open'],
                ['id' => 'BMRU_1te', 'label' => '', 'x' => 900, 'y' => 800, 'type' => 'junction', 'source_name' => 'Parigi Secondary Canal'],

                ['id' => 'TITLE_CIKANANGA', 'label' => 'Cikananga Secondary Canal', 'x' => 1470, 'y' => 590, 'type' => 'label_text'],
                ['id' => 'BCK_1', 'label' => '', 'x' => 1500, 'y' => 550, 'type' => 'junction', 'source_name' => 'Cikananga Secondary Canal', 'status' => 'open'],
                ['id' => 'BMCK_1.Ki', 'label' => '', 'x' => 1600, 'y' => 550, 'type' => 'junction', 'source_name' => 'Cikananga Secondary Canal'],
                
                ['id' => 'LBL_BPG_0', 'label' => 'BPG.0', 'x' => 600, 'y' => 500, 'type' => 'label_yellow', 'source_name' => 'Parigi Secondary Canal'],
                ['id' => 'LBL_BPG_3', 'label' => 'BPG.3', 'x' => 900, 'y' => 500, 'type' => 'label_yellow', 'source_name' => 'Parigi Secondary Canal'],

                // ==============================
                // CIKUKUK SECONDARY CANAL (Dari BCD.5 turun)
                // ==============================
                ['id' => 'TITLE_CIKUKUK', 'label' => 'Cikukuk Sec. Canal', 'x' => 1060, 'y' => 300, 'type' => 'label_text', 'rotation' => -90],
                ['id' => 'BC_1', 'label' => '', 'x' => 1100, 'y' => 290, 'type' => 'junction', 'source_name' => 'Cikukuk Secondary Canal', 'status' => 'open'],
                ['id' => 'LBL_BC_1', 'label' => 'BC.1', 'x' => 1160, 'y' => 290, 'type' => 'label_yellow', 'source_name' => 'Cikukuk Secondary Canal'],

                ['id' => 'BMC_1ka', 'label' => '', 'x' => 1100, 'y' => 370, 'type' => 'junction', 'source_name' => 'Cikukuk Secondary Canal', 'status' => 'open'],

                // ==============================
                // CINANTI SECONDARY CANAL
                // ==============================
                ['id' => 'TITLE_CINANTI', 'label' => 'Cinanti Sec. Canal', 'x' => 1400, 'y' => 420, 'type' => 'label_text'],
                ['id' => 'CN_START', 'label' => 'BCN_1', 'x' => 1000, 'y' => 450, 'type' => 'corner', 'source_name' => 'Cinanti Secondary Canal', 'status' => 'open'], 
                ['id' => 'BCN_1', 'label' => '', 'x' => 1150, 'y' => 450, 'type' => 'junction', 'source_name' => 'Cinanti Secondary Canal', 'status' => 'open'],
                ['id' => 'BCN_2', 'label' => '', 'x' => 1250, 'y' => 450, 'type' => 'junction', 'source_name' => 'Cinanti Secondary Canal', 'status' => 'open'],
                ['id' => 'BCN_3', 'label' => '', 'x' => 1350, 'y' => 450, 'type' => 'junction', 'source_name' => 'Cinanti Secondary Canal', 'status' => 'open'],
                ['id' => 'BCN_4', 'label' => '', 'x' => 1450, 'y' => 450, 'type' => 'junction', 'source_name' => 'Cinanti Secondary Canal', 'status' => 'open'],
                ['id' => 'BCN_5', 'label' => '', 'x' => 1550, 'y' => 450, 'type' => 'junction', 'source_name' => 'Cinanti Secondary Canal', 'status' => 'open'],
                ['id' => 'BCN_6', 'label' => '', 'x' => 1650, 'y' => 450, 'type' => 'junction', 'source_name' => 'Cinanti Secondary Canal', 'status' => 'open'],

                ['id' => 'BMCN_6ka', 'label' => '', 'x' => 1750, 'y' => 450, 'type' => 'junction', 'source_name' => 'Cinanti Secondary Canal'],

                // ==============================
                // CISEUREUH & MAIN CANAL DROP (Dari BCD.16 Turun Kanan)
                // ==============================
                ['id' => 'BCD_17', 'label' => '', 'x' => 2300, 'y' => 250, 'type' => 'junction', 'source_name' => 'Ciduga Secondary Canal', 'status' => 'open'],
                ['id' => 'BCD_18', 'label' => '', 'x' => 2300, 'y' => 320, 'type' => 'junction', 'source_name' => 'Ciduga Secondary Canal', 'status' => 'open'],
                ['id' => 'BCD_19', 'label' => '', 'x' => 2300, 'y' => 380, 'type' => 'junction', 'source_name' => 'Ciduga Secondary Canal', 'status' => 'open'],
                // AWLR sebelum percabangan Leuw Goong & Ciseureuh — titik kontrol hilir Ciduga (AKTIF)
                ['id' => 'AWLR_LG',  'label' => '', 'x' => 2300, 'y' => 415, 'type' => 'sensor_awlr', 'source_name' => 'Ciduga Secondary Canal'],
                ['id' => 'BCD_20', 'label' => '', 'x' => 2300, 'y' => 450, 'type' => 'junction', 'source_name' => 'Ciduga Secondary Canal', 'status' => 'open'],

                ['id' => 'TITLE_CISEUREUH', 'label' => 'Ciseureuh Sec. Canal', 'x' => 2420, 'y' => 550, 'type' => 'label_text', 'rotation' => -90],
                ['id' => 'BCS_1', 'label' => '', 'x' => 2450, 'y' => 450, 'type' => 'junction', 'source_name' => 'Ciseureuh Secondary Canal', 'status' => 'open'],
                ['id' => 'BCS_2', 'label' => '', 'x' => 2450, 'y' => 520, 'type' => 'junction', 'source_name' => 'Ciseureuh Secondary Canal', 'status' => 'open'],
                ['id' => 'BCS_3', 'label' => '', 'x' => 2450, 'y' => 590, 'type' => 'junction', 'source_name' => 'Ciseureuh Secondary Canal', 'status' => 'open'],
                ['id' => 'BCS_4', 'label' => '', 'x' => 2450, 'y' => 660, 'type' => 'junction', 'source_name' => 'Ciseureuh Secondary Canal', 'status' => 'open'],
                
                // ==============================
                // KAMUNG LUHUK SEC. CANAL (Belok Kiri dari BCS.4)
                // ==============================
                ['id' => 'TITLE_KAMUNG', 'label' => 'Kamung Luhuk Sec.', 'x' => 2400, 'y' => 780, 'type' => 'label_text'],
                ['id' => 'BKL_1', 'label' => '', 'x' => 2500, 'y' => 800, 'type' => 'junction', 'source_name' => 'Kamung Luhuk Secondary Canal', 'status' => 'open'],
                ['id' => 'BKL_2', 'label' => '', 'x' => 2300, 'y' => 800, 'type' => 'junction', 'source_name' => 'Kamung Luhuk Secondary Canal', 'status' => 'open'],

                // ==============================
                // LEUW GOONG SEC. CANAL (Belok Kiri dari BCD.20 lalu Turun)
                // ==============================
                ['id' => 'TITLE_LEUW', 'label' => 'Leuwi Goong Sec. Canal', 'x' => 1950, 'y' => 1180, 'type' => 'label_text'],
                ['id' => 'BLG_1', 'label' => '', 'x' => 2150, 'y' => 450, 'type' => 'junction', 'source_name' => 'Leuw Goong Secondary Canal', 'status' => 'open'],
                ['id' => 'BLG_2', 'label' => '', 'x' => 2150, 'y' => 520, 'type' => 'junction', 'source_name' => 'Leuw Goong Secondary Canal', 'status' => 'open'],
                ['id' => 'BLG_3', 'label' => '', 'x' => 2150, 'y' => 590, 'type' => 'junction', 'source_name' => 'Leuw Goong Secondary Canal', 'status' => 'open'],
                ['id' => 'BLG_4', 'label' => '', 'x' => 2150, 'y' => 660, 'type' => 'junction', 'source_name' => 'Leuw Goong Secondary Canal', 'status' => 'open'],
                ['id' => 'BLG_5', 'label' => '', 'x' => 2150, 'y' => 730, 'type' => 'junction', 'source_name' => 'Leuw Goong Secondary Canal', 'status' => 'open'],
                ['id' => 'BLG_6', 'label' => '', 'x' => 2150, 'y' => 800, 'type' => 'junction', 'source_name' => 'Leuw Goong Secondary Canal', 'status' => 'open'],
                ['id' => 'BLG_7', 'label' => '', 'x' => 2150, 'y' => 870, 'type' => 'junction', 'source_name' => 'Leuw Goong Secondary Canal', 'status' => 'open'],
                ['id' => 'BLG_8', 'label' => '', 'x' => 2150, 'y' => 940, 'type' => 'junction', 'source_name' => 'Leuw Goong Secondary Canal', 'status' => 'open'],
                ['id' => 'BLG_9', 'label' => '', 'x' => 2150, 'y' => 1010, 'type' => 'junction', 'source_name' => 'Leuw Goong Secondary Canal', 'status' => 'open'],
                ['id' => 'BLG_10', 'label' => '', 'x' => 2150, 'y' => 1080, 'type' => 'junction', 'source_name' => 'Leuw Goong Secondary Canal', 'status' => 'open'],
                ['id' => 'BLG_11', 'label' => '', 'x' => 2150, 'y' => 1150, 'type' => 'junction', 'source_name' => 'Leuw Goong Secondary Canal', 'status' => 'open'],
                ['id' => 'BLG_12', 'label' => '', 'x' => 2150, 'y' => 1220, 'type' => 'junction', 'source_name' => 'Leuw Goong Secondary Canal', 'status' => 'open'],
                ['id' => 'BLG_13', 'label' => '', 'x' => 2050, 'y' => 1220, 'type' => 'junction', 'source_name' => 'Leuw Goong Secondary Canal', 'status' => 'open'],
                ['id' => 'BLG_14', 'label' => '', 'x' => 1950, 'y' => 1220, 'type' => 'junction', 'source_name' => 'Leuw Goong Secondary Canal', 'status' => 'open'],
                ['id' => 'BLG_15', 'label' => '', 'x' => 1850, 'y' => 1220, 'type' => 'junction', 'source_name' => 'Leuw Goong Secondary Canal', 'status' => 'open'],
                ['id' => 'BMLG_15ka', 'label' => '', 'x' => 1750, 'y' => 1220, 'type' => 'junction', 'source_name' => 'Leuw Goong Secondary Canal', 'status'],
                ['id' => 'LBL_BLG_12', 'label' => 'BLG.12', 'x' => 2150, 'y' => 1280, 'type' => 'label_yellow', 'source_name' => 'Leuw Goong Secondary Canal'],
                
                // SAWAH BERA SEC. CANAL
                ['id' => 'TITLE_SAWAH', 'label' => 'Sawah Bera Sec. Canal', 'x' => 1270, 'y' => 730, 'type' => 'label_text', 'rotation' => -90],
                ['id' => 'BSB_1', 'label' => '', 'x' => 1300, 'y' => 650, 'type' => 'junction', 'source_name' => 'Sawah Bera Secondary Canal', 'status' => 'open'],
                ['id' => 'BSB_2', 'label' => '', 'x' => 1300, 'y' => 730, 'type' => 'junction', 'source_name' => 'Sawah Bera Secondary Canal', 'status' => 'open'],
                ['id' => 'BSB_3', 'label' => '', 'x' => 1300, 'y' => 810, 'type' => 'junction', 'source_name' => 'Sawah Bera Secondary Canal', 'status' => 'open'],
                ['id' => 'BMSB_3ter', 'label' => '', 'x' => 1300, 'y' => 890, 'type' => 'junction', 'source_name' => 'Sawah Bera Secondary Canal'],

            ],
            'edges' => [
                // ==============================
                // COPONG KANAN (Tersambung dari BGP.3, bukan dari Weir)
                // ==============================
                // ['source' => 'BGP_3', 'target' => 'AWLR_KO',   'type' => 'primary'],    // ← AWLR intake Copong Kanan
                ['source' => 'BGP_3',  'target' => 'BCP_Ko_1', 'type' => 'primary'],
                ['source' => 'BCP_Ko_1', 'target' => 'BCP_Ko_2', 'type' => 'primary'],
                ['source' => 'BCP_Ko_2', 'target' => 'BCP_Ko_3', 'type' => 'primary'],
                ['source' => 'BCP_Ko_3', 'target' => 'BCP_Ko_4', 'type' => 'primary'],
                ['source' => 'BCP_Ko_4', 'target' => 'BCP_Ko_5', 'type' => 'primary'],
                ['source' => 'BCP_Ko_5', 'target' => 'BCP_Ko_6', 'type' => 'primary'],
                
                ['source' => 'BCP_Ko_1', 'target' => 'LBL_BCP_Ko_1', 'type' => 'tertiary'],
                ['source' => 'BCP_Ko_5', 'target' => 'LBL_BCP_Ko_5', 'type' => 'tertiary'],
                ['source' => 'BCP_Ko_6', 'target' => 'LBL_BCP_Ko_6', 'type' => 'tertiary'],

                // ==============================
                // COPONG MAIN CANAL
                // ==============================
                // ['source' => 'WEIR_COPONG', 'target' => 'AWLR_MID_1', 'type' => 'primary'],
                ['source' => 'WEIR_COPONG', 'target' => 'BGP_1', 'type' => 'primary'],
                
                ['source' => 'BGP_1',     'target' => 'BGP_2',     'type' => 'primary'],
                ['source' => 'BGP_2',     'target' => 'AWLR_MAIN', 'type' => 'primary'],   // AWLR sebelum percabangan BGP_3
                ['source' => 'AWLR_MAIN', 'target' => 'BGP_3',     'type' => 'primary'],
                ['source' => 'BGP_3', 'target' => 'BCP_Ki_1', 'type' => 'primary'],

                ['source' => 'BCP_Ki_1', 'target' => 'BCD_1', 'type' => 'primary'],

                ['source' => 'BCD_1', 'target' => 'BCD_2', 'type' => 'primary'],
                ['source' => 'BCD_2', 'target' => 'BCD_3', 'type' => 'primary'],
                ['source' => 'BCD_3', 'target' => 'BCD_4', 'type' => 'primary'],
                ['source' => 'BCD_4', 'target' => 'BCD_5', 'type' => 'primary'],
                ['source' => 'BCD_5', 'target' => 'BCD_6', 'type' => 'secondary'],
                ['source' => 'BCD_6', 'target' => 'BCD_7', 'type' => 'secondary'],
                ['source' => 'BCD_7', 'target' => 'BCD_8', 'type' => 'secondary'],
                ['source' => 'BCD_8', 'target' => 'BCD_9', 'type' => 'secondary'],
                ['source' => 'BCD_9', 'target' => 'BCD_10', 'type' => 'secondary'],
                ['source' => 'BCD_10', 'target' => 'BCD_11', 'type' => 'secondary'],
                ['source' => 'BCD_11', 'target' => 'BCD_12', 'type' => 'secondary'],
                ['source' => 'BCD_12', 'target' => 'BCD_13', 'type' => 'secondary'],
                ['source' => 'BCD_13', 'target' => 'BCD_14', 'type' => 'secondary'],
                ['source' => 'BCD_14', 'target' => 'BCD_15', 'type' => 'secondary'],
                ['source' => 'BCD_15', 'target' => 'BCD_16', 'type' => 'secondary'],

                ['source' => 'BGP_1', 'target' => 'LBL_BGP1', 'type' => 'tertiary'],
                ['source' => 'BGP_2', 'target' => 'LBL_BGP2', 'type' => 'tertiary'],
                ['source' => 'BGP_3', 'target' => 'LBL_BGP3', 'type' => 'tertiary'],
                ['source' => 'BCD_16', 'target' => 'LBL_BCD16', 'type' => 'tertiary'],

                // ==============================
                // BAGENDIT -> PARIGI
                // ==============================
                ['source' => 'BGP_3',      'target' => 'BAGENDIT',    'type' => 'secondary'], // drop to bagendit
                ['source' => 'BAGENDIT',   'target' => 'AWLR_PARIGI', 'type' => 'secondary'], // AWLR intake Parigi
                ['source' => 'AWLR_PARIGI','target' => 'BPG_0',       'type' => 'secondary'], // AWLR → BPG_0
                ['source' => 'BPG_0', 'target' => 'BPG_1', 'type' => 'secondary'],
                ['source' => 'BPG_1', 'target' => 'BPG_2', 'type' => 'secondary'],
                ['source' => 'BPG_2', 'target' => 'BPG_3', 'type' => 'secondary'],
                ['source' => 'BPG_3', 'target' => 'BPG_4', 'type' => 'secondary'],
                ['source' => 'BPG_4', 'target' => 'BPG_5', 'type' => 'secondary'],
                ['source' => 'BPG_5', 'target' => 'BPG_6', 'type' => 'secondary'],
                ['source' => 'BPG_6', 'target' => 'BPG_7', 'type' => 'secondary'],
                ['source' => 'BPG_7', 'target' => 'BCK_1', 'type' => 'secondary', 'status' => 'open'],
                ['source' => 'BCK_1', 'target' => 'BMCK_1.Ki', 'type' => 'secondary'],
                
                ['source' => 'BPG_3', 'target' => 'BRU_1', 'type' => 'secondary'],
                ['source' => 'BRU_1', 'target' => 'BMRU_1te', 'type' => 'secondary'],
                ['source' => 'BPG_0', 'target' => 'BMPG_0ka2', 'type' => 'secondary'],
                ['source' => 'BPG_2', 'target' => 'BMPG_2ka1', 'type' => 'secondary'],

                ['source' => 'BPG_0', 'target' => 'LBL_BPG_0', 'type' => 'tertiary'],
                ['source' => 'BPG_3', 'target' => 'LBL_BPG_3', 'type' => 'tertiary'],



                // ==============================
                // CIKUKUK
                // ==============================
                ['source' => 'BCD_5', 'target' => 'BC_1', 'type' => 'secondary'],
                ['source' => 'BC_1', 'target' => 'BMC_1ka', 'type' => 'secondary'],
                ['source' => 'BC_1', 'target' => 'LBL_BC_1', 'type' => 'tertiary', 'status' => 'open'],

                // ==============================
                // CINANTI
                // ==============================
                ['source' => 'BPG_4', 'target' => 'CN_START', 'type' => 'primary'],
                ['source' => 'CN_START', 'target' => 'BCN_1', 'type' => 'primary'],
                ['source' => 'BCN_1', 'target' => 'BCN_2', 'type' => 'secondary'],
                ['source' => 'BCN_2', 'target' => 'BCN_3', 'type' => 'secondary'],
                ['source' => 'BCN_3', 'target' => 'BCN_4', 'type' => 'secondary'],
                ['source' => 'BCN_4', 'target' => 'BCN_5', 'type' => 'secondary'],
                ['source' => 'BCN_5', 'target' => 'BCN_6', 'type' => 'secondary'],
                ['source' => 'BCN_6', 'target' => 'BMCN_6ka', 'type' => 'secondary'],

                // ==============================
                // CISEUREUH & KAMUNG LUHUK
                // ==============================
                ['source' => 'BCD_20', 'target' => 'BCS_1', 'type' => 'secondary'],
                ['source' => 'BCS_1', 'target' => 'BCS_2', 'type' => 'secondary'],
                ['source' => 'BCS_2', 'target' => 'BCS_3', 'type' => 'secondary'],
                ['source' => 'BCS_3', 'target' => 'BCS_4', 'type' => 'secondary'],
                ['source' => 'BLG_1', 'target' => 'BKL_2', 'type' => 'secondary', 'status' => 'open'],
                ['source' => 'BKL_2', 'target' => 'BKL_1', 'type' => 'secondary'],

                // ==============================
                // LEUW GOONG & SAWAH BERA
                // ==============================
                // BCD.16 -> CORNER -> BCD.17 -> BCD.20
                ['source' => 'BCD_16', 'target' => 'CORNER_BCD16', 'type' => 'secondary'],
                ['source' => 'CORNER_BCD16', 'target' => 'BCD_17', 'type' => 'secondary'],
                ['source' => 'BCD_17', 'target' => 'BCD_18', 'type' => 'secondary'],
                ['source' => 'BCD_18', 'target' => 'BCD_19', 'type' => 'secondary'],
                ['source' => 'BCD_19',  'target' => 'AWLR_LG', 'type' => 'secondary'],  // AWLR sebelum split LG/CS
                ['source' => 'AWLR_LG', 'target' => 'BCD_20', 'type' => 'secondary'],  // AWLR → BCD_20
                
                // Dari BCD.20 belok kiri 90-derajat lalu turun jadi BLG (Main canal)
                ['source' => 'BCD_20', 'target' => 'BLG_1', 'type' => 'secondary'],
                ['source' => 'BLG_1', 'target' => 'BLG_2', 'type' => 'secondary', 'status' => 'open'],
                ['source' => 'BLG_2', 'target' => 'BLG_3', 'type' => 'secondary'],
                ['source' => 'BLG_3', 'target' => 'BLG_4', 'type' => 'secondary'],
                ['source' => 'BLG_4', 'target' => 'BLG_5', 'type' => 'secondary'],
                ['source' => 'BLG_5', 'target' => 'BLG_6', 'type' => 'secondary'],
                ['source' => 'BLG_6', 'target' => 'BLG_7', 'type' => 'secondary'],
                ['source' => 'BLG_7', 'target' => 'BLG_8', 'type' => 'secondary'],
                ['source' => 'BLG_8', 'target' => 'BLG_9', 'type' => 'secondary'],
                ['source' => 'BLG_9', 'target' => 'BLG_10', 'type' => 'secondary'],
                ['source' => 'BLG_10', 'target' => 'BLG_11', 'type' => 'secondary'],
                ['source' => 'BLG_11', 'target' => 'BLG_12', 'type' => 'secondary'],

                ['source' => 'BLG_12', 'target' => 'BLG_13', 'type' => 'secondary'],
                ['source' => 'BLG_13', 'target' => 'BLG_14', 'type' => 'secondary'],
                ['source' => 'BLG_14', 'target' => 'BLG_15', 'type' => 'secondary'],
                ['source' => 'BLG_15', 'target' => 'BMLG_15ka', 'type' => 'secondary'],
                ['source' => 'BLG_12', 'target' => 'LBL_BLG_12', 'type' => 'tertiary', 'status' => 'open'],
                
                ['source' => 'BPG_7', 'target' => 'BSB_1', 'type' => 'secondary', 'status' => 'open'],
                ['source' => 'BSB_1', 'target' => 'BSB_2', 'type' => 'secondary'],
                ['source' => 'BSB_2', 'target' => 'BSB_3', 'type' => 'secondary'],
                ['source' => 'BSB_3', 'target' => 'BMSB_3ter', 'type' => 'secondary'],
                
            ]
        ];
    }

    public function getData()
    {
        $topology = self::getRawTopology();

        // Inject dummy info panel (TMA, debit, kapasitas, saluran) ke setiap node dengan Network Flow
        // PENTING: Harus dijalankan SEBELUM injectSensorData agar panel_kapasitas sudah tersedia
        // saat sensor data (debit aktual AWGC) dibandingkan dengan kapasitas yang benar
        $this->injectPanelInfo($topology['nodes'], $topology['edges']);

        // Inject data sensor AWLR/AWGC dari database ke node yang terhubung
        $this->injectSensorData($topology['nodes'], $topology['edges']);

        // Jalankan propagasi debit secara dinamis dari hulu ke hilir berbasis topologi
        $this->calculateFlowPropagation($topology['nodes'], $topology['edges']);

        return response()->json($topology);
    }

    /**
     * Mengembalikan array [node_id => kapasitas_m3s] dari semua node yang terdaftar di panelInfo.
     * Digunakan oleh SkemaIrigasiDummySeeder agar koefisien debit dummy proporsional per saluran.
     */
    public static function getPanelCapacities(): array
    {
        $topology = self::getRawTopology();
        $nodes    = $topology['nodes'];
        $edges    = $topology['edges'];

        $instance = new self();
        $instance->injectPanelInfo($nodes, $edges);

        $result = [];
        foreach ($nodes as $node) {
            if (isset($node['id'], $node['panel_kapasitas'])) {
                $result[$node['id']] = (float)$node['panel_kapasitas'];
            }
        }
        return $result;
    }

    /**
     * Inject data informasi panel (TMA, debit, kapasitas, saluran) ke setiap node.
     * Data ini digunakan oleh info panel di index.blade.php saat node diklik.
     */
    private function injectPanelInfo(array &$nodes, array $edges): void
    {
        $panelInfo = [
            'WEIR_COPONG' => ['tma_hulu_cm' => 325, 'tma_hilir_cm' => 298, 'debit_m3s' => 15.50, 'kapasitas_m3s' => 15.00, 'saluran' => 'Bendung Copong — Sungai Cimanuk',          'elevasi_m' => 241.5, 'luas_area' => 950],
            // ── AWLR Sensors — Titik monitoring TMA di aliran ──
            // 'AWLR_MID_1'  => tidak diaktifkan (redundan, terlalu dekat WEIR_COPONG)
            'AWLR_MAIN'   => ['tma_hulu_cm' => 0, 'tma_hilir_cm' => 240, 'debit_m3s' => 11.00, 'kapasitas_m3s' => 15.00, 'saluran' => 'Copong Main Canal (Pra-Cabang BGP.3)', 'elevasi_m' => 230.5, 'luas_area' => 870],
            // 'AWLR_KO'     => tidak diaktifkan (berada di ruas diagonal/belokan BGP.3→BCP.Ko.1)
            'AWLR_PARIGI' => ['tma_hulu_cm' => 0, 'tma_hilir_cm' => 120, 'debit_m3s' =>  2.10, 'kapasitas_m3s' =>  5.00, 'saluran' => 'Parigi Secondary Canal (Intake Bagendit)', 'elevasi_m' => 224.0, 'luas_area' => 300],
            'AWLR_LG'     => ['tma_hulu_cm' => 0, 'tma_hilir_cm' => 104, 'debit_m3s' =>  1.35, 'kapasitas_m3s' =>  2.00, 'saluran' => 'Ciduga — Pra-Split Leuw Goong/Ciseureuh',  'elevasi_m' => 217.2, 'luas_area' => 320],
            // ── Saluran Primer: debit menurun dari hulu (WEIR=15.50) sesuai percabangan ──
            // BGP_1→BGP_2→BGP_3 = trunk utama; BGP_3 split ~50% Kiri, ~30% Kanan, ~20% Bagendit
            'BGP_1'    => ['tma_hulu_cm' => 315, 'tma_hilir_cm' => 295, 'debit_m3s' => 13.50, 'kapasitas_m3s' => 15.00, 'saluran' => 'Saluran Primer Copong Hulu',    'elevasi_m' => 238.2, 'luas_area' => 950],
            'BGP_2'    => ['tma_hulu_cm' => 290, 'tma_hilir_cm' => 270, 'debit_m3s' => 12.00, 'kapasitas_m3s' => 15.00, 'saluran' => 'Copong Main Canal',             'elevasi_m' => 235.0, 'luas_area' => 900],
            'BGP_3'    => ['tma_hulu_cm' => 215, 'tma_hilir_cm' => 198, 'debit_m3s' => 10.50, 'kapasitas_m3s' => 19.00, 'saluran' => 'Percabangan Primer Copong',     'elevasi_m' => 228.3, 'luas_area' => 840],
            // BCP_Ki_1 = ~50% dari BGP_3 → lanjut ke BCD_1 dst
            'BCP_Ki_1' => ['tma_hulu_cm' => 185, 'tma_hilir_cm' => 170, 'debit_m3s' =>  5.25, 'kapasitas_m3s' => 15.00, 'saluran' => 'Saluran Copong Kiri',           'elevasi_m' => 226.1, 'luas_area' => 221],
            'BCD_1'    => ['tma_hulu_cm' => 180, 'tma_hilir_cm' => 165, 'debit_m3s' =>  4.90, 'kapasitas_m3s' => 15.00, 'saluran' => 'Copong Main Canal',             'elevasi_m' => 224.8, 'luas_area' => 210],
            'BCD_2'    => ['tma_hulu_cm' => 176, 'tma_hilir_cm' => 161, 'debit_m3s' =>  4.55, 'kapasitas_m3s' => 15.00, 'saluran' => 'Copong Main Canal',             'elevasi_m' => 223.6, 'luas_area' => 205],
            'BCD_3'    => ['tma_hulu_cm' => 174, 'tma_hilir_cm' => 159, 'debit_m3s' =>  4.20, 'kapasitas_m3s' => 15.00, 'saluran' => 'Copong Main Canal',             'elevasi_m' => 222.8, 'luas_area' => 198],
            'BCD_4'    => ['tma_hulu_cm' => 173, 'tma_hilir_cm' => 158, 'debit_m3s' =>  3.85, 'kapasitas_m3s' => 15.00, 'saluran' => 'Copong Main Canal',             'elevasi_m' => 222.3, 'luas_area' => 190],
            'BCD_5'    => ['tma_hulu_cm' => 172, 'tma_hilir_cm' => 158, 'debit_m3s' =>  3.50, 'kapasitas_m3s' => 15.00, 'saluran' => 'Saluran Ciduga',                'elevasi_m' => 222.0, 'luas_area' => 610],
            // ── Ciduga Secondary Canal: sisa BCD_5 (3.50) setelah Cikukuk tersadap ~0.50 ──
            'BCD_6'  => ['tma_hulu_cm' => 170, 'tma_hilir_cm' => 156, 'debit_m3s' =>  3.00, 'kapasitas_m3s' =>  3.50, 'saluran' => 'Ciduga Secondary Canal',  'elevasi_m' => 221.2, 'luas_area' => 560],
            'BCD_7'  => ['tma_hulu_cm' => 169, 'tma_hilir_cm' => 155, 'debit_m3s' =>  2.80, 'kapasitas_m3s' =>  3.50, 'saluran' => 'Ciduga Secondary Canal',  'elevasi_m' => 220.6, 'luas_area' => 540],
            'BCD_8'  => ['tma_hulu_cm' => 168, 'tma_hilir_cm' => 154, 'debit_m3s' =>  2.65, 'kapasitas_m3s' =>  3.50, 'saluran' => 'Ciduga Secondary Canal',  'elevasi_m' => 220.1, 'luas_area' => 525],
            'BCD_9'  => ['tma_hulu_cm' => 167, 'tma_hilir_cm' => 153, 'debit_m3s' =>  2.50, 'kapasitas_m3s' =>  3.50, 'saluran' => 'Ciduga Secondary Canal',  'elevasi_m' => 219.7, 'luas_area' => 510],
            'BCD_10' => ['tma_hulu_cm' => 166, 'tma_hilir_cm' => 152, 'debit_m3s' =>  2.38, 'kapasitas_m3s' =>  3.50, 'saluran' => 'Ciduga Secondary Canal',  'elevasi_m' => 219.2, 'luas_area' => 500],
            'BCD_11' => ['tma_hulu_cm' => 166, 'tma_hilir_cm' => 151, 'debit_m3s' =>  2.25, 'kapasitas_m3s' =>  2.50, 'saluran' => 'Ciduga Secondary Canal',  'elevasi_m' => 218.8, 'luas_area' => 490],
            'BCD_12' => ['tma_hulu_cm' => 165, 'tma_hilir_cm' => 148, 'debit_m3s' =>  2.15, 'kapasitas_m3s' =>  2.50, 'saluran' => 'Saluran Ciduga Tengah',   'elevasi_m' => 218.5, 'luas_area' => 480],
            'BCD_13' => ['tma_hulu_cm' => 163, 'tma_hilir_cm' => 147, 'debit_m3s' =>  2.00, 'kapasitas_m3s' =>  2.50, 'saluran' => 'Ciduga Secondary Canal',  'elevasi_m' => 218.0, 'luas_area' => 450],
            'BCD_14' => ['tma_hulu_cm' => 162, 'tma_hilir_cm' => 146, 'debit_m3s' =>  1.85, 'kapasitas_m3s' =>  2.50, 'saluran' => 'Ciduga Secondary Canal',  'elevasi_m' => 217.6, 'luas_area' => 420],
            'BCD_15' => ['tma_hulu_cm' => 161, 'tma_hilir_cm' => 145, 'debit_m3s' =>  1.70, 'kapasitas_m3s' =>  2.50, 'saluran' => 'Ciduga Secondary Canal',  'elevasi_m' => 217.2, 'luas_area' => 390],
            'BCD_16' => ['tma_hulu_cm' => 160, 'tma_hilir_cm' => 144, 'debit_m3s' =>  1.55, 'kapasitas_m3s' =>  2.50, 'saluran' => 'Ciduga Secondary Canal',  'elevasi_m' => 217.0, 'luas_area' => 360],
            // ── BCP Kanan: ~30% dari BGP_3 (3.15 m³/s), menurun ke hilir ──
            'BCP_Ko_1' => ['tma_hulu_cm' => 178, 'tma_hilir_cm' => 163, 'debit_m3s' =>  3.15, 'kapasitas_m3s' =>  5.00, 'saluran' => 'Saluran Copong Kanan',        'elevasi_m' => 224.8, 'luas_area' => 350],
            'BCP_Ko_2' => ['tma_hulu_cm' => 173, 'tma_hilir_cm' => 158, 'debit_m3s' =>  2.85, 'kapasitas_m3s' =>  5.00, 'saluran' => 'Copong Kanan Main Canal',     'elevasi_m' => 223.9, 'luas_area' => 335],
            'BCP_Ko_3' => ['tma_hulu_cm' => 168, 'tma_hilir_cm' => 153, 'debit_m3s' =>  2.55, 'kapasitas_m3s' =>  5.00, 'saluran' => 'Copong Kanan Main Canal',     'elevasi_m' => 223.1, 'luas_area' => 320],
            'BCP_Ko_4' => ['tma_hulu_cm' => 162, 'tma_hilir_cm' => 147, 'debit_m3s' =>  2.25, 'kapasitas_m3s' =>  5.00, 'saluran' => 'Copong Kanan Main Canal',     'elevasi_m' => 222.0, 'luas_area' => 300],
            'BCP_Ko_5' => ['tma_hulu_cm' => 155, 'tma_hilir_cm' => 140, 'debit_m3s' =>  1.95, 'kapasitas_m3s' =>  5.00, 'saluran' => 'Saluran Copong Kanan Hilir',  'elevasi_m' => 220.4, 'luas_area' => 280],
            'BCP_Ko_6' => ['tma_hulu_cm' => 150, 'tma_hilir_cm' => 136, 'debit_m3s' =>  1.65, 'kapasitas_m3s' =>  5.00, 'saluran' => 'Copong Kanan Main Canal',     'elevasi_m' => 219.6, 'luas_area' => 265],
            // ── Parigi Secondary Canal: ~20% dari BGP_3 (10.50) = 2.10 m³/s via Bagendit ──
            'BAGENDIT'   => ['tma_hulu_cm' => 280, 'tma_hilir_cm' => 265, 'debit_m3s' =>  2.10, 'kapasitas_m3s' =>  5.00, 'saluran' => 'Situ Bagendit',                    'elevasi_m' => 712.0, 'luas_area' => 0],
            'BPG_0'      => ['tma_hulu_cm' => 180, 'tma_hilir_cm' => 162, 'debit_m3s' =>  2.10, 'kapasitas_m3s' =>  2.50, 'saluran' => 'Percabangan Parigi Hulu',         'elevasi_m' => 215.7, 'luas_area' => 450],
            'BPG_1'      => ['tma_hulu_cm' => 178, 'tma_hilir_cm' => 160, 'debit_m3s' =>  1.75, 'kapasitas_m3s' =>  2.50, 'saluran' => 'Parigi Secondary Canal',          'elevasi_m' => 215.1, 'luas_area' => 430],
            'BPG_2'      => ['tma_hulu_cm' => 173, 'tma_hilir_cm' => 156, 'debit_m3s' =>  1.60, 'kapasitas_m3s' =>  2.50, 'saluran' => 'Parigi Secondary Canal',          'elevasi_m' => 214.0, 'luas_area' => 405],
            'BPG_3'      => ['tma_hulu_cm' => 168, 'tma_hilir_cm' => 152, 'debit_m3s' =>  1.30, 'kapasitas_m3s' =>  1.25, 'saluran' => 'Percabangan Parigi — Ranca Ucing', 'elevasi_m' => 212.4, 'luas_area' => 210],
            'BPG_4'      => ['tma_hulu_cm' => 165, 'tma_hilir_cm' => 149, 'debit_m3s' =>  0.90, 'kapasitas_m3s' =>  0.90, 'saluran' => 'Parigi Secondary Canal',          'elevasi_m' => 211.8, 'luas_area' => 195],
            'BPG_5'      => ['tma_hulu_cm' => 160, 'tma_hilir_cm' => 145, 'debit_m3s' =>  0.62, 'kapasitas_m3s' =>  0.63, 'saluran' => 'Parigi Secondary Canal',          'elevasi_m' => 210.9, 'luas_area' => 180],
            'BPG_6'      => ['tma_hulu_cm' => 154, 'tma_hilir_cm' => 139, 'debit_m3s' =>  0.53, 'kapasitas_m3s' =>  0.63, 'saluran' => 'Parigi Secondary Canal',          'elevasi_m' => 209.5, 'luas_area' => 165],
            'BPG_7'      => ['tma_hulu_cm' => 148, 'tma_hilir_cm' => 132, 'debit_m3s' =>  0.45, 'kapasitas_m3s' =>  0.63, 'saluran' => 'Percabangan Cikananga — Sawah Bera', 'elevasi_m' => 208.1, 'luas_area' => 145],
            // Sadap kecil (titik abstrak)
            'BMPG_0ka2'  => ['tma_hulu_cm' => 170, 'tma_hilir_cm' => 154, 'debit_m3s' =>  0.35, 'kapasitas_m3s' =>  0.63, 'saluran' => 'Parigi Secondary Canal',          'elevasi_m' => 213.2, 'luas_area' => 75],
            'BMPG_2ka1'  => ['tma_hulu_cm' => 164, 'tma_hilir_cm' => 149, 'debit_m3s' =>  0.30, 'kapasitas_m3s' =>  0.63, 'saluran' => 'Parigi Secondary Canal',          'elevasi_m' => 211.7, 'luas_area' => 62],
            // ── Ranca Ucing: ~30% dari BPG_3 (1.30) ──
            'BRU_1'      => ['tma_hulu_cm' => 135, 'tma_hilir_cm' => 120, 'debit_m3s' =>  0.40, 'kapasitas_m3s' =>  0.50, 'saluran' => 'Saluran Ranca Ucing',            'elevasi_m' => 207.6, 'luas_area' => 55],
            'BMRU_1te'   => ['tma_hulu_cm' => 128, 'tma_hilir_cm' => 114, 'debit_m3s' =>  0.33, 'kapasitas_m3s' =>  0.50, 'saluran' => 'Ranca Ucing Secondary Canal',    'elevasi_m' => 206.4, 'luas_area' => 48],
            // ── Cikananga: ~50% dari BPG_7 (0.45) ──
            'BCK_1'      => ['tma_hulu_cm' => 142, 'tma_hilir_cm' => 128, 'debit_m3s' =>  0.22, 'kapasitas_m3s' =>  0.31, 'saluran' => 'Saluran Cikananga',              'elevasi_m' => 210.2, 'luas_area' => 85],
            'BMCK_1.Ki'  => ['tma_hulu_cm' => 136, 'tma_hilir_cm' => 123, 'debit_m3s' =>  0.18, 'kapasitas_m3s' =>  0.31, 'saluran' => 'Cikananga Secondary Canal',     'elevasi_m' => 209.1, 'luas_area' => 73],
            // ── Cikukuk: tersadap dari BCD_5 ~0.50 m³/s ──
            'BC_1'       => ['tma_hulu_cm' => 154, 'tma_hilir_cm' => 140, 'debit_m3s' =>  0.50, 'kapasitas_m3s' =>  0.75, 'saluran' => 'Cikukuk Secondary Canal',        'elevasi_m' => 220.8, 'luas_area' => 68],
            'BMC_1ka'    => ['tma_hulu_cm' => 148, 'tma_hilir_cm' => 135, 'debit_m3s' =>  0.42, 'kapasitas_m3s' =>  0.75, 'saluran' => 'Cikukuk Secondary Canal',        'elevasi_m' => 219.9, 'luas_area' => 57],
            // ── Cinanti: ~40% dari BPG_4 (0.90) tersadap ke saluran Cinanti ──
            'BCN_1'    => ['tma_hulu_cm' =>  62, 'tma_hilir_cm' =>  50, 'debit_m3s' =>  0.38, 'kapasitas_m3s' =>  0.50, 'saluran' => 'Cinanti Secondary Canal', 'elevasi_m' => 197.1, 'luas_area' => 58],
            'BCN_2'    => ['tma_hulu_cm' =>  50, 'tma_hilir_cm' =>  39, 'debit_m3s' =>  0.32, 'kapasitas_m3s' =>  0.50, 'saluran' => 'Cinanti Secondary Canal', 'elevasi_m' => 196.3, 'luas_area' => 51],
            'BCN_3'    => ['tma_hulu_cm' =>  38, 'tma_hilir_cm' =>  28, 'debit_m3s' =>  0.26, 'kapasitas_m3s' =>  0.50, 'saluran' => 'Saluran Cinanti',          'elevasi_m' => 195.5, 'luas_area' => 42],
            'BCN_4'    => ['tma_hulu_cm' =>  34, 'tma_hilir_cm' =>  24, 'debit_m3s' =>  0.20, 'kapasitas_m3s' =>  0.50, 'saluran' => 'Cinanti Secondary Canal', 'elevasi_m' => 194.8, 'luas_area' => 36],
            'BCN_5'    => ['tma_hulu_cm' =>  30, 'tma_hilir_cm' =>  21, 'debit_m3s' =>  0.15, 'kapasitas_m3s' =>  0.50, 'saluran' => 'Cinanti Secondary Canal', 'elevasi_m' => 194.1, 'luas_area' => 31],
            'BCN_6'    => ['tma_hulu_cm' =>  27, 'tma_hilir_cm' =>  18, 'debit_m3s' =>  0.11, 'kapasitas_m3s' =>  0.50, 'saluran' => 'Cinanti Secondary Canal', 'elevasi_m' => 193.5, 'luas_area' => 26],
            'BMCN_6ka' => ['tma_hulu_cm' =>  24, 'tma_hilir_cm' =>  16, 'debit_m3s' =>  0.08, 'kapasitas_m3s' =>  0.50, 'saluran' => 'Cinanti Secondary Canal', 'elevasi_m' => 193.0, 'luas_area' => 22],
            // ── Ciduga akhir: BCD_16 (1.55) turun vertikal ke BCD_20 junction ──
            'BCD_17' => ['tma_hulu_cm' => 159, 'tma_hilir_cm' => 143, 'debit_m3s' =>  1.50, 'kapasitas_m3s' =>  2.00, 'saluran' => 'Ciduga Secondary Canal',           'elevasi_m' => 216.95, 'luas_area' => 345],
            'BCD_18' => ['tma_hulu_cm' => 159, 'tma_hilir_cm' => 143, 'debit_m3s' =>  1.45, 'kapasitas_m3s' =>  2.00, 'saluran' => 'Ciduga Secondary Canal',           'elevasi_m' => 216.92, 'luas_area' => 335],
            'BCD_19' => ['tma_hulu_cm' => 158, 'tma_hilir_cm' => 142, 'debit_m3s' =>  1.40, 'kapasitas_m3s' =>  2.00, 'saluran' => 'Ciduga Secondary Canal',           'elevasi_m' => 216.90, 'luas_area' => 322],
            'BCD_20' => ['tma_hulu_cm' => 158, 'tma_hilir_cm' => 142, 'debit_m3s' =>  1.35, 'kapasitas_m3s' =>  2.00, 'saluran' => 'Percabangan Leuw Goong — Ciseureuh', 'elevasi_m' => 216.9,  'luas_area' => 310],
            // ── Ciseureuh: ~45% dari BCD_20 (1.35) = 0.61 m³/s ──
            'BCS_1' => ['tma_hulu_cm' => 165, 'tma_hilir_cm' => 148, 'debit_m3s' =>  0.61, 'kapasitas_m3s' =>  1.00, 'saluran' => 'Saluran Ciseureuh',         'elevasi_m' => 218.3, 'luas_area' => 180],
            'BCS_2' => ['tma_hulu_cm' => 155, 'tma_hilir_cm' => 140, 'debit_m3s' =>  0.52, 'kapasitas_m3s' =>  1.00, 'saluran' => 'Saluran Ciseureuh Hilir',   'elevasi_m' => 216.8, 'luas_area' => 150],
            'BCS_3' => ['tma_hulu_cm' => 145, 'tma_hilir_cm' => 130, 'debit_m3s' =>  0.43, 'kapasitas_m3s' =>  1.00, 'saluran' => 'Saluran Ciseureuh Tengah', 'elevasi_m' => 213.8, 'luas_area' => 110],
            'BCS_4' => ['tma_hulu_cm' => 135, 'tma_hilir_cm' => 121, 'debit_m3s' =>  0.35, 'kapasitas_m3s' =>  1.00, 'saluran' => 'Ciseureuh Secondary Canal', 'elevasi_m' => 212.4, 'luas_area' => 92],
            // ── Kamung Luhuk: sadap dari BLG_1 ──
            'BKL_1' => ['tma_hulu_cm' => 126, 'tma_hilir_cm' => 113, 'debit_m3s' =>  0.12, 'kapasitas_m3s' =>  0.25, 'saluran' => 'Kamung Luhuk Secondary Canal', 'elevasi_m' => 211.0, 'luas_area' => 44],
            'BKL_2' => ['tma_hulu_cm' => 120, 'tma_hilir_cm' => 108, 'debit_m3s' =>  0.15, 'kapasitas_m3s' =>  0.25, 'saluran' => 'Kamung Luhuk Secondary Canal', 'elevasi_m' => 209.8, 'luas_area' => 37],
            // ── Leuw Goong: ~55% dari BCD_20 (1.35) = 0.74 m³/s, menurun ke hilir ──
            'BLG_1'     => ['tma_hulu_cm' => 138, 'tma_hilir_cm' => 122, 'debit_m3s' =>  0.74, 'kapasitas_m3s' =>  1.00, 'saluran' => 'Percabangan Leuw Goong — Kamung Luhuk', 'elevasi_m' => 209.5, 'luas_area' => 120],
            'BLG_2'     => ['tma_hulu_cm' => 134, 'tma_hilir_cm' => 119, 'debit_m3s' =>  0.59, 'kapasitas_m3s' =>  1.00, 'saluran' => 'Leuw Goong Secondary Canal',             'elevasi_m' => 208.8, 'luas_area' => 112],
            'BLG_3'     => ['tma_hulu_cm' => 130, 'tma_hilir_cm' => 116, 'debit_m3s' =>  0.52, 'kapasitas_m3s' =>  1.00, 'saluran' => 'Leuw Goong Secondary Canal',             'elevasi_m' => 208.1, 'luas_area' => 104],
            'BLG_4'     => ['tma_hulu_cm' => 126, 'tma_hilir_cm' => 112, 'debit_m3s' =>  0.46, 'kapasitas_m3s' =>  1.00, 'saluran' => 'Leuw Goong Secondary Canal',             'elevasi_m' => 207.2, 'luas_area' => 97],
            'BLG_5'     => ['tma_hulu_cm' => 118, 'tma_hilir_cm' => 105, 'debit_m3s' =>  0.40, 'kapasitas_m3s' =>  0.75, 'saluran' => 'Leuw Goong Secondary Canal',             'elevasi_m' => 205.8, 'luas_area' => 82],
            'BLG_6'     => ['tma_hulu_cm' =>  92, 'tma_hilir_cm' =>  80, 'debit_m3s' =>  0.34, 'kapasitas_m3s' =>  0.75, 'saluran' => 'Saluran Leuw Goong Hilir',               'elevasi_m' => 200.1, 'luas_area' => 65],
            'BLG_7'     => ['tma_hulu_cm' =>  86, 'tma_hilir_cm' =>  75, 'debit_m3s' =>  0.29, 'kapasitas_m3s' =>  0.75, 'saluran' => 'Leuw Goong Secondary Canal',             'elevasi_m' => 199.3, 'luas_area' => 59],
            'BLG_8'     => ['tma_hulu_cm' =>  82, 'tma_hilir_cm' =>  71, 'debit_m3s' =>  0.24, 'kapasitas_m3s' =>  0.50, 'saluran' => 'Leuw Goong Secondary Canal',             'elevasi_m' => 198.6, 'luas_area' => 54],
            'BLG_9'     => ['tma_hulu_cm' =>  78, 'tma_hilir_cm' =>  68, 'debit_m3s' =>  0.20, 'kapasitas_m3s' =>  0.50, 'saluran' => 'Leuw Goong Secondary Canal',             'elevasi_m' => 197.9, 'luas_area' => 48],
            'BLG_10'    => ['tma_hulu_cm' =>  74, 'tma_hilir_cm' =>  64, 'debit_m3s' =>  0.17, 'kapasitas_m3s' =>  0.50, 'saluran' => 'Leuw Goong Secondary Canal',             'elevasi_m' => 197.2, 'luas_area' => 42],
            'BLG_11'    => ['tma_hulu_cm' =>  70, 'tma_hilir_cm' =>  60, 'debit_m3s' =>  0.14, 'kapasitas_m3s' =>  0.25, 'saluran' => 'Leuw Goong Secondary Canal',             'elevasi_m' => 196.5, 'luas_area' => 36],
            'BLG_12'    => ['tma_hulu_cm' =>  66, 'tma_hilir_cm' =>  57, 'debit_m3s' =>  0.11, 'kapasitas_m3s' =>  0.25, 'saluran' => 'Leuw Goong Secondary Canal',             'elevasi_m' => 195.9, 'luas_area' => 31],
            'BLG_13'    => ['tma_hulu_cm' =>  62, 'tma_hilir_cm' =>  53, 'debit_m3s' =>  0.09, 'kapasitas_m3s' =>  0.25, 'saluran' => 'Leuw Goong Secondary Canal',             'elevasi_m' => 195.3, 'luas_area' => 27],
            'BLG_14'    => ['tma_hulu_cm' =>  58, 'tma_hilir_cm' =>  49, 'debit_m3s' =>  0.07, 'kapasitas_m3s' =>  0.25, 'saluran' => 'Leuw Goong Secondary Canal',             'elevasi_m' => 194.8, 'luas_area' => 23],
            'BLG_15'    => ['tma_hulu_cm' =>  54, 'tma_hilir_cm' =>  46, 'debit_m3s' =>  0.05, 'kapasitas_m3s' =>  0.25, 'saluran' => 'Leuw Goong Secondary Canal',             'elevasi_m' => 194.3, 'luas_area' => 19],
            'BMLG_15ka' => ['tma_hulu_cm' =>  50, 'tma_hilir_cm' =>  42, 'debit_m3s' =>  0.04, 'kapasitas_m3s' =>  0.25, 'saluran' => 'Leuw Goong Secondary Canal',             'elevasi_m' => 193.9, 'luas_area' => 15],
            // ── Sawah Bera: ~50% dari BPG_7 (0.45) ──
            'BSB_1'     => ['tma_hulu_cm' => 122, 'tma_hilir_cm' => 108, 'debit_m3s' =>  0.22, 'kapasitas_m3s' =>  0.31, 'saluran' => 'Sawah Bera Secondary Canal', 'elevasi_m' => 206.0, 'luas_area' => 90],
            'BSB_2'     => ['tma_hulu_cm' =>  95, 'tma_hilir_cm' =>  82, 'debit_m3s' =>  0.17, 'kapasitas_m3s' =>  0.31, 'saluran' => 'Saluran Sawah Bera',          'elevasi_m' => 202.3, 'luas_area' => 78],
            'BSB_3'     => ['tma_hulu_cm' =>  84, 'tma_hilir_cm' =>  72, 'debit_m3s' =>  0.13, 'kapasitas_m3s' =>  0.31, 'saluran' => 'Sawah Bera Secondary Canal', 'elevasi_m' => 200.7, 'luas_area' => 60],
            'BMSB_3ter' => ['tma_hulu_cm' =>  75, 'tma_hilir_cm' =>  64, 'debit_m3s' =>  0.09, 'kapasitas_m3s' =>  0.31, 'saluran' => 'Sawah Bera Secondary Canal', 'elevasi_m' => 199.6, 'luas_area' => 43],
        ];
        foreach ($nodes as &$node) {
            $id = $node['id'];
            if (isset($panelInfo[$id])) {
                $info = $panelInfo[$id];
                $node['panel_luas_area']       = $info['luas_area'];
                $node['panel_tma_hulu']        = $info['tma_hulu_cm'];
                $node['panel_tma_hilir']       = $info['tma_hilir_cm'];
                $node['panel_selisih_tma']     = $info['tma_hulu_cm'] - $info['tma_hilir_cm'];
                $node['panel_debit']           = 0;   // Akan diisi oleh topological BFS sort
                $node['panel_kapasitas']       = $info['kapasitas_m3s'];
                $node['panel_saluran']         = $info['saluran'];
                $node['panel_elevasi_m']       = $info['elevasi_m'];

                // Simpan debit rencana (dari data dummy/perencanaan) sebagai acuan Q_Perintah
                // Ini berbeda dari kapasitas maks saluran — ini adalah target distribusi operasional
                $node['panel_q_perintah_raw']  = $info['debit_m3s'];

                if ($info['luas_area'] > 0) {
                    $node['panel_kb_irigasi']    = round($info['luas_area'] * 1.5, 2);
                    $node['panel_kb_kehilangan'] = round(($info['luas_area'] * 1.5) * 0.2, 2);
                    $node['panel_kb_total']      = round(($info['luas_area'] * 1.5) * 1.2, 2);
                    $node['panel_faktor_k']      = 1.0;
                }

                $node['panel_pct_debit'] = 0; // Akan diisi oleh topological BFS sort

                // Untuk node AWLR tanpa logger DB → set jenis_alat & tma dari data dummy
                // agar panel informasi AWLR (TMA, status siaga) tetap muncul di UI
                // Jika ada sensor DB, injectSensorData() akan overwrite ini dengan data nyata
                if (($node['type'] ?? '') === 'sensor_awlr' && !isset($node['jenis_alat'])) {
                    $node['jenis_alat'] = 'AWLR';
                    $node['tma']        = $info['tma_hulu_cm']; // TMA dummy (cm)
                }
            } elseif ($id === 'AWLR_MID_1') {
                $node['panel_kapasitas']      = 15.00; // Samakan dengan kapasitas saluran primer Copong
                $node['panel_saluran']        = 'Copong Main Canal';
                $node['panel_debit']          = 0;
                $node['panel_pct_debit']      = 0;
                $node['panel_q_perintah_raw'] = 12.45; // Debit rencana bendung (dari data historis)
            } elseif (isset($node['type']) && $node['type'] !== 'title' && $node['type'] !== 'label_text') {
                $node['panel_kapasitas']      = 1.00; // Kapasitas cadangan untuk tersier
                $node['panel_saluran']        = 'Saluran Tersier / Titik Jemput';
                $node['panel_debit']          = 0;
                $node['panel_pct_debit']      = 0;
                $node['panel_q_perintah_raw'] = 0.10; // Debit rencana tersier/titik jemput (0.10 m³/s)
            }
        }
    }

    /**
     * GET /api/skema/node/{nodeId}/history
     *
     * Mengambil historis data sensor 6 jam terakhir untuk satu node AWLR.
     * Digunakan oleh panel informasi di Skema Irigasi untuk menampilkan chart TMA.
     */
    public function getNodeHistory(string $nodeId)
    {
        $logger = t_Logger::where('node_skema_id', $nodeId)->first();

        if (!$logger) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada logger yang terhubung ke node ini.',
                'data'    => [],
            ], 404);
        }

        $tableMain = $logger->tabel_main ?? 't_s16_01';
        $since     = Carbon::now()->subHours(6);

        try {
            $rows = DB::table($tableMain)
                ->where('id_logger', $logger->id_logger)
                ->where('waktu', '>=', $since)
                ->orderBy('waktu', 'asc')
                ->limit(500)
                ->get(['waktu', 's1', 's2', 's3']); // ambil kolom sensor utama

            return response()->json([
                'success'    => true,
                'node_id'    => $nodeId,
                'id_logger'  => $logger->id_logger,
                'jenis_alat' => $logger->jenis_alat,
                'data'       => $rows,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal ambil historis: ' . $e->getMessage(),
                'data'    => [],
            ], 500);
        }
    }

    /**
     * Inject data sensor terkini dari database ke dalam array node topologi.
     */
    private function injectSensorData(array &$nodes, array $edges = []): void
    {
        $loggers = \App\Models\t_Logger::linkedToSkema()
            ->with(['temp16', 'temp19', 'tingkatSiagaAwlr'])
            ->get()
            ->keyBy('node_skema_id');

        if ($loggers->isEmpty()) return;

        $gateConfigs = [];
        $outgoingEdges = [];
        $outgoingTargets = [];
        foreach ($edges as $edge) {
            if (in_array($edge['type'] ?? '', ['primary', 'secondary', 'tertiary'])) {
                $outgoingEdges[$edge['source']][] = $edge['type'];
                if (strpos($edge['target'], 'LBL_') !== 0) {
                    $outgoingTargets[$edge['source']][] = $edge['target'];
                }
            }
        }
        
        $nodeSourceNames = [];
        foreach ($nodes as $n) {
            $nodeSourceNames[$n['id']] = $n['source_name'] ?? $n['label'] ?? $n['id'];
        }

        $customNames = [
            'WEIR_COPONG' => ['sensor1' => 'Pintu Utama Bendung'],
            'BGP_3' => [
                'sensor1' => 'Pintu Sadap Ciduga',
                'sensor2' => 'Pintu Sadap Copong Kanan',
                'sensor3' => 'Pintu Sadap Bagendit'
            ],
            'BPG_0' => [
                'sensor1' => 'Pintu Lanjutan Parigi',
                'sensor2' => 'Pintu Sadap Kanan Parigi'
            ],
            'BPG_3' => [
                'sensor1' => 'Pintu Lanjutan Parigi',
                'sensor2' => 'Pintu Sadap Ranca Ucing'
            ],
            'BPG_7' => [
                'sensor1' => 'Pintu Sadap Cikananga',
                'sensor2' => 'Pintu Sadap Sawah Bera'
            ],
            'BCD_20' => [
                'sensor1' => 'Pintu Sadap Leuw Goong',
                'sensor2' => 'Pintu Sadap Ciseureuh'
            ],
            'BLG_1' => [
                'sensor1' => 'Pintu Lanjutan Leuw Goong',
                'sensor2' => 'Pintu Sadap Kamung Luhuk'
            ],
        ];

        foreach ($nodes as $n) {
            $nodeId = $n['id'];
            if (isset($outgoingEdges[$nodeId])) {
                $types = $outgoingEdges[$nodeId];
                $targets = $outgoingTargets[$nodeId] ?? [];
                
                $maxDoorCount = in_array('primary', $types) ? 3 : (in_array('secondary', $types) ? 2 : 1);
                $max_cm = $loggers[$nodeId]->bukaan_maksimal_cm ?? 100;

                $doors = [];
                for ($i = 1; $i <= $maxDoorCount; $i++) {
                    // Default fallback
                    $doorName = ($maxDoorCount === 3) ? ["Pintu Kiri", "Pintu Tengah", "Pintu Kanan"][$i-1] : 
                                (($maxDoorCount === 2) ? ["Pintu Lanjutan", "Pintu Sadap"][$i-1] : "Pintu Utama");

                    // Coba deteksi arah saluran tujuannya secara otomatis jika ada target
                    if ($maxDoorCount === 2 && isset($targets[$i-1])) {
                        $tgtName = $nodeSourceNames[$targets[$i-1]] ?? '';
                        if ($tgtName) {
                            $prefix = ($i === 1) ? "Pintu Arah" : "Pintu Sadap";
                            // Bersihkan kata-kata seperti 'Secondary Canal' agar tidak terlalu panjang
                            $cleanName = str_replace([' Secondary Canal', ' Sec. Canal', ' Main Canal'], '', $tgtName);
                            // Hindari duplikasi jika sudah tertulis Pintu Arah
                            $doorName = "$prefix $cleanName";
                        }
                    }

                    // Tumpuk dengan customNames jika disediakan secara eksplisit
                    if (isset($customNames[$nodeId]["sensor{$i}"])) {
                        $doorName = $customNames[$nodeId]["sensor{$i}"];
                    }

                    $doors["sensor{$i}"] = ['name' => $doorName, 'max_cm' => $max_cm];
                }
                $gateConfigs[$nodeId] = $doors;
            }
        }

        foreach ($nodes as &$node) {
            if (!isset($node['id']) || !$loggers->has($node['id'])) continue;

            $logger = $loggers[$node['id']];

            // Tentukan data terbaru berdasarkan sensor_count
            $latestData = ($logger->sensor_count == 19) ? $logger->temp19 : $logger->temp16;
            $lastTime   = $latestData?->waktu;
            $diffMin    = $lastTime ? Carbon::parse($lastTime)->diffInMinutes(now()) : null;
            $isOnline   = $diffMin !== null && $diffMin < 60;

            // Khusus AWGC (dan AWLR sementara), buat koneksinya "terus terhubung" agar panel kontrol
            // tetap bisa membaca histori nilai terakhir, tapi status putusnya tetap tersimpan
            if (in_array($logger->jenis_alat, ['AWGC', 'AWLR'])) {
                $node['logger_koneksi_putus'] = !$isOnline;
                $isOnline = true;
            }

            $node['id_logger']  = $logger->id_logger;
            $node['jenis_alat'] = $logger->jenis_alat ?? 'OTHER';
            $node['nama_logger']= $logger->nama_logger;
            $node['is_online']  = $isOnline;
            $node['last_time']  = $lastTime;

            if (!$isOnline) {
                $node['status_alat'] = 'offline';
                // Jika offline (khusus non-AWGC), node tidak diinjek data pembacaan aktual
                continue;
            }

            $node['status_alat'] = 'online';

            // Inject data spesifik berdasarkan jenis alat
            if ($logger->jenis_alat === 'AWLR') {
                // AWLR: TMA diambil murni dari sensor1 (Tinggi Muka Air dalam cm)
                $tma = $latestData ? (float)($latestData->sensor1 ?? 0) : 0;
                $node['tma'] = $tma;

                // Tambahkan data debit AWLR ke panel (jika sensor2 berisi debit)
                // Sementara untuk dummy: panel_debit diisi 0 agar BFS yang bekerja
                $node['panel_debit_awlr'] = $latestData ? (float)($latestData->sensor2 ?? 0) : 0;

                // Tentukan status aliran berdasarkan tingkat siaga AWLR
                $siagaLevels = $logger->tingkatSiagaAwlr->sortByDesc('nilai');
                $node['status']       = $this->classifyFlowStateByTma($tma, $siagaLevels);
                $node['status_siaga'] = $this->classifySiagaByTma($tma, $siagaLevels);

                // Override type ke sensor_awlr agar visual berbeda
                // (Dinonaktifkan: visual tetap pakai tipe asli junction/weir)
                // if (in_array($node['type'], ['junction', 'weir_main'])) {
                //     $node['type'] = 'sensor_awlr';
                // }
            } elseif ($logger->jenis_alat === 'AWGC') {
                $node['bukaan_maksimal_cm'] = $logger->bukaan_maksimal_cm ?? 100;

                // Konfigurasi Gate: gunakan dictionary jika ada, jika tidak default ke sensor1
                $config = $gateConfigs[$node['id']] ?? ['sensor1' => ['name' => 'Pintu Utama', 'max_cm' => $logger->bukaan_maksimal_cm ?? 100]];
                $gates = [];

                foreach ($config as $sensorKey => $gateData) {
                    $bukaanVal = $latestData ? (float)($latestData->{$sensorKey} ?? 0) : 0;
                    
                    // Baca Debit aktual pararel dari sensor4, s5, s6
                    $debitKey = str_replace(
                        ['sensor1', 'sensor2', 'sensor3'], 
                        ['sensor4', 'sensor5', 'sensor6'], 
                        $sensorKey
                    );
                    $debitVal = $latestData ? (float)($latestData->{$debitKey} ?? 0) : 0;

                    $gates[] = [
                        'sensor_id'           => $sensorKey,
                        'name'                => $gateData['name'],
                        'bukaan_persen'       => $bukaanVal,
                        'max_cm'              => $gateData['max_cm'],
                        'debit_aktual_sensor' => $debitVal
                    ];
                }

                $node['gates'] = $gates;

                // Untuk keperluan warna status di diagram, gunakan DEBIT AKTUAL total dari sensor4/5/6
                // dibandingkan kapasitas node untuk menentukan warna yang tepat
                $totalDebitAktual = array_sum(array_column($gates, 'debit_aktual_sensor'));
                $kapasitasNode    = $node['panel_kapasitas'] ?? 15.0;
                $rasioDebit       = $kapasitasNode > 0 ? ($totalDebitAktual / $kapasitasNode) * 100 : 0;

                // Simpan debit aktual ke node agar BFS bisa meneruskannya
                $node['panel_debit_aktual_awgc'] = round($totalDebitAktual, 2);

                // Tentukan status awal node AWGC berdasarkan rasio debit aktual vs kapasitas
                if ($totalDebitAktual <= 0) {
                    $node['status'] = 'dry';      // Tidak ada air keluar
                } elseif ($rasioDebit >= 135) {
                    $node['status'] = 'overflow'; // Ungu - melapaui kapasitas 135%
                } elseif ($rasioDebit > 100) {
                    $node['status'] = 'high';     // Oranye - debit mencapai/melebihi kapasitas
                } elseif ($rasioDebit >= 50) {
                    $node['status'] = 'full';     // Biru - debit normal (50-99%)
                } elseif ($rasioDebit > 0) {
                    $node['status'] = 'trickle';  // Cokelat - kurang aliran
                } else {
                    $node['status'] = 'dry';
                }

                // Override type ke gate_awgc
                // (Dinonaktifkan: visual tetap pakai tipe asli junction/weir)
                // if (in_array($node['type'], ['junction', 'weir_main', 'weir_large'])) {
                //     $node['type'] = 'gate_awgc';
                // }
            }
        }
    }

    /**
     * Klasifikasi flow_state berdasarkan TMA dan tingkat siaga yang dikonfigurasi.
     */
    private function classifyFlowStateByTma(float $tma, $siagaLevels): string
    {
        foreach ($siagaLevels as $siaga) {
            if ($tma >= (float)$siaga->nilai) {
                return match(strtolower($siaga->nama)) {
                    'banjir', 'awas', 'bahaya'   => 'overflow',
                    'siaga 2', 'siaga 3', 'siaga'=> 'high',
                    'siaga 1', 'waspada'         => 'high',
                    default                      => 'full',
                };
            }
        }

        return $tma > 0 ? 'full' : 'dry';
    }

    /**
     * Klasifikasi label status siaga berdasarkan TMA.
     */
    private function classifySiagaByTma(float $tma, $siagaLevels): string
    {
        foreach ($siagaLevels as $siaga) {
            if ($tma >= (float)$siaga->nilai) {
                return $siaga->nama ?? 'Siaga';
            }
        }
        return $tma > 0 ? 'Normal' : 'Kering';
    }

    /**
     * Hitung propagasi Debit Air dari hulu ke hilir berdasarkan persentase pintu
     */
    private function calculateFlowPropagation(array &$nodes, array &$edges): void
    {
        $resolveOperationalNodeId = function (string $nodeId) use (&$edges, &$nodesById, &$nodes): string {
            $visited = [];
            $current = $nodeId;

            while (!isset($visited[$current])) {
                $currentIdx = $nodesById[$current] ?? null;
                $currentNode = $currentIdx !== null ? ($nodes[$currentIdx] ?? null) : null;
                if (($currentNode['type'] ?? null) !== 'corner') {
                    break;
                }

                $visited[$current] = true;
                foreach ($edges as $edgeRef) {
                    if (($edgeRef['source'] ?? null) === $current) {
                        $current = $edgeRef['target'];
                        continue 2;
                    }
                }
                break;
            }

            return $current;
        };

        // 1. Tentukan relasi Pintu ke cabang spesifik
        $branchTargets = [
            'BGP_3' => [
                'sensor1' => 'BCP_Ki_1',
                'sensor2' => 'BCP_Ko_1',
                'sensor3' => 'BAGENDIT',
            ],
            'BPG_0' => [
                'sensor1' => 'BPG_1',
                'sensor2' => 'BMPG_0ka2',
            ],
            'BPG_3' => [
                'sensor1' => 'BPG_4',
                'sensor2' => 'BRU_1',
            ],
            'BPG_7' => [
                'sensor1' => 'BCK_1',
                'sensor2' => 'BSB_1',
            ],
            'BLG_1' => [
                'sensor1' => 'BLG_2', // Lanjutan
                'sensor2' => 'BKL_2', // Sadap Kamung Luhuk
            ],
        ];

        // 2. Index nodes dan set debit = 0
        $nodesById = [];
        foreach ($nodes as $idx => $node) {
            $nodesById[$node['id']] = $idx;
        }

        // 3. Bangun struktur graph dan In-Degree
        $adjacency = [];
        $inDegree = [];
        foreach ($edges as $edge) {
            $source = $edge['source'];
            $target = $edge['target'];
            $adjacency[$source][] = $target;
            
            if (!isset($inDegree[$target])) $inDegree[$target] = 0;
            $inDegree[$target]++;
            
            if (!isset($inDegree[$source])) $inDegree[$source] = 0;
        }

        // 4. Mulai BFS Topologi dari titik hulu utama (WEIR_COPONG)
        $queue = ['WEIR_COPONG'];
        $idx = $nodesById['WEIR_COPONG'] ?? -1;
        if ($idx !== -1) {
            // Gunakan total debit aktual dari sensor (flow meter) AWGC jika ada, jika tidak default ke 15.00 m3/s (kapasitas rencana)
            $debitAwal = $nodes[$idx]['panel_debit_aktual_awgc'] ?? round($nodes[$idx]['panel_kapasitas'] ?? 15.00, 2);
            $nodes[$idx]['panel_debit'] = $debitAwal;
            if (($nodes[$idx]['panel_kapasitas'] ?? 0) > 0) {
                $nodes[$idx]['panel_pct_debit'] = round(($debitAwal / $nodes[$idx]['panel_kapasitas']) * 100);
            }
        }

        while (count($queue) > 0) {
            $currId = array_shift($queue);
            if (!isset($adjacency[$currId])) continue;
            
            $currIdx = $nodesById[$currId] ?? -1;
            if ($currIdx === -1) continue;
            
            $currNode = $nodes[$currIdx];
            $incomingFlow = $currNode['panel_debit'] ?? 0;
            
            $outgoingEdges = $adjacency[$currId];
            $totalEdges = count($outgoingEdges);
            
            if ($totalEdges === 0) continue;

            $isAwgc = isset($currNode['jenis_alat']) && $currNode['jenis_alat'] === 'AWGC' && !empty($currNode['gates']);
            $isAwlr = isset($currNode['jenis_alat']) && $currNode['jenis_alat'] === 'AWLR';
            $gateDebitMap = [];
            if ($isAwgc) {
                foreach ($currNode['gates'] as $gate) {
                    $sensorId = $gate['sensor_id'] ?? null;
                    if ($sensorId) {
                        $gateDebitMap[$sensorId] = (float) ($gate['debit_aktual_sensor'] ?? 0);
                    }
                }
            }

            $defaultTargetSensorMap = [];
            foreach (array_values($outgoingEdges) as $edgeIndex => $targetEdgeId) {
                $defaultTargetSensorMap[$targetEdgeId] = 'sensor' . ($edgeIndex + 1);
            }
            
            foreach ($outgoingEdges as $targetId) {
                $targetIdx = $nodesById[$targetId] ?? -1;
                if ($targetIdx === -1) continue;
                
                $flowToPass = 0;
                $passedPct = 100;
                
                // ── Cara B: Hanya baca sensor untuk junction utama (ada di branchTargets) ──
                // AWGC tanpa mapping branchTargets → distribusi proporsional (tidak pakai rata-rata sensor mentah)
                if ($isAwgc && isset($branchTargets[$currId])) {
                    // Junction utama yang sensornya terkalibrasi per-cabang (BGP_3, BPG_0, dll.)
                    $sensorKey = array_search($targetId, $branchTargets[$currId]);
                    if ($sensorKey !== false) {
                        $flowToPass = $gateDebitMap[$sensorKey] ?? 0;
                    } elseif (strpos($targetId, 'LBL_') === 0) {
                        $labelCap = $nodes[$targetIdx]['panel_kapasitas'] ?? 1;
                        $flowToPass = round($labelCap * 0.75, 2);
                    } else {
                        // Edge LBL_ atau edge lain tidak di-map → proporsional kapasitas
                        $totalCap = 0;
                        foreach ($outgoingEdges as $tgt) {
                            $tgtIdx = $nodesById[$tgt] ?? -1;
                            if ($tgtIdx !== -1) $totalCap += ($nodes[$tgtIdx]['panel_kapasitas'] ?? 0);
                        }
                        $maxTargetFlow = $nodes[$targetIdx]['panel_kapasitas'] ?? 0;
                        $flowToPass    = ($totalCap > 0) ? ($incomingFlow * ($maxTargetFlow / $totalCap)) : ($incomingFlow / $totalEdges);
                    }
                } elseif ($isAwgc && isset($defaultTargetSensorMap[$targetId])) {
                    // Untuk dummy AWGC di ruas lanjutan, pakai debit sensor per-ruas langsung
                    // agar warna edge konsisten dengan seed demo.
                    $sensorKey = $defaultTargetSensorMap[$targetId];
                    $flowToPass = $gateDebitMap[$sensorKey] ?? 0;
                } elseif ($isAwlr && $totalEdges === 1 && (($currNode['panel_debit_awlr'] ?? 0) > 0)) {
                    // AWLR dummy dapat membawa debit jalur lanjutan via sensor2.
                    $flowToPass = (float) $currNode['panel_debit_awlr'];
                } else {
                    // Non-AWGC  —atau—  AWGC tanpa branchTargets (node biasa: BGP_1, BCD_1, BLG_3, dll.)
                    // → Hukum Konservasi Massa: ikuti debit masuk, bagi proporsional ke hilir
                    $totalCap = 0;
                    foreach ($outgoingEdges as $tgt) {
                        $tgtIdx = $nodesById[$tgt] ?? -1;
                        if ($tgtIdx !== -1) $totalCap += ($nodes[$tgtIdx]['panel_kapasitas'] ?? 0);
                    }
                    $maxTargetFlow = $nodes[$targetIdx]['panel_kapasitas'] ?? 0;
                    $flowToPass    = ($totalCap > 0) ? ($incomingFlow * ($maxTargetFlow / $totalCap)) : ($incomingFlow / $totalEdges);
                }
                
                // CATAT Q PERINTAH (Tuntutan murni sebelum air mengalami loss resapan)
                $nodes[$targetIdx]['panel_q_perintah'] = ($nodes[$targetIdx]['panel_q_perintah'] ?? 0) + $flowToPass;
                
                // Terapkan faktor kehilangan air (Water Loss) / Penyusutan sepanjang perjalanan saluran
                // Simulasi standar: Setiap melewati segmen, air berkurang 5% karena resapan, evaporasi, dsb.
                $lossFactor = 0.05; 
                // Abaikan loss jika ujungnya hanya sekadar node visual pembengkok garis/label (jaraknya 0 meter)
                $targetNodeType = $nodes[$targetIdx]['type'] ?? null;
                if (strpos($targetId, 'LBL_') === 0 || $targetNodeType === 'corner') {
                    $lossFactor = 0.00;
                }
                
                $flowReceived = $flowToPass * (1 - $lossFactor);

                // Set dan akumulasi debit bersih yang diterima
                $nodes[$targetIdx]['panel_debit'] = ($nodes[$targetIdx]['panel_debit'] ?? 0) + $flowReceived;
                $nodes[$targetIdx]['panel_debit'] = round($nodes[$targetIdx]['panel_debit'], 2);
                
                $kap = $nodes[$targetIdx]['panel_kapasitas'] ?? 0;
                
                // Inherit capacity if missing (useful for straight lines bending via dummy corner nodes)
                if ($kap <= 0 && isset($currNode['panel_kapasitas']) && $currNode['panel_kapasitas'] > 0) {
                    $kap = $currNode['panel_kapasitas'];
                    $nodes[$targetIdx]['panel_kapasitas'] = $kap;
                }

                if ($kap > 0) {
                    // Tanpa min(100) agar kondisi High (>=120%) dan Overflow (>=135%) bisa terdeteksi
                    $nodes[$targetIdx]['panel_pct_debit'] = round(($nodes[$targetIdx]['panel_debit'] / $kap) * 100);
                } else if (isset($currNode['panel_pct_debit'])) {
                    $nodes[$targetIdx]['panel_pct_debit'] = $currNode['panel_pct_debit'];
                }

                // SIMPAN DEBIT SPESIFIK RUAS KEDALAM EDGE (PIPA)
                // Agar popup saat "Ruas" diklik murni merepresentasikan debit pintu asalnya,
                // buka debit dari node target yang mungkin di-override jika ia AWGC sendiri.
                foreach ($edges as &$eRef) {
                    if ($eRef['source'] === $currId && $eRef['target'] === $targetId) {
                        $effectiveTargetId = $resolveOperationalNodeId($targetId);
                        $effectiveTargetIdx = $nodesById[$effectiveTargetId] ?? $targetIdx;
                        $effectiveCap = $nodes[$effectiveTargetIdx]['panel_kapasitas'] ?? $kap;

                        $eRef['panel_debit'] = round($flowToPass, 2);
                        if (($effectiveCap ?? 0) > 0) {
                            $eRef['panel_pct_debit'] = round(($flowToPass / $effectiveCap) * 100);
                        }
                        break;
                    }
                }

                $inDegree[$targetId]--;
                if ($inDegree[$targetId] == 0) {
                    $queue[] = $targetId;
                }
            }
        }
        
        // HITUNG KALKULASI WATER BALANCE FINAL
        // Q_Perintah = debit rencana operasional (dari data dummy/perencanaan, bukan kapasitas maks)
        // Q_Terukur  = debit aktual hasil propagasi BFS (sensor AWGC / susut saluran)
        foreach ($nodes as &$node) {
            // Prioritaskan debit rencana dummy; fallback ke kapasitas jika tidak ada
            $q_perintah = $node['panel_q_perintah_raw'] ?? ($node['panel_kapasitas'] ?? 0);

            // Untuk AWGC: gunakan debit AKTUAL dari Flow Meter sensor (bukan estimasi BFS)
            // BFS hanya dipakai untuk node non-sensor yang tidak punya pembacaan langsung
            if (($node['jenis_alat'] ?? '') === 'AWGC' && isset($node['panel_debit_aktual_awgc'])) {
                $node['panel_debit']     = $node['panel_debit_aktual_awgc'];
                $kap = $node['panel_kapasitas'] ?? 0;
                if ($kap > 0) {
                    $node['panel_pct_debit'] = round(($node['panel_debit'] / $kap) * 100);
                }
            }

            $q_terukur = $node['panel_debit'] ?? 0;

            $node['panel_q_perintah'] = round($q_perintah, 2);
            $node['panel_q_terukur']  = round($q_terukur, 2);

            // Error = selisih antara debit yang sampai vs yang direncanakan
            $errVal = $q_terukur - $q_perintah;
            $node['panel_wb_error_val'] = round($errVal, 2);

            // Error dalam persen terhadap rencana, hindari pembagian nol
            $errPct = $q_perintah > 0 ? round(($errVal / $q_perintah) * 100, 1) : 0;
            $node['panel_wb_error_pct'] = $errPct;

            // Volume defisit/surplus per jam (m³/jam)
            $node['panel_wb_selisih_vol'] = round($errVal * 3600);

            // Klasifikasi status water balance
            if ($q_perintah <= 0) {
                // Node tanpa target distribusi (tersier/corner/label) tidak dievaluasi
                $node['panel_wb_status'] = 'Tidak dievaluasi';
            } elseif ($errPct <= -8) {
                $node['panel_wb_status'] = 'Kurang aliran';
            } elseif ($errPct >= 8) {
                $node['panel_wb_status'] = 'Kelebihan aliran';
            } elseif ($errPct > 2.5 || $errPct < -2.5) {
                $node['panel_wb_status'] = 'Mendekati target';
            } else {
                $node['panel_wb_status'] = 'Stabil';
            }

            // ── Warna Pipa berdasarkan Rasio Debit vs Kapasitas Penampang ──
            // AWLR tidak diwarnai di sini — statusnya sudah dari classifyFlowStateByTma()
            if (($node['jenis_alat'] ?? $node['type'] ?? '') !== 'AWLR' &&
                ($node['jenis_alat'] ?? '') !== 'AWGC' &&
                !in_array($node['type'] ?? '', ['title', 'label_text', 'label_yellow', 'sensor_awlr'])) {
                $pct = $node['panel_pct_debit'] ?? 0;
                if ($pct <= 0 || ($node['status'] ?? '') === 'closed') {
                    $node['status'] = 'dry';       // Abu-abu / kering
                } elseif ($pct >= 135) {
                    $node['status'] = 'overflow';  // Ungu/Magenta — melampaui 135% kapasitas
                } elseif ($pct > 100) {
                    $node['status'] = 'high';      // Oranye — penuh/melebihi kapasitas normal
                } elseif ($pct >= 50) {
                    $node['status'] = 'full';      // Biru — debit stabil (50–99%)
                } else {
                    $node['status'] = 'trickle';   // Cokelat — kurang aliran (<50%)
                }
            }
        }
    }
}
