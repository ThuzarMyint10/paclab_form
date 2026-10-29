<?php

namespace App\Http\Controllers;

use App\Models\Enquiry;
use App\Models\Invoice;
use Illuminate\Http\Request;

/**
 * Public tracking: any PacLab reference (enquiry, quotation, sample submission form, invoice)
 * together with the email address used on the enquiry.
 */
class TrackController extends Controller
{
    public function form()
    {
        return view('public.track');
    }

    public function lookup(Request $request)
    {
        $data = $request->validate([
            'reference' => 'required|string|max:100',
            'email' => 'required|email',
        ]);

        $ref = trim($data['reference']);
        $email = strtolower(trim($data['email']));

        $enquiry = Enquiry::where(function ($q) use ($ref) {
            $q->where('reference', $ref)->orWhere('quotation_number', $ref)->orWhere('ssf_number', $ref);
        })->first();

        if (! $enquiry) {
            $invoice = Invoice::where('invoice_number', $ref)->whereIn('status', ['issued', 'paid'])->first();
            $enquiry = $invoice?->enquiry;
        }

        if (! $enquiry || strtolower($enquiry->email) !== $email) {
            return back()->withInput()->withErrors(['reference' => 'We could not find a job with that reference and email address.']);
        }

        $enquiry->load(['samples', 'trackingUpdates' => fn ($q) => $q->where('visible_to_customer', true)]);

        return view('public.track', ['enquiry' => $enquiry]);
    }
}
