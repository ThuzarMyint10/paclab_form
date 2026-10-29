@extends('emails.layout')
@section('body')
<p>Dear Pacific Lab team,</p>
@if ($approved)
    <p style="font-size:17px;color:#468a24;"><strong>✔ Quotation {{ $enquiry->quotation_number }} has been APPROVED.</strong></p>
    <table cellpadding="4" cellspacing="0" style="font-size:14px;width:100%;">
        <tr><td style="color:#6b7785;width:170px;">Customer</td><td>{{ $enquiry->company_name }} ({{ $enquiry->contact_person }})</td></tr>
        <tr><td style="color:#6b7785;">Approved by</td><td>{{ $enquiry->approved_by_name }}</td></tr>
        <tr><td style="color:#6b7785;">PO number</td><td>{{ $enquiry->po_number ?: '—' }}{{ $enquiry->po_file ? ' (PO document uploaded)' : '' }}</td></tr>
        <tr><td style="color:#6b7785;">Amount</td><td>{{ $enquiry->money($enquiry->total) }}</td></tr>
        <tr><td style="color:#6b7785;">Sample Submission Form</td><td><strong>{{ $enquiry->ssf_number }}</strong> (emailed to the customer)</td></tr>
    </table>
    <p>Please expect the samples to arrive and update the tracking status on receipt.</p>
@else
    <p style="font-size:17px;color:#d63939;"><strong>✖ Quotation {{ $enquiry->quotation_number }} has been DECLINED.</strong></p>
    <p>Customer: {{ $enquiry->company_name }} ({{ $enquiry->contact_person }})<br>
    Reason: {{ $enquiry->decline_reason ?: 'not given' }}</p>
    <p>The enquiry is now closed — no further action is required.</p>
@endif
@include('emails._button', ['url' => route('admin.enquiries.show', $enquiry), 'label' => 'Open '.$enquiry->reference, 'colour' => '#17365d'])
@endsection
