<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(\Database\Seeders\MiniStesySeeder::class);
        $this->call(\Database\Seeders\AddAwlrSinduadiTimurSeeder::class);
        $this->call(\Database\Seeders\ListParameterSeeder::class);
    }
}
