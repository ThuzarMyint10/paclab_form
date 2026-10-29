@extends('layouts.public')
@section('title', 'Track sample')
@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <h2 class="text-navy mb-1">Track your request</h2>
        <p class="text-muted">Enter any PacLab reference — enquiry (ENQ-…), quotation (Q…/PLS/…), sample submission form (2026-09-15-001) or invoice (PLB…) — and the email address used on the enquiry.</p>

        <div class="card mb-4">
            <div class="card-body">
                <form method="POST" action="{{ route('track.lookup') }}" class="row g-2 align-items-end">
                    @csrf
                    <div class="col-md-5">
                        <label class="form-label">Reference number</label>
                        <input name="reference" class="form-control" value="{{ old('reference', isset($enquiry) ? request('reference') : '') }}" required>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Email address</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', isset($enquiry) ? request('email') : '') }}" required>
                    </div>
                    <div class="col-md-2 d-grid">
                        <button class="btn btn-green">Track</button>
                    </div>
                </form>
            </div>
        </div>

        @isset($enquiry)
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                        <div>
                            <div class="section-title">Enquiry {{ $enquiry->reference }}</div>
                            <h4 class="text-navy mb-1">{{ $enquiry->statusLabel() }}</h4>
                            <div class="small text-muted">
                                Submitted {{ $enquiry->created_at->format('d M Y') }}
                                @if ($enquiry->quotation_number) · Quotation {{ $enquiry->quotation_number }} @endif
                                @if ($enquiry->ssf_number) · Submission form {{ $enquiry->ssf_number }} @endif
                            </div>
                        </div>
                        <div class="text-end small">
                            <div>{{ $enquiry->samples->count() }} sample line(s), {{ $enquiry->totalSamples() }} sample(s)</div>
                            @if ($enquiry->status === 'quoted')
                                <a href="{{ $enquiry->publicUrl() }}" class="btn btn-sm btn-green mt-2">Review quotation</a>
                            @endif
                        </div>
                    </div>
                    @include('partials.progress', ['enquiry' => $enquiry])
                    <hr>
                    <div class="section-title mb-2">History</div>
                    @include('partials.timeline', ['updates' => $enquiry->trackingUpdates])
                    <p class="small text-muted mt-3 mb-0">For quotation details and documents, log in to the <a href="{{ route('login') }}">customer portal</a>.</p>
                </div>
            </div>
        @endisset
    </div>
</div>
@endsection
