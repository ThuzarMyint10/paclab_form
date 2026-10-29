@extends('layouts.public')
@section('title', 'Register')
@section('content')
<div class="row justify-content-center">
    <div class="col-md-7 col-lg-6">
        <div class="card">
            <div class="card-body p-4 p-lg-5">
                <div class="section-title">Customer portal</div>
                <h3 class="text-navy mb-4">Create your account</h3>
                <form method="POST" action="{{ route('register') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label required">Your name</label><input name="name" class="form-control" value="{{ old('name') }}" required></div>
                        <div class="col-md-6"><label class="form-label required">Company</label><input name="company_name" class="form-control" value="{{ old('company_name') }}" required></div>
                        <div class="col-md-6"><label class="form-label required">Email</label><input type="email" name="email" class="form-control" value="{{ old('email') }}" required></div>
                        <div class="col-md-6"><label class="form-label">Phone</label><input name="phone" class="form-control" value="{{ old('phone') }}"></div>
                        <div class="col-md-6"><label class="form-label required">Password</label><input type="password" name="password" class="form-control" required minlength="8"></div>
                        <div class="col-md-6"><label class="form-label required">Confirm password</label><input type="password" name="password_confirmation" class="form-control" required></div>
                    </div>
                    <button class="btn btn-green w-100 mt-4">Create account</button>
                </form>
                <p class="small text-muted mt-3 mb-0">Already have an account? <a href="{{ route('login') }}">Log in</a></p>
            </div>
        </div>
    </div>
</div>
@endsection
