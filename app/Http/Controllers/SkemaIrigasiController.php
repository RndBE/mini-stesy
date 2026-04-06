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

    public function getData()
    {
        // Topology modeled after Copong Weir image layout
        $topology = [
            'nodes' => [
                ['id' => 'TITLE', 'label' => "SKEMA PEMBAGIAN AIR IRIGASI\nDAERAH LEUWIGOONG", 'x' => 1000, 'y' => 50, 'type' => 'title'],
                
                // MAIN WEIR
                ['id' => 'WEIR_COPONG', 'label' => '', 'x' => 200, 'y' => 200, 'type' => 'weir_large'],
                // Teks dipisah agar tidak menabrak garis, digeser ke kanan
                ['id' => 'LBL_WEIR_COPONG', 'label' => 'COPONG WEIR', 'x' => 200, 'y' => 150, 'type' => 'label_text'],
                
                // ==============================
                // COPONG MAIN CANAL (Kanan - Horizontal)
                // ==============================
                ['id' => 'BGP_1', 'label' => '', 'x' => 300, 'y' => 200, 'type' => 'junction', 'source_name' => 'Copong Main Canal', 'status' => 'open'],
                ['id' => 'BGP_2', 'label' => '', 'x' => 400, 'y' => 200, 'type' => 'junction', 'source_name' => 'Copong Main Canal', 'status' => 'open'],
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
                ['source' => 'BGP_3', 'target' => 'BCP_Ko_1', 'type' => 'primary'],
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
                ['source' => 'WEIR_COPONG', 'target' => 'BGP_1', 'type' => 'primary'],
                ['source' => 'BGP_1', 'target' => 'BGP_2', 'type' => 'primary'],
                ['source' => 'BGP_2', 'target' => 'BGP_3', 'type' => 'primary'],
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
                ['source' => 'BGP_3', 'target' => 'BAGENDIT', 'type' => 'secondary'], // drop to bagendit
                ['source' => 'BAGENDIT', 'target' => 'BPG_0', 'type' => 'secondary'], // bagendit to parigi start
                ['source' => 'BPG_0', 'target' => 'BPG_1', 'type' => 'secondary'],
                ['source' => 'BPG_1', 'target' => 'BPG_2', 'type' => 'secondary'],
                ['source' => 'BPG_2', 'target' => 'BPG_3', 'type' => 'secondary'],
                ['source' => 'BPG_3', 'target' => 'BPG_4', 'type' => 'secondary'],
                ['source' => 'BPG_4', 'target' => 'BPG_5', 'type' => 'secondary'],
                ['source' => 'BPG_5', 'target' => 'BPG_6', 'type' => 'secondary'],
                ['source' => 'BPG_6', 'target' => 'BPG_7', 'type' => 'secondary'],
                ['source' => 'BPG_7', 'target' => 'BCK_1', 'type' => 'secondary', 'status' => 'trickle'],
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
                ['source' => 'BC_1', 'target' => 'LBL_BC_1', 'type' => 'tertiary', 'status' => 'closed'],

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
                ['source' => 'BLG_1', 'target' => 'BKL_2', 'type' => 'secondary', 'status' => 'high'],
                ['source' => 'BKL_2', 'target' => 'BKL_1', 'type' => 'secondary'],

                // ==============================
                // LEUW GOONG & SAWAH BERA
                // ==============================
                // BCD.16 -> CORNER -> BCD.17 -> BCD.20
                ['source' => 'BCD_16', 'target' => 'CORNER_BCD16', 'type' => 'secondary'],
                ['source' => 'CORNER_BCD16', 'target' => 'BCD_17', 'type' => 'secondary'],
                ['source' => 'BCD_17', 'target' => 'BCD_18', 'type' => 'secondary'],
                ['source' => 'BCD_18', 'target' => 'BCD_19', 'type' => 'secondary'],
                ['source' => 'BCD_19', 'target' => 'BCD_20', 'type' => 'secondary'],
                
                // Dari BCD.20 belok kiri 90-derajat lalu turun jadi BLG (Main canal)
                ['source' => 'BCD_20', 'target' => 'BLG_1', 'type' => 'secondary'],
                ['source' => 'BLG_1', 'target' => 'BLG_2', 'type' => 'secondary', 'status' => 'overflow'],
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
                ['source' => 'BLG_12', 'target' => 'LBL_BLG_12', 'type' => 'tertiary', 'status' => 'trickle'],
                
                ['source' => 'BPG_7', 'target' => 'BSB_1', 'type' => 'secondary', 'status' => 'high'],
                ['source' => 'BSB_1', 'target' => 'BSB_2', 'type' => 'secondary'],
                ['source' => 'BSB_2', 'target' => 'BSB_3', 'type' => 'secondary'],
                ['source' => 'BSB_3', 'target' => 'BMSB_3ter', 'type' => 'secondary'],
                
            ]
        ];

        // Inject data sensor AWLR/AWGC dari database ke node yang terhubung
        // Jika belum ada mapping node_skema_id, data statis tetap digunakan
        $this->injectSensorData($topology['nodes']);

        return response()->json($topology);
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
     * Setiap node yang memiliki id_logger yang cocok akan dilengkapi dengan:
     * - jenis_alat (AWLR/AWGC)
     * - data sensor terakhir (TMA, debit, bukaan)
     * - status (online/offline)
     * - flow_state yang akurat sesuai data nyata
     */
    private function injectSensorData(array &$nodes): void
    {
        // Ambil semua logger yang terhubung ke skema irigasi
        $loggers = t_Logger::linkedToSkema()
            ->with(['temp16', 'temp19', 'tingkatSiagaAwlr'])
            ->get()
            ->keyBy('node_skema_id');

        if ($loggers->isEmpty()) {
            return; // Belum ada logger yang dikonfigurasi, gunakan data statis
        }

        foreach ($nodes as &$node) {
            if (!isset($node['id']) || !$loggers->has($node['id'])) {
                continue;
            }

            $logger = $loggers[$node['id']];

            // Tentukan data terbaru berdasarkan sensor_count
            $latestData = ($logger->sensor_count == 19) ? $logger->temp19 : $logger->temp16;
            $lastTime   = $latestData?->waktu;
            $diffMin    = $lastTime ? Carbon::parse($lastTime)->diffInMinutes(now()) : null;
            $isOnline   = $diffMin !== null && $diffMin < 60;

            $node['id_logger']  = $logger->id_logger;
            $node['jenis_alat'] = $logger->jenis_alat ?? 'OTHER';
            $node['nama_logger']= $logger->nama_logger;
            $node['is_online']  = $isOnline;
            $node['last_time']  = $lastTime;

            if (!$isOnline) {
                $node['status_alat'] = 'offline';
                // Jika offline, node ditampilkan sebagai dry (tidak ada aliran)
                // tapi JANGAN override status statis yang sudah ada jika belum ada logger
                continue;
            }

            $node['status_alat'] = 'online';

            // Inject data spesifik berdasarkan jenis alat
            if ($logger->jenis_alat === 'AWLR') {
                $tma = $latestData ? (float)($latestData->s1 ?? $latestData->s2 ?? 0) : 0;
                $node['tma'] = $tma;

                // Tentukan flow_state berdasarkan tingkat siaga AWLR
                $siagaLevels = $logger->tingkatSiagaAwlr->sortByDesc('nilai_batas');
                $node['flow_state']   = $this->classifyFlowStateByTma($tma, $siagaLevels);
                $node['status_siaga'] = $this->classifySiagaByTma($tma, $siagaLevels);

                // Override type ke sensor_awlr agar visual berbeda
                if (in_array($node['type'], ['junction', 'weir_main'])) {
                    $node['type'] = 'sensor_awlr';
                }
            } elseif ($logger->jenis_alat === 'AWGC') {
                // Untuk AWGC, posisi bukaan bisa dari kolom sensor tertentu
                $bukaan = $latestData ? (float)($latestData->s1 ?? 0) : 0;
                $node['bukaan_persen']     = $bukaan;
                $node['bukaan_maksimal_cm']= $logger->bukaan_maksimal_cm ?? 100;

                // Persentase bukaan menentukan flow_state downstream
                if ($bukaan <= 0) {
                    $node['status'] = 'closed';
                } elseif ($bukaan < 30) {
                    $node['status'] = 'trickle';
                } elseif ($bukaan < 80) {
                    $node['status'] = 'open';
                } else {
                    $node['status'] = 'overflow';
                }

                // Override type ke gate_awgc
                if (in_array($node['type'], ['junction', 'weir_main', 'weir_large'])) {
                    $node['type'] = 'gate_awgc';
                }
            }
        }
    }

    /**
     * Klasifikasi flow_state berdasarkan TMA dan tingkat siaga yang dikonfigurasi.
     */
    private function classifyFlowStateByTma(float $tma, $siagaLevels): string
    {
        foreach ($siagaLevels as $siaga) {
            if ($tma >= (float)$siaga->nilai_batas) {
                return match($siaga->nama_siaga) {
                    'Banjir', 'banjir'   => 'overflow',
                    'Siaga 2', 'siaga_2' => 'high',
                    'Siaga 1', 'siaga_1' => 'high',
                    default              => 'full',
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
            if ($tma >= (float)$siaga->nilai_batas) {
                return $siaga->nama_siaga ?? 'Siaga';
            }
        }
        return $tma > 0 ? 'Normal' : 'Kering';
    }
}
