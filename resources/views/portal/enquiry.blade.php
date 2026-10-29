@extends('layouts.public')
@section('title', $enquiry->reference)
@section('content')
<div class="mb-3"><a href="{{ route('portal.dashboard') }}">← My jobs</a></div>
<div class="d-flex flex-wrap justify-content-between align-items-start mb-3 gap-2">
    <div>
        <div class="section-title">Enquiry {{ $enquiry->reference }}</div>
        <h2 class="text-navy mb-1">{{ $enquiry->statusLabel() }}</h2>
        <div class="small text-muted">
            Submitted {{ $enquiry->created_at->format('d M Y') }}
            @if ($enquiry->quotation_number) · Quotation <strong>{{ $enquiry->quotation_number }}</strong> @endif
            @if ($enquiry->ssf_number) · Submission form <strong>{{ $enquiry->ssf_number }}</strong> @endif
        </div>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @if ($enquiry->quotation_number)
            <a href="{{ route('portal.enquiries.document', [$enquiry, 'quotation']) }}" class="btn btn-outline-secondary btn-sm">⬇ Quotation</a>
        @endif
        @if ($enquiry->ssf_number && $enquiry->isApprovedJob())
            <a href="{{ route('portal.enquiries.document', [$enquiry, 'ssf']) }}" class="btn btn-green btn-sm">⬇ Sample Submission Form</a>
        @endif
        @if ($enquiry->coa && $enquiry->coa->isReleased())
            <a href="{{ route('portal.enquiries.document', [$enquiry, 'coa']) }}" class="btn btn-navy btn-sm">⬇ Certificate of Analysis</a>
        @endif
        @foreach ($enquiry->invoices as $inv)
            <a href="{{ route('portal.invoices.pdf', $inv) }}" class="btn btn-outline-primary btn-sm">⬇ Invoice {{ $inv->invoice_number }}</a>
        @endforeach
    </div>
</div>

<div class="card mb-4"><div class="card-body">@include('partials.progress', ['enquiry' => $enquiry])</div></div>

@if ($enquiry->canRespond())
    <div class="alert alert-info"><strong>Your quotation is ready.</strong> Please review it below and approve or decline.</div>
@endif

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">{{ $enquiry->quotation_number ? 'Quotation '.$enquiry->quotation_number : 'Requested tests (awaiting quotation)' }}</div>
            <div class="card-body">
                @if ($enquiry->quotation_number)
                    <div class="row small mb-3">
                        <div class="col-md-4"><span class="text-muted">Date:</span> {{ optional($enquiry->quotation_date)->format('d M Y') }}</div>
                        <div class="col-md-4"><span class="text-muted">Valid until:</span> {{ optional($enquiry->quotation_valid_until)->format('d M Y') }}</div>
                        <div class="col-md-4"><span class="text-muted">Payment terms:</span> {{ $enquiry->payment_terms ?: '—' }}</div>
                    </div>
                    @include('partials.quotation-table', ['enquiry' => $enquiry])
                    @if ($enquiry->quotation_notes)
                        <div class="mt-3 small" style="white-space:pre-line"><strong>Notes:</strong> {{ $enquiry->quotation_notes }}</div>
                    @endif
                @else
                    <table class="table table-sm table-light-head mb-0">
                        <thead><tr><th>Sample</th><th>Test</th><th>Matrix / Method</th></tr></thead>
                        <tbody>
                        @foreach ($enquiry->samples as $s)
                            @foreach ($s->items as $item)
                                <tr><td>@if ($loop->first){{ $s->description.' (×'.$s->quantity.')' }}@if ($s->urgent) <span class="badge text-bg-danger">URGENT</span>@endif @endif</td><td>{{ $item->test_name }}</td><td class="small text-muted">{{ collect([$item->matrix, $item->method])->filter()->implode(' – ') }}</td></tr>
                            @endforeach
                        @endforeach
                        </tbody>
                    </table>
                    <p class="small text-muted mt-3 mb-0">Our team is preparing your quotation. You will receive an email as soon as it is ready.</p>
                @endif
            </div>
        </div>

        @if ($enquiry->canRespond())
            @include('partials.response-forms', [
                'enquiry' => $enquiry,
                'approveUrl' => route('portal.enquiries.approve', $enquiry),
                'declineUrl' => route('portal.enquiries.decline', $enquiry),
            ])
        @endif

        @if ($enquiry->ssf_number && $enquiry->status === 'approved')
            <div class="card border border-success mt-4">
                <div class="card-body">
                    <h5 class="text-success">Next step: send your samples</h5>
                    <div class="small" style="white-space:pre-line">{{ \App\Models\Setting::get('sample_dispatch_instructions') }}</div>
                    <a href="{{ route('portal.enquiries.document', [$enquiry, 'ssf']) }}" class="btn btn-green btn-sm mt-3">⬇ Download Sample Submission Form {{ $enquiry->ssf_number }}</a>
                </div>
            </div>
        @endif
    </div>
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">Tracking history</div>
            <div class="card-body">@include('partials.timeline', ['updates' => $enquiry->trackingUpdates])</div>
        </div>
        <div class="card">
            <div class="card-header">Samples</div>
            <ul class="list-group list-group-flush small">
                @foreach ($enquiry->samples as $s)
                    <li class="list-group-item">
                        <div class="fw-semibold">{{ $s->description }}</div>
                        <div class="text-muted">
                            {{ $s->quantity }} sample(s){{ $s->batch_no ? ' · Batch '.$s->batch_no : '' }}{{ $s->lab_code ? ' · Lab code '.$s->lab_code : '' }}
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
@endsection
