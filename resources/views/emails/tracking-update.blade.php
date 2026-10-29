@extends('emails.layout')
@section('body')
<p>Dear {{ $enquiry->contact_person }},</p>
<p>There is an update on your job <strong>{{ $enquiry->reference }}</strong>{{ $enquiry->ssf_number ? ' (Sample Submission Form '.$enquiry->ssf_number.')' : '' }}:</p>
<div style="background:#f4f6f9;border-left:4px solid #1f75bc;padding:14px 18px;margin:14px 0;">
    <div style="font-size:18px;font-weight:700;color:#17365d;">{{ $update->label }}</div>
    <div style="font-size:12px;color:#6b7785;">{{ $update->created_at->format('d M Y, h:i A') }}</div>
    @if ($update->status?->customer_message)<div style="margin-top:6px;">{{ $update->status->customer_message }}</div>@endif
    @if ($update->remarks)<div style="margin-top:6px;font-size:14px;">{!! nl2br(e($update->remarks)) !!}</div>@endif
</div>
@if ($enquiry->status === 'declined')
    <p>No further action will be taken on this enquiry. We hope to be of service to you in the future.</p>
@else
    @include('emails._button', ['url' => $enquiry->publicUrl(), 'label' => 'Track my job'])
@endif
<p>Kind regards,<br><strong>Pacific Lab Services</strong></p>
@endsection
