<?php

namespace Database\Seeders;

use App\Http\Controllers\SkemaIrigasiController;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SkemaIrigasiDummySeeder extends Seeder
{
    private array $nodes = [
        ['id' => 'AWLR_MID_1',  'nama' => 'AWLR Copong Main Canal', 'jenis' => 'AWLR', 'bukaan' => null, 'sensor1' => 60, 'sensor2' => 11.25, 'note' => 'NORMAL - Arus deras dari bendung utama'],
        ['id' => 'AWLR_MAIN',   'nama' => 'AWLR Copong Pra-Cabang', 'jenis' => 'AWLR', 'bukaan' => null, 'sensor1' => 58, 'sensor2' => 11.25, 'note' => 'NORMAL - Sebelum pecah ke sekunder'],
        ['id' => 'AWLR_KO',     'nama' => 'AWLR Copong Kanan Main', 'jenis' => 'AWLR', 'bukaan' => null, 'sensor1' => 62, 'sensor2' => 3.75, 'note' => 'NORMAL - Jalur pipa kanan'],
        ['id' => 'AWLR_PARIGI', 'nama' => 'AWLR Parigi Intake',     'jenis' => 'AWLR', 'bukaan' => null, 'sensor1' => 55, 'sensor2' => 1.88, 'note' => 'NORMAL - Saluran Parigi'],
        ['id' => 'AWLR_LG',     'nama' => 'AWLR Pra-Split LG/CS',   'jenis' => 'AWLR', 'bukaan' => null, 'sensor1' => 50, 'sensor2' => 1.50, 'note' => 'NORMAL - Percabangan ujung'],

        ['id' => 'BGP_1',    'nama' => 'AWLR BGP.1',         'jenis' => 'AWLR', 'bukaan' => null, 'sensor1' => 55, 'note' => 'FULL - TMA normal hilir bendung'],
        ['id' => 'BCD_5',    'nama' => 'AWLR BCD.5',         'jenis' => 'AWLR', 'bukaan' => null, 'sensor1' => 62, 'note' => 'FULL - Normal Ciduga'],
        ['id' => 'BCD_12',   'nama' => 'AWLR BCD.12',        'jenis' => 'AWLR', 'bukaan' => null, 'sensor1' => 65, 'note' => 'NORMAL - Ciduga'],
        ['id' => 'BAGENDIT', 'nama' => 'AWLR Situ Bagendit', 'jenis' => 'AWLR', 'bukaan' => null, 'sensor1' => 61, 'note' => 'FULL - Situ Bagendit normal'],
        ['id' => 'BPG_3',    'nama' => 'AWLR BPG.3',         'jenis' => 'AWLR', 'bukaan' => null, 'sensor1' => 48, 'note' => 'FULL - Normal Parigi'],
        ['id' => 'BLG_6',    'nama' => 'AWLR BLG.6',         'jenis' => 'AWLR', 'bukaan' => null, 'sensor1' => 52, 'note' => 'FULL - Normal Leuw Goong'],
        ['id' => 'BCN_3',    'nama' => 'AWLR Cinanti.3',     'jenis' => 'AWLR', 'bukaan' => null, 'sensor1' => 38, 'note' => 'NORMAL - Cinanti'],
        ['id' => 'BSB_2',    'nama' => 'AWLR Sawah Bera.2',  'jenis' => 'AWLR', 'bukaan' => null, 'sensor1' => 55, 'note' => 'NORMAL - Sawah Bera'],
        ['id' => 'BCS_2',    'nama' => 'AWLR Ciseureuh.2',   'jenis' => 'AWLR', 'bukaan' => null, 'sensor1' => 57, 'note' => 'FULL - Normal Ciseureuh'],
    ];

    private array $siagaLevels = [
        ['id_status' => 1, 'nama' => 'Normal',  'nilai' => 30,  'warna' => '#22c55e'],
        ['id_status' => 2, 'nama' => 'Siaga 1', 'nilai' => 80,  'warna' => '#f59e0b'],
        ['id_status' => 3, 'nama' => 'Banjir',  'nilai' => 150, 'warna' => '#ef4444'],
    ];

    private int $idLoggerBase = 90001;
    private int $instansiId = 1;

    private function resolveOperationalTarget(?string $targetId, array $nodesById, array $edges): ?string
    {
        if (!$targetId) {
            return null;
        }

        $visited = [];
        $current = $targetId;

        while ($current && !isset($visited[$current])) {
            $visited[$current] = true;
            $node = $nodesById[$current] ?? null;

            if (!$node || ($node['type'] ?? null) !== 'corner') {
                return $current;
            }

            $nextEdge = collect($edges)->first(fn ($edge) => ($edge['source'] ?? null) === $current);
            $current = $nextEdge['target'] ?? null;
        }

        return $targetId;
    }

    public function run(): void
    {
        $this->command->info('');
        $this->command->info('SkemaIrigasiDummySeeder - Mulai...');

        DB::table('t_s16_01')->whereBetween('id_logger', [90001, 90099])->delete();
        DB::table('temp_s16_latest')->whereBetween('id_logger', [90001, 90099])->delete();
        DB::table('tingkat_siaga_awlr')->whereBetween('id_logger', [90001, 90099])->delete();
        DB::table('t_logger')->whereBetween('id_logger', [90001, 90099])->delete();

        $topology = SkemaIrigasiController::getRawTopology();
        $edges = $topology['edges'] ?? [];
        $outgoingEdgeTypes = [];
        $outgoingTargets = [];

        foreach ($edges as $edge) {
            if (!in_array($edge['type'] ?? '', ['primary', 'secondary', 'tertiary'], true)) {
                continue;
            }

            $source = $edge['source'];
            $outgoingEdgeTypes[$source][] = $edge['type'];
            $outgoingTargets[$source][] = $edge['target'];
        }

        $panelCapacities = SkemaIrigasiController::getPanelCapacities();
        $nodesById = collect($topology['nodes'] ?? [])->keyBy('id')->all();
        $explicitBranchTargets = [
            'BGP_3' => ['sensor1' => 'BCP_Ki_1', 'sensor2' => 'BCP_Ko_1', 'sensor3' => 'BAGENDIT'],
            'BPG_0' => ['sensor1' => 'BPG_1', 'sensor2' => 'BMPG_0ka2'],
            'BPG_3' => ['sensor1' => 'BPG_4', 'sensor2' => 'BRU_1'],
            'BPG_7' => ['sensor1' => 'BCK_1', 'sensor2' => 'BSB_1'],
            'BCD_20' => ['sensor1' => 'BCS_1', 'sensor2' => 'BLG_1'],
            'BLG_1' => ['sensor1' => 'BLG_2', 'sensor2' => 'BKL_2'],
        ];

        foreach ($topology['nodes'] as $node) {
            $nodeId = $node['id'] ?? null;
            $nodeType = $node['type'] ?? null;

            if (!$nodeId || in_array($nodeType, ['title', 'label_text', 'label_yellow'], true)) {
                continue;
            }

            if ($nodeType === 'corner') {
                continue;
            }

            if ($nodeType === 'sensor_awlr') {
                $alreadyExists = collect($this->nodes)->contains(fn ($existing) => $existing['id'] === $nodeId);
                if (!$alreadyExists) {
                    $this->nodes[] = [
                        'id' => $nodeId,
                        'nama' => 'AWLR ' . str_replace('AWLR_', '', $nodeId),
                        'jenis' => 'AWLR',
                        'bukaan' => null,
                        'sensor1' => 55,
                        'sensor2' => null,
                        'note' => 'DYNAMIC AWLR SCADA',
                    ];
                }
                continue;
            }

            if (!isset($outgoingEdgeTypes[$nodeId])) {
                continue;
            }

            $types = $outgoingEdgeTypes[$nodeId];
            $maxDoorCount = in_array('primary', $types, true) ? 3 : (in_array('secondary', $types, true) ? 2 : 1);
            $nodeName = $maxDoorCount > 1 ? 'AWGC Percabangan ' . $nodeId : 'AWGC Pintu ' . $nodeId;
            $kapasitasNode = $panelCapacities[$nodeId] ?? 0.5;
            $targets = $outgoingTargets[$nodeId] ?? [];

            if (isset($explicitBranchTargets[$nodeId])) {
                $target1 = $explicitBranchTargets[$nodeId]['sensor1'] ?? null;
                $target2 = $explicitBranchTargets[$nodeId]['sensor2'] ?? null;
                $target3 = $explicitBranchTargets[$nodeId]['sensor3'] ?? null;
            } else {
                $target1 = $this->resolveOperationalTarget($targets[0] ?? null, $nodesById, $edges);
                $target2 = $this->resolveOperationalTarget($targets[1] ?? null, $nodesById, $edges);
                $target3 = $this->resolveOperationalTarget($targets[2] ?? null, $nodesById, $edges);
            }

            $kapTgt1 = $target1 ? ($panelCapacities[$target1] ?? $kapasitasNode) : $kapasitasNode;
            $kapTgt2 = $target2 ? ($panelCapacities[$target2] ?? $kapasitasNode) : $kapasitasNode;
            $kapTgt3 = $target3 ? ($panelCapacities[$target3] ?? $kapasitasNode) : $kapasitasNode;

            $this->nodes[] = [
                'id' => $nodeId,
                'nama' => $nodeName,
                'jenis' => 'AWGC',
                'bukaan' => 100,
                'sensor1' => $target1 ? 75 : null,
                'sensor2' => $target2 ? 75 : null,
                'sensor3' => $target3 ? 75 : null,
                'debit1' => $target1 ? round($kapTgt1 * 0.75, 2) : 0,
                'debit2' => $target2 ? round($kapTgt2 * 0.75, 2) : null,
                'debit3' => $target3 ? round($kapTgt3 * 0.75, 2) : null,
                'note' => 'DYNAMIC AWGC - Target Tgt1:' . $kapTgt1,
            ];
        }

        $now = now()->format('Y-m-d H:i:s');

        foreach ($this->nodes as $idx => $node) {
            $idLogger = $this->idLoggerBase + $idx;
            $nodeId = $node['id'];
            $jenis = $node['jenis'];

            DB::table('t_logger')->upsert(
                [[
                    'id_logger' => $idLogger,
                    'instansi_id' => $this->instansiId,
                    'nama_logger' => $node['nama'],
                    'tabel_main' => 't_s16_01',
                    'jeda_notif' => 60,
                    'idlokasi' => null,
                    'id_katlogger' => null,
                    'jenis_alat' => $jenis,
                    'node_skema_id' => $nodeId,
                    'bukaan_maksimal_cm' => $node['bukaan'],
                    'sensor_count' => 16,
                    'status_perbaikan' => 'normal',
                ]],
                ['id_logger'],
                ['nama_logger', 'jenis_alat', 'node_skema_id', 'bukaan_maksimal_cm', 'sensor_count']
            );

            if ($jenis === 'AWLR') {
                foreach ($this->siagaLevels as $lvlIdx => $lvl) {
                    DB::table('tingkat_siaga_awlr')->upsert(
                        [[
                            'id' => 9000 + ($idx * 10) + $lvlIdx,
                            'id_logger' => $idLogger,
                            'id_status' => $lvl['id_status'],
                            'nama' => $lvl['nama'],
                            'nilai' => $lvl['nilai'],
                            'status' => 1,
                            'warna' => $lvl['warna'],
                        ]],
                        ['id'],
                        ['nama', 'nilai', 'warna', 'id_logger']
                    );
                }
            }

            $historyData = [];
            $nowCarbon = now();
            $baseS1 = $node['sensor1'] ?? null;
            $baseS2 = $node['sensor2'] ?? null;
            $baseS3 = $node['sensor3'] ?? null;
            $latestRow = [];

            for ($min = 60; $min >= 0; $min--) {
                $time = (clone $nowCarbon)->subMinutes($min);
                $menit = (int) $time->format('i');
                $rad = deg2rad($menit * 12.0);

                $wave1 = 1.0 + 0.20 * sin($rad);
                $valS1 = ($jenis === 'AWLR' && $baseS1 !== null) ? round($baseS1 * $wave1) : ($baseS1 ?? 0);
                $valS2 = $baseS2 ?? 0;
                $valS3 = $baseS3 ?? 0;

                $s4 = 0;
                $s5 = 0;
                $s6 = 0;
                if ($jenis === 'AWGC') {
                    $s4 = round((float) ($node['debit1'] ?? 0), 2);
                    $s5 = round((float) ($node['debit2'] ?? 0), 2);
                    $s6 = round((float) ($node['debit3'] ?? 0), 2);
                }

                $rowData = [
                    'id_logger' => $idLogger,
                    'waktu' => $time->format('Y-m-d H:i:s'),
                    'sensor1' => $valS1 ?? 0,
                    'sensor2' => $valS2 ?? 0,
                    'sensor3' => $valS3 ?? 0,
                    'sensor4' => $s4,
                    'sensor5' => $s5,
                    'sensor6' => $s6,
                    'sensor7' => 0,
                    'sensor8' => 0,
                    'sensor9' => 0,
                    'sensor10' => 0,
                    'sensor11' => 0,
                    'sensor12' => 0,
                    'sensor13' => 0,
                    'sensor14' => 0,
                    'sensor15' => 0,
                    'sensor16' => 0,
                ];

                $historyData[] = $rowData;
                if ($min === 0) {
                    $latestRow = $rowData;
                }
            }

            DB::table('t_s16_01')->insert($historyData);

            DB::table('temp_s16_latest')->upsert(
                [[
                    'id_logger' => $idLogger,
                    'waktu' => $latestRow['waktu'],
                    'sensor1' => $latestRow['sensor1'] ?? 0,
                    'sensor2' => $latestRow['sensor2'] ?? 0,
                    'sensor3' => $latestRow['sensor3'] ?? 0,
                    'sensor4' => $latestRow['sensor4'] ?? 0,
                    'sensor5' => $latestRow['sensor5'] ?? 0,
                    'sensor6' => $latestRow['sensor6'] ?? 0,
                    'updated_at' => $now,
                ]],
                ['id_logger'],
                ['waktu', 'sensor1', 'sensor2', 'sensor3', 'sensor4', 'sensor5', 'sensor6', 'updated_at']
            );

            $statusIcon = $jenis === 'AWLR' ? 'AWLR' : 'AWGC';
            $this->command->line(sprintf('  %s [%d] %-12s sensor1=%-5s %s', $statusIcon, $idLogger, $nodeId, $node['sensor1'] ?? '-', $node['note']));
        }

        $this->command->info('');
        $this->command->info('Selesai! ' . count($this->nodes) . ' node dummy berhasil diinsert.');
    }
}
