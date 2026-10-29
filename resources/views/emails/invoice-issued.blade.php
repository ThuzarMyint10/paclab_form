@extends('emails.layout')
@section('body')
@php $e = $invoice->enquiry; @endphp
<p>Dear {{ $e->contact_person }},</p>
<p>Please find attached our Tax Invoice <strong>{{ $invoice->invoice_number }}</strong> for job {{ $e->reference }}.</p>
<table cellpadding="4" cellspacing="0" style="font-size:14px;background:#f4f6f9;border-radius:6px;width:100%;">
    <tr><td style="color:#6b7785;width:160px;">Invoice no.</td><td><strong>{{ $invoice->invoice_number }}</strong></td></tr>
    <tr><td style="color:#6b7785;">Invoice date</td><td>{{ $invoice->invoice_date->format('d M Y') }}</td></tr>
    <tr><td style="color:#6b7785;">Your ref</td><td>{{ $invoice->po_number ? 'PO '.$invoice->po_number : '—' }}</td></tr>
    <tr><td style="color:#6b7785;">Payment terms</td><td>{{ $invoice->payment_terms }}</td></tr>
    <tr><td style="color:#6b7785;">Grand total</td><td><strong style="font-size:17px;color:#17365d;">{{ $invoice->currency }} {{ number_format($invoice->grand_total, 2) }}</strong></td></tr>
</table>
<p style="font-size:13px;">{!! nl2br(e(\App\Models\Setting::get('bank_details'))) !!}</p>
@include('emails._button', ['url' => route('portal.dashboard'), 'label' => 'View in customer portal'])
<p>Kind regards,<br><strong>Pacific Lab Services</strong></p>
@endsection
