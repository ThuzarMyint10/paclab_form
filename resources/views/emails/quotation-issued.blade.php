@extends('emails.layout')
@section('body')
<p>Dear {{ $enquiry->contact_person }},</p>
<p>Thank you for your interest in Pacific Lab Services. {{ $revised ? 'Please find our revised quotation' : 'Please find our quotation' }} for your enquiry <strong>{{ $enquiry->reference }}</strong> below and attached as a PDF.</p>
<table cellpadding="4" cellspacing="0" style="font-size:14px;background:#f4f6f9;border-radius:6px;width:100%;">
    <tr><td style="color:#6b7785;width:160px;">Quotation no.</td><td><strong>{{ $enquiry->quotation_number }}</strong></td></tr>
    <tr><td style="color:#6b7785;">Amount due</td><td><strong style="color:#17365d;font-size:17px;">{{ $enquiry->money($enquiry->total) }}</strong></td></tr>
    <tr><td style="color:#6b7785;">Valid until</td><td>{{ optional($enquiry->quotation_valid_until)->format('d M Y') }}</td></tr>
    <tr><td style="color:#6b7785;">Payment terms</td><td>{{ $enquiry->payment_terms }}</td></tr>
</table>
@include('emails._summary', ['enquiry' => $enquiry, 'withPrice' => true])
<p>Please click the button below to <strong>review and approve or decline</strong> the quotation. Once approved, your Sample Submission Form will be generated automatically.</p>
@include('emails._button', ['url' => $enquiry->publicUrl(), 'label' => 'Review quotation'])
<p style="font-size:13px;color:#6b7785;">If the button does not work, copy this link into your browser:<br>{{ $enquiry->publicUrl() }}</p>
<p>Kind regards,<br><strong>Pacific Lab Services</strong></p>
@endsection
