<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Turnaround (Standard / Urgent) is chosen per SAMPLE. Every test in an urgent sample is urgent.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('enquiry_samples', 'urgent')) {
            Schema::table('enquiry_samples', function (Blueprint $table) {
                $table->boolean('urgent')->default(false)->after('storage');
            });
        }

        // A sample is urgent if any of its tests was urgent, or the whole enquiry was urgent
        DB::table('enquiry_samples')
            ->whereIn('id', DB::table('enquiry_items')->where('urgent', true)->whereNotNull('enquiry_sample_id')->select('enquiry_sample_id'))
            ->orWhereIn('enquiry_id', DB::table('enquiries')->where('turnaround', 'urgent')->select('id'))
            ->update(['urgent' => true]);

        // Make every test follow its sample
        DB::table('enquiry_items')->update(['urgent' => false]);
        DB::table('enquiry_items')
            ->whereIn('enquiry_sample_id', DB::table('enquiry_samples')->where('urgent', true)->select('id'))
            ->update(['urgent' => true]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('enquiry_samples', 'urgent')) {
            Schema::table('enquiry_samples', function (Blueprint $table) {
                $table->dropColumn('urgent');
            });
        }
    }
};
