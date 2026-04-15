<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use MoonShine\Laravel\Models\MoonshineUser;
use MoonShine\Laravel\Models\MoonshineUserRole;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        MoonshineUser::query()->updateOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('admin'),
                'moonshine_user_role_id' => MoonshineUserRole::DEFAULT_ROLE_ID, // Usualmente 1 (Admin)
            ]
        );
    }
}
