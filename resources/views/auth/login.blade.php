@extends('layouts.public')
@section('title', $area === 'admin' ? 'Staff Login' : 'Customer Login')
@section('content')
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card">
            <div class="card-body p-4 p-lg-5">
                @if ($area === 'admin')
                    <div class="section-title">Pacific Lab staff</div>
                    <h3 class="text-navy mb-4">Administration login</h3>
                @else
                    <div class="section-title">Customer portal</div>
                    <h3 class="text-navy mb-1">Log in to your account</h3>
                    <p class="text-muted small mb-4">View your quotations, approve them, download your Sample Submission Forms, reports and invoices, and track your samples.</p>
                @endif
                <form method="POST" action="{{ $area === 'admin' ? route('admin.login') : route('login') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1">
                            <label class="form-check-label small" for="remember">Remember me</label>
                        </div>
                        <a href="{{ route('password.request') }}" class="small">Forgot / set password?</a>
                    </div>
                    <button class="btn {{ $area === 'admin' ? 'btn-navy' : 'btn-green' }} w-100">Log in</button>
                </form>
                @if ($area !== 'admin')
                    <hr>
                    <p class="small text-muted mb-0">
                        New customer? Simply <a href="{{ route('enquiry.create') }}">submit an enquiry</a> — your account is created automatically
                        and we email you a link to set your password. Or <a href="{{ route('register') }}">register now</a>.
                    </p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
