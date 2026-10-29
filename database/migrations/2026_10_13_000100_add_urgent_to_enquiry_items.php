<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Turnaround is now chosen per test (Standard / Urgent) instead of per enquiry.
 * Existing urgent enquiries keep all their tests urgent.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('enquiry_items', 'urgent')) {
            Schema::table('enquiry_items', function (Blueprint $table) {
                $table->boolean('urgent')->default(false)->after('quantity');
            });
        }

        DB::table('enquiry_items')
            ->whereIn('enquiry_id', DB::table('enquiries')->where('turnaround', 'urgent')->select('id'))
            ->update(['urgent' => true]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('enquiry_items', 'urgent')) {
            Schema::table('enquiry_items', function (Blueprint $table) {
                $table->dropColumn('urgent');
            });
        }
    }
};
