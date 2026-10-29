@extends('emails.layout')
@section('body')
<p>Dear {{ $enquiry->contact_person }},</p>
<p>Thank you for approving quotation <strong>{{ $enquiry->quotation_number }}</strong>. Your <strong>Sample Submission Form {{ $enquiry->ssf_number }}</strong> has been generated and is attached to this email.</p>
<p><strong>Next steps:</strong></p>
<div style="background:#f4f6f9;border-left:4px solid #5bb031;padding:12px 16px;font-size:14px;">{!! nl2br(e(\App\Models\Setting::get('sample_dispatch_instructions'))) !!}</div>
<p>Please quote <strong>{{ $enquiry->ssf_number }}</strong> on your parcel. We will email you when your samples arrive and at each stage of testing.</p>
@include('emails._button', ['url' => $enquiry->publicUrl(), 'label' => 'View job & download form'])
<p>Kind regards,<br><strong>Pacific Lab Services</strong></p>
@endsection
