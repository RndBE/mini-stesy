<?php

namespace Tests\Unit;

use App\Support\DisplayFormat;
use PHPUnit\Framework\TestCase;

class DisplayFormatTest extends TestCase
{
    public function test_format_returns_raw_string_when_decimals_null(): void
    {
        $this->assertSame('12.34', DisplayFormat::format('12.34', null));
        $this->assertSame('62', DisplayFormat::format(62, null));
    }

    public function test_format_returns_dash_untouched_for_non_numeric(): void
    {
        $this->assertSame('-', DisplayFormat::format('-', 1));
        $this->assertSame('-', DisplayFormat::format('-', null));
    }

    public function test_format_applies_number_format_with_decimals(): void
    {
        $this->assertSame('12.0', DisplayFormat::format(12, 1));
        $this->assertSame('12.3', DisplayFormat::format(12.34, 1));
        $this->assertSame('1,234.6', DisplayFormat::format(1234.56, 1));
        $this->assertSame('12.34', DisplayFormat::format(12.34, 2));
    }
}
