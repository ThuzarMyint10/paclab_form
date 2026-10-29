<?php

namespace App\Services;

use App\Models\Enquiry;
use App\Models\Setting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Running numbers for enquiries, quotations, invoices, lab codes and sample submission forms.
 * Counters live in the settings table so staff can set the starting number.
 */
class Numbering
{
    /** Atomically take the next value of a counter and increment it */
    public static function take(string $key): int
    {
        return DB::transaction(function () use ($key) {
            $row = Setting::where('key', $key)->lockForUpdate()->first();
            if (! $row) {
                $row = Setting::create(['key' => $key, 'value' => (string) Setting::get($key, 1)]);
            }
            $current = max(1, (int) $row->value);
            $row->update(['value' => (string) ($current + 1)]);
            Setting::flushCache();

            return $current;
        });
    }

    /** ENQ-2026-00001 */
    public static function enquiry(): string
    {
        do {
            $ref = sprintf('ENQ-%s-%05d', now()->format('Y'), self::take('next_enquiry_number'));
        } while (Enquiry::where('reference', $ref)->exists());

        return $ref;
    }

    /** Q312/PLS/2026 */
    public static function quotation(): string
    {
        $format = Setting::get('quotation_number_format', 'Q{n}/PLS/{Y}');
        do {
            $ref = strtr($format, ['{n}' => self::take('next_quotation_number'), '{Y}' => now()->format('Y')]);
        } while (Enquiry::where('quotation_number', $ref)->exists());

        return $ref;
    }

    /** PLB262223 */
    public static function invoice(): string
    {
        do {
            $ref = Setting::get('invoice_number_prefix', 'PLB').self::take('next_invoice_number');
        } while (\App\Models\Invoice::where('invoice_number', $ref)->exists());

        return $ref;
    }

    /** AJ12926 — laboratory sample code written on each received sample */
    public static function labCode(): string
    {
        return Setting::get('lab_code_prefix', 'AJ').self::take('next_lab_code');
    }

    /** 2026-09-15-001 — sample submission form number (also used as the COA report number) */
    public static function ssf(?Carbon $date = null): string
    {
        $date = ($date ?? now())->format('Y-m-d');

        return DB::transaction(function () use ($date) {
            $last = Enquiry::where('ssf_number', 'like', $date.'-%')->lockForUpdate()->orderByDesc('ssf_number')->value('ssf_number');
            $seq = $last ? ((int) substr($last, -3)) + 1 : 1;

            return sprintf('%s-%03d', $date, $seq);
        });
    }
}
