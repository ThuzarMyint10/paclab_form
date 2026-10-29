<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

/** Loads "PAC LAB EXTERNAL PAYMENT TERMS - DISCOUNTS" (database/seeders/data/companies.json) */
class CompanySeeder extends Seeder
{
    public function run(): void
    {
        if (Company::exists()) {
            $this->command?->info('Customer companies already loaded — skipped.');

            return;
        }

        $rows = json_decode(file_get_contents(__DIR__.'/data/companies.json'), true);
        foreach ($rows as $row) {
            Company::create($row + ['is_active' => true]);
        }
        $this->command?->info(count($rows).' customer companies loaded with their discount and payment terms.');
    }
}
