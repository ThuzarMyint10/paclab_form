@extends('layouts.public')
@section('title', 'Quotation '.$enquiry->quotation_number)
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div>
        <div class="section-title">Quotation</div>
        <h2 class="text-navy mb-0">{{ $enquiry->quotation_number }}</h2>
        <div class="text-muted small">For {{ $enquiry->company_name }} · Enquiry {{ $enquiry->reference }}</div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('quotation.public.document', [$enquiry->access_token, 'quotation']) }}" class="btn btn-outline-secondary">⬇ Quotation PDF</a>
        @if ($enquiry->ssf_number && $enquiry->isApprovedJob())
            <a href="{{ route('quotation.public.document', [$enquiry->access_token, 'ssf']) }}" class="btn btn-green">⬇ Sample Submission Form</a>
        @endif
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <div class="row small mb-3">
            <div class="col-md-4"><span class="text-muted">Date:</span> {{ optional($enquiry->quotation_date)->format('d M Y') }}</div>
            <div class="col-md-4"><span class="text-muted">Valid until:</span> {{ optional($enquiry->quotation_valid_until)->format('d M Y') }}</div>
            <div class="col-md-4"><span class="text-muted">Payment terms:</span> {{ $enquiry->payment_terms ?: '—' }}</div>
        </div>
        @include('partials.quotation-table', ['enquiry' => $enquiry])
        @if ($enquiry->quotation_notes)
            <div class="mt-3 small" style="white-space:pre-line"><strong>Notes:</strong> {{ $enquiry->quotation_notes }}</div>
        @endif
    </div>
</div>

@if ($enquiry->canRespond())
    @include('partials.response-forms', [
        'enquiry' => $enquiry,
        'approveUrl' => route('quotation.public.approve', $enquiry->access_token),
        'declineUrl' => route('quotation.public.decline', $enquiry->access_token),
    ])
@elseif ($enquiry->status === 'quoted')
    <div class="alert alert-warning">This quotation expired on {{ $enquiry->quotation_valid_until->format('d M Y') }}. Please contact us for an updated quotation.</div>
@elseif ($enquiry->isDeclined())
    <div class="alert alert-secondary">This quotation was declined on {{ optional($enquiry->responded_at)->format('d M Y') }}. The enquiry is closed.</div>
@else
    <div class="alert alert-success">
        <strong>Approved on {{ optional($enquiry->responded_at)->format('d M Y') }}.</strong>
        Sample Submission Form <strong>{{ $enquiry->ssf_number }}</strong> — please print, sign and send it together with your samples.
        Current status: <strong>{{ $enquiry->statusLabel() }}</strong>.
    </div>
    <div class="card"><div class="card-body">@include('partials.progress', ['enquiry' => $enquiry])</div></div>
@endif
@endsection
