@extends('emails.layout')
@section('body')
<p>Dear {{ $enquiry->contact_person }},</p>
<p>Thank you for your enquiry. This email confirms that <strong>Pacific Lab Services</strong> has received your request for a quotation.</p>
<table cellpadding="4" cellspacing="0" style="font-size:14px;background:#f4f6f9;border-radius:6px;padding:10px;width:100%;">
    <tr><td style="color:#6b7785;width:160px;">Reference no.</td><td><strong style="color:#17365d;font-size:16px;">{{ $enquiry->reference }}</strong></td></tr>
    <tr><td style="color:#6b7785;">Company</td><td>{{ $enquiry->company_name }}</td></tr>
    <tr><td style="color:#6b7785;">Submitted</td><td>{{ $enquiry->created_at->format('d M Y, h:i A') }}</td></tr>
    <tr><td style="color:#6b7785;">Turnaround</td><td>{{ $enquiry->turnaroundLabel() }}</td></tr>
</table>
@include('emails._summary', ['enquiry' => $enquiry])
<p><strong>What happens next?</strong><br>
Our team will review your request and email you a formal quotation, normally within one working day. You can then approve or decline it online with one click.</p>
@if ($setupToken)
    <p>We have also created a <strong>customer portal account</strong> for you, where you can approve quotations, download your Sample Submission Form, reports and invoices, and track your samples. Please set your password here (link valid for 72 hours):</p>
    @include('emails._button', ['url' => route('password.reset', ['token' => $setupToken, 'email' => $enquiry->email]), 'label' => 'Set my portal password'])
@else
    @include('emails._button', ['url' => route('portal.dashboard'), 'label' => 'View in customer portal'])
@endif
<p>You can also track this enquiry at any time at <a href="{{ route('track') }}">{{ route('track') }}</a> using your reference number and email address.</p>
<p>Kind regards,<br><strong>Pacific Lab Services</strong></p>
@endsection
