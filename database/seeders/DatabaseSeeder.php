<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Demo products and collections are for local/staging only. Running
        // `db:seed` against production must never insert sample catalogue data.
        if (app()->isProduction()) {
            $this->command?->warn('Skipping demo catalogue seeders in production.');

            return;
        }

        $this->call([
            WearDemoSeeder::class,
            WearCollectionSeeder::class,
        ]);
    }
}
