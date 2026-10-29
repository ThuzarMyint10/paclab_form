@extends('layouts.public')
@section('title', 'Welcome')
@section('content')
<div class="hero p-4 p-lg-5 mb-4">
    <div class="row align-items-center">
        <div class="col-lg-8">
            <div class="text-uppercase small fw-bold" style="color:#9ed47f">Since 1971 · SAC-SINGLAS accredited</div>
            <h1 class="display-6 fw-bold mt-2">Animal, Crop &amp; Human Nutrition Analysis</h1>
            <p class="lead mb-4">Request a quotation, approve it online, send your samples and follow every step until your Certificate of Analysis is ready.</p>
            <a href="{{ route('enquiry.create') }}" class="btn btn-green btn-lg me-2">Request a Quotation</a>
            <a href="{{ route('track') }}" class="btn btn-outline-light btn-lg">Track my Sample</a>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-6 col-lg-3">
        <a href="{{ route('enquiry.create') }}" class="card action-card h-100 text-decoration-none">
            <div class="card-body">
                <div class="action-icon bg-success-subtle text-success mb-3">₱</div>
                <h5 class="text-navy">New Price Request</h5>
                <p class="text-muted small mb-0">Tell us about your samples and choose the tests you need — we will send you a quotation.</p>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-lg-3">
        <a href="{{ route('login') }}" class="card action-card h-100 text-decoration-none">
            <div class="card-body">
                <div class="action-icon bg-primary-subtle text-primary mb-3">Q</div>
                <h5 class="text-navy">Existing Quotation</h5>
                <p class="text-muted small mb-0">Log in to review, approve or decline your quotation, or use the link in your email.</p>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-lg-3">
        <a href="{{ route('login') }}" class="card action-card h-100 text-decoration-none">
            <div class="card-body">
                <div class="action-icon bg-warning-subtle text-warning mb-3">S</div>
                <h5 class="text-navy">Sample Submission</h5>
                <p class="text-muted small mb-0">Your Sample Submission Form is generated automatically once you approve a quotation.</p>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-lg-3">
        <a href="{{ route('track') }}" class="card action-card h-100 text-decoration-none">
            <div class="card-body">
                <div class="action-icon bg-info-subtle text-info mb-3">→</div>
                <h5 class="text-navy">Track Sample</h5>
                <p class="text-muted small mb-0">Use any PacLab reference (enquiry, quotation, submission or invoice) with your email.</p>
            </div>
        </a>
    </div>
</div>

<div class="card mt-4">
    <div class="card-body">
        <div class="section-title mb-3">How it works</div>
        <div class="row g-3 small">
            @foreach ([
                ['1', 'Submit enquiry', 'You receive an email confirmation straight away.'],
                ['2', 'Receive quotation', 'Our team prepares your quotation and emails you a link to review it.'],
                ['3', 'Approve online', 'Approve (with your PO) and your Sample Submission Form is generated automatically.'],
                ['4', 'Send samples', 'Courier or post your samples with the signed form.'],
                ['5', 'Track & receive results', 'Follow each step online; get your COA and invoice by email.'],
            ] as [$n, $t, $d])
                <div class="col">
                    <div class="fw-bold text-navy"><span class="badge bg-navy me-1">{{ $n }}</span>{{ $t }}</div>
                    <div class="text-muted mt-1">{{ $d }}</div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
