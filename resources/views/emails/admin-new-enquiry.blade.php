@extends('emails.layout')
@section('body')
<p>Dear Pacific Lab team,</p>
<p>A <strong>new enquiry</strong> has been submitted through the website and is waiting for a quotation.</p>
<table cellpadding="4" cellspacing="0" style="font-size:14px;width:100%;">
    <tr><td style="color:#6b7785;width:160px;">Reference</td><td><strong>{{ $enquiry->reference }}</strong>@if ($enquiry->isUrgent()) <span style="color:#d63939;font-weight:bold;">{{ strtoupper($enquiry->turnaroundLabel()) }}</span>@endif</td></tr>
    <tr><td style="color:#6b7785;">Company</td><td>{{ $enquiry->company_name }}{{ $enquiry->country ? ', '.$enquiry->country : '' }}</td></tr>
    <tr><td style="color:#6b7785;">Contact</td><td>{{ $enquiry->contact_person }} · {{ $enquiry->email }}{{ $enquiry->phone ? ' · '.$enquiry->phone : '' }}</td></tr>
    <tr><td style="color:#6b7785;">Currency</td><td>{{ $enquiry->currency }}</td></tr>
    <tr><td style="color:#6b7785;">Customer account</td><td>{{ $enquiry->company?->name ? $enquiry->company->name.' (agreed discount '.rtrim(rtrim(number_format($enquiry->company->discount_percent, 2), '0'), '.').'%)' : 'Not matched — standard price' }}</td></tr>
    <tr><td style="color:#6b7785;">Indicative total</td><td><strong>{{ $enquiry->money($enquiry->total) }}</strong> (from the standard price list)</td></tr>
</table>
@include('emails._summary', ['enquiry' => $enquiry, 'withPrice' => true])
@if ($enquiry->special_instructions)
    <p><strong>Special instructions:</strong><br>{!! nl2br(e($enquiry->special_instructions)) !!}</p>
@endif
@include('emails._button', ['url' => route('admin.enquiries.show', $enquiry), 'label' => 'Log in to review & send quotation', 'colour' => '#17365d'])
@endsection
