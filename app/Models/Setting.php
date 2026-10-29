<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    protected static ?array $loaded = null;

    /** Default values — any of these can be changed in Admin > Settings. */
    public static function defaults(): array
    {
        return [
            'company_name' => 'PacLab Pte Ltd',
            'trading_name' => 'Pacific Lab Services',
            'tagline' => 'Animal and Human Nutritional Analysis',
            'address' => "Level 5, Innovation Hub\n5 Woodlands Terrace\nSingapore 738430",
            'phone' => '+65 6753 3141',
            'fax' => '+65 6759 2066',
            'email' => 'paclab@pacificlab.com.sg',
            'website' => 'www.pacificlab.com.sg',
            'company_reg_no' => '202111345R',
            'gst_reg_no' => '202111345R',
            'gst_percent' => '9',
            'admin_emails' => implode(',', config('paclab.admin_emails', [])),
            'bank_details' => "Payment has to be made to Paclab Pte Ltd\nBank account no. 047-158092-001 (for SGD) / 260-373436-178 (for USD)\nBank name: The Hongkong and Shanghai Banking Corporation Limited\nSwift code: HSBCSGSG\nPaynow UEN: 202111345R\nPlease quote our invoice number when making payment.",
            'default_payment_terms' => '30 DAYS',
            'quotation_valid_days' => '90',
            'urgent_surcharge_percent' => '50',
            'quotation_notes' => "Results in 7–9 working days from the day after sample receipt.\n100g or 100mL sample required per test.",
            'sample_dispatch_instructions' => "Please print the attached Sample Submission Form, sign it and enclose it with your samples.\nLabel every sample exactly as described on the form.\nSend samples to: Pacific Lab Services, Level 5, Innovation Hub, 5 Woodlands Terrace, Singapore 738430.",
            'next_enquiry_number' => '1',
            'next_quotation_number' => '312',
            'quotation_number_format' => 'Q{n}/PLS/{Y}',
            'next_invoice_number' => '262223',
            'invoice_number_prefix' => 'PLB',
            'lab_code_prefix' => 'AJ',
            'next_lab_code' => '12926',
            'coa_signatory_name' => 'Angie Tan',
            'coa_signatory_title' => 'Chemist',
            'coa_disclaimer' => "This report shall not be reproduced, except in full, without the written approval of the Laboratory.\nThe analysis is based solely on the sample(s) submitted by the client. This report must not be used for advertising purposes.\nThe user of this waives the right to any legal action/compensation against Pacific Lab Services for any loss (financial or otherwise) sustained resulting from a reliance on the information given. The result reported herein has been performed in accordance with the laboratory's term of accreditation under the Singapore Accreditation Council - Singapore Laboratory Accredited Scheme.",
        ];
    }

    public static function get(string $key, $default = null)
    {
        if (static::$loaded === null) {
            static::$loaded = Schema::hasTable('settings')
                ? static::query()->pluck('value', 'key')->all()
                : [];
        }

        return static::$loaded[$key] ?? static::defaults()[$key] ?? $default;
    }

    public static function put(string $key, $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        if (static::$loaded !== null) {
            static::$loaded[$key] = $value;
        }
    }

    public static function flushCache(): void
    {
        static::$loaded = null;
    }

    /** Staff mailboxes for new-enquiry notifications */
    public static function adminEmails(): array
    {
        $list = array_filter(array_map('trim', preg_split('/[,;\s]+/', (string) static::get('admin_emails'))));

        return array_values(array_filter($list, fn ($e) => filter_var($e, FILTER_VALIDATE_EMAIL)));
    }
}
