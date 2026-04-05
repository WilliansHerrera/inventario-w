<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GlobalSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\GlobalSetting::updateOrCreate(
            ['id' => 1],
            [
                'country_name' => 'El Salvador',
                'locale' => 'es',
                'currency_code' => 'USD',
                'currency_symbol' => '$',
                'theme_palette' => \MoonShine\ColorManager\Palettes\PurplePalette::class,
            ]
        );
    }
}
