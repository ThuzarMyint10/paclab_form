<?php

namespace Database\Seeders;

use App\Models\LabTest;
use Illuminate\Database\Seeder;

/** Loads the PACLAB Pricing 2026 standard price list (database/seeders/data/tests.json) */
class LabTestSeeder extends Seeder
{
    public function run(): void
    {
        if (LabTest::exists()) {
            $this->command?->info('Price list already loaded — skipped (edit it in Admin > Price List).');

            return;
        }

        $rows = json_decode(file_get_contents(__DIR__.'/data/tests.json'), true);
        foreach ($rows as $i => $row) {
            LabTest::create($row + ['is_active' => true, 'sort_order' => $i + 1]);
        }
        $this->command?->info(count($rows).' tests loaded from the 2026 price list.');
    }
}
