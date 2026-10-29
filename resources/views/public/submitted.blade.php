@extends('layouts.public')
@section('title', 'Enquiry submitted')
@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card text-center">
            <div class="card-body p-5">
                <div class="display-4 text-success mb-3">✓</div>
                <h2 class="text-navy">Thank you — your enquiry has been received</h2>
                <p class="lead mt-3">Your reference number is</p>
                <div class="fs-2 fw-bold text-green mb-3">{{ $reference }}</div>
                <p class="text-muted">A confirmation email has been sent to <strong>{{ $email }}</strong>.
                    Our team will review your request and email your quotation shortly.</p>
                <p class="text-muted small">New customers also receive a link to set a password for the customer portal, where you can approve quotations and track your samples.</p>
                <div class="d-flex justify-content-center gap-2 mt-4">
                    <a href="{{ route('track') }}" class="btn btn-navy">Track this enquiry</a>
                    <a href="{{ route('home') }}" class="btn btn-outline-secondary">Back to home</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
