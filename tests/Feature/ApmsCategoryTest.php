<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApmsCategoryTest extends TestCase
{
    public function test_apms_contract_is_registered_without_pump_ui(): void
    {
        $migration = file_get_contents(database_path('migrations/2026_07_16_000000_add_apms_category_and_parameters.php'));
        $seeder = file_get_contents(database_path('seeders/ListParameterSeeder.php'));
        $index = file_get_contents(resource_path('views/beranda/index.blade.php'));
        $awlr = file_get_contents(resource_path('views/beranda/categories/awlr.blade.php'));
        $arr = file_get_contents(resource_path('views/beranda/categories/arr.blade.php'));
        $view = file_get_contents(resource_path('views/beranda/categories/apms.blade.php'));
        $sharedWellPath = resource_path('views/beranda/categories/partials/monitoring_well.blade.php');
        $sharedRainPath = resource_path('views/beranda/categories/partials/rainfall_cards.blade.php');
        $categoryIcon = public_path('kategori/apms.svg');
        $apmsMeasurementIcons = [
            'icons/apms/ph_tanah.svg',
            'icons/apms/electrical_conductivity.svg',
            'icons/apms/soil_moisture.svg',
            'icons/apms/soil_temperature.svg',
            'icons/apms/soil_salinity.svg',
        ];

        $this->assertStringContainsString("'APMS'", $migration);
        $this->assertFileExists($categoryIcon);

        $mappings = [
            'muka_air_tanah' => ['order' => 1, 'sensor' => 'sensor1'],
            'ph_tanah' => ['order' => 2, 'sensor' => 'sensor2'],
            'electrical_conductivity' => ['order' => 3, 'sensor' => 'sensor3'],
            'kelembaban_tanah' => ['order' => 4, 'sensor' => 'sensor4'],
            'temperature_tanah' => ['order' => 5, 'sensor' => 'sensor5'],
            'salinity' => ['order' => 6, 'sensor' => 'sensor7'],
            'hujan' => ['order' => 7, 'sensor' => 'sensor6'],
            'humidity_logger' => ['order' => 8, 'sensor' => 'sensor14'],
            'battery_logger' => ['order' => 9, 'sensor' => 'sensor15'],
            'temperature_logger' => ['order' => 10, 'sensor' => 'sensor16'],
        ];

        foreach ($mappings as $base => $mapping) {
            $this->assertStringContainsString(
                "'base' => '{$base}', 'order' => {$mapping['order']}, 'column' => '{$mapping['sensor']}'",
                $seeder
            );
        }

        $this->assertStringContainsString("'APMS' => 'beranda.categories.apms'", $index);
        $this->assertFileExists($sharedWellPath);
        $sharedWell = file_get_contents($sharedWellPath);
        $this->assertStringContainsString("@include('beranda.categories.partials.monitoring_well')", $awlr);
        $this->assertStringContainsString(
            "@include('beranda.categories.partials.monitoring_well', [",
            $view
        );
        $this->assertStringContainsString("'showWellHardware' => false", $view);
        $this->assertFileExists($sharedRainPath);
        $this->assertStringContainsString(
            "@include('beranda.categories.partials.rainfall_cards')",
            $arr
        );
        $this->assertStringContainsString(
            "@include('beranda.categories.partials.rainfall_cards', [",
            $view
        );
        $this->assertStringContainsString("'desktopCardClass' => 'lg:h-[202px] lg:min-h-0 lg:py-2'", $view);
        $this->assertStringContainsString("'desktopIconClass' => 'lg:h-24 lg:w-32'", $view);
        $sharedRain = file_get_contents($sharedRainPath);
        $this->assertStringContainsString("{{ \$desktopCardClass ?? '' }}", $sharedRain);
        $this->assertStringContainsString("{{ \$desktopIconClass ?? '' }}", $sharedRain);
        $this->assertStringContainsString("asset('sumur/badan_sumur.svg')", $sharedWell);
        $this->assertStringContainsString('Data Pengukuran', $view);
        $this->assertStringContainsString('Data Logger', $view);
        $this->assertStringContainsString('Curah Hujan', $view);
        $this->assertStringContainsString('Electrical Conductivity', $view);
        $this->assertStringContainsString("'label' => 'Salinity'", $view);
        foreach ($apmsMeasurementIcons as $icon) {
            $this->assertFileExists(public_path($icon));
            $this->assertStringContainsString("'icon' => '{$icon}'", $view);
        }
        $this->assertStringNotContainsString('klasifikasi_hujan/', $view);
        $this->assertStringNotContainsString('apms-water-', $view);
        $this->assertStringNotContainsString('pompa', strtolower($view));
        $this->assertStringNotContainsString('pump', strtolower($view));
    }
}
