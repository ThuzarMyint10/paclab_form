@extends('layouts.public')
@section('title', 'Set or reset password')
@section('content')
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card">
            <div class="card-body p-4 p-lg-5">
                <h3 class="text-navy">Set or reset your password</h3>
                <p class="text-muted small">Enter the email address you used on your enquiry. We will email you a link to choose a password.</p>
                <form method="POST" action="{{ route('password.email') }}">
                    @csrf
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control mb-3" value="{{ old('email') }}" required autofocus>
                    <button class="btn btn-green w-100">Email me a link</button>
                </form>
                <p class="small mt-3 mb-0"><a href="{{ route('login') }}">← Back to login</a></p>
            </div>
        </div>
    </div>
</div>
@endsection
