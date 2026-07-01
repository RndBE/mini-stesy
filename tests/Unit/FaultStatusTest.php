<?php

namespace Tests\Unit;

use App\Support\FaultStatus;
use PHPUnit\Framework\TestCase;

class FaultStatusTest extends TestCase
{
    public function test_bits_map_has_14_labels_and_excludes_unused_bits(): void
    {
        $bits = FaultStatus::bits();
        $this->assertCount(14, $bits);
        $this->assertSame('Insulation error', $bits[1]);
        $this->assertSame('Reverse flow warning', $bits[14]);
        $this->assertArrayNotHasKey(15, $bits);
        $this->assertArrayNotHasKey(16, $bits);
    }

    public function test_decode_returns_empty_for_zero(): void
    {
        $this->assertSame([], FaultStatus::decode(0));
    }

    public function test_decode_single_bit(): void
    {
        $this->assertSame(['Empty pipe warning'], FaultStatus::decode(1024));
        $this->assertSame(['Reverse flow warning'], FaultStatus::decode(8192));
    }

    public function test_decode_multiple_bits_ascending_by_bit(): void
    {
        // 1026 = 2 (bit 2) + 1024 (bit 11)
        $this->assertSame(
            ['Coil current error', 'Empty pipe warning'],
            FaultStatus::decode(1026)
        );
    }

    public function test_decode_ignores_unused_high_bits(): void
    {
        // bit 15 (16384) + bit 16 (32768) only
        $this->assertSame([], FaultStatus::decode(16384 + 32768));
    }

    public function test_is_fault(): void
    {
        $this->assertFalse(FaultStatus::isFault(0));
        $this->assertTrue(FaultStatus::isFault(1));
        $this->assertFalse(FaultStatus::isFault(16384)); // only unused bit set
    }

    public function test_summary(): void
    {
        $this->assertSame('Normal', FaultStatus::summary(0));
        $this->assertSame('Fault (1)', FaultStatus::summary(1024));
        $this->assertSame('Fault (2)', FaultStatus::summary(1026));
    }

    public function test_combine_bitwise_ors_all_values(): void
    {
        $this->assertSame(8194, FaultStatus::combine([8192, 2]));
        $this->assertSame(0, FaultStatus::combine([]));
        $this->assertSame(6, FaultStatus::combine([2, 4, '2', null])); // casts, null->0
    }

    public function test_is_fault_param_matches_nama_parameter(): void
    {
        $fault = (object) ['nama_parameter' => 'Fault', 'parameter_utama' => null];
        $lower = (object) ['nama_parameter' => 'fault', 'parameter_utama' => null];
        $other = (object) ['nama_parameter' => 'Debit', 'parameter_utama' => null];

        $this->assertTrue(FaultStatus::isFaultParam($fault));
        $this->assertTrue(FaultStatus::isFaultParam($lower));
        $this->assertFalse(FaultStatus::isFaultParam($other));
    }
}
