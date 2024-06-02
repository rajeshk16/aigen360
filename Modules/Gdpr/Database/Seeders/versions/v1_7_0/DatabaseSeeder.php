<?php

namespace Modules\Gdpr\Database\Seeders\versions\v1_7_0;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call(MenuItemsTableSeeder::class);
    }
}
