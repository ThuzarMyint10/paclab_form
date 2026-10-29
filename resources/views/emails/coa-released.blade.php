@extends('emails.layout')
@section('body')
<p>Dear {{ $enquiry->contact_person }},</p>
<p>We are pleased to inform you that testing of your samples is complete. Your <strong>Certificate of Analysis {{ $enquiry->coa->report_number }}</strong> is attached.</p>
<p>Job: {{ $enquiry->reference }} · Quotation {{ $enquiry->quotation_number }}{{ $enquiry->po_number ? ' · PO '.$enquiry->po_number : '' }}</p>
@include('emails._button', ['url' => route('portal.dashboard'), 'label' => 'View in customer portal'])
<p>Thank you for choosing Pacific Lab Services.</p>
<p>Kind regards,<br><strong>Pacific Lab Services</strong></p>
@endsection
