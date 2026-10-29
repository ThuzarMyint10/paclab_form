<?php

namespace App\Services;

use App\Models\Enquiry;
use App\Models\Invoice;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * Builds the PDF documents from the PacLab templates:
 * Quotation, Sample Submission Form, Tax Invoice and Certificate of Analysis.
 */
class Documents
{
    public static function common(): array
    {
        $logoPath = public_path('images/logo.png');

        return [
            'logo' => is_file($logoPath) ? 'data:image/png;base64,'.base64_encode(file_get_contents($logoPath)) : null,
            'co' => [
                'name' => Setting::get('company_name'),
                'trading' => Setting::get('trading_name'),
                'tagline' => Setting::get('tagline'),
                'address' => Setting::get('address'),
                'phone' => Setting::get('phone'),
                'fax' => Setting::get('fax'),
                'email' => Setting::get('email'),
                'website' => Setting::get('website'),
                'reg_no' => Setting::get('company_reg_no'),
                'gst_no' => Setting::get('gst_reg_no'),
                'bank' => Setting::get('bank_details'),
            ],
        ];
    }

    public static function quotation(Enquiry $enquiry)
    {
        $enquiry->loadMissing('samples.items', 'items');

        return Pdf::loadView('pdf.quotation', self::common() + ['e' => $enquiry])->setPaper('a4');
    }

    public static function ssf(Enquiry $enquiry)
    {
        $enquiry->loadMissing('samples.items');

        return Pdf::loadView('pdf.ssf', self::common() + ['e' => $enquiry])->setPaper('a4');
    }

    public static function invoice(Invoice $invoice)
    {
        $invoice->loadMissing('items', 'enquiry');

        return Pdf::loadView('pdf.invoice', self::common() + ['inv' => $invoice, 'e' => $invoice->enquiry])->setPaper('a4');
    }

    public static function coa(Enquiry $enquiry)
    {
        $enquiry->loadMissing('samples.items', 'coa');

        return Pdf::loadView('pdf.coa', self::common() + ['e' => $enquiry, 'coa' => $enquiry->coa])->setPaper('a4');
    }

    public static function fileName(string $prefix, ?string $number): string
    {
        return $prefix.'_'.preg_replace('/[^A-Za-z0-9\-]+/', '-', (string) $number).'.pdf';
    }
}
