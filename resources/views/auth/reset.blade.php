@extends('layouts.public')
@section('title', 'Choose a password')
@section('content')
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card">
            <div class="card-body p-4 p-lg-5">
                <h3 class="text-navy mb-4">Choose your password</h3>
                <form method="POST" action="{{ route('password.update') }}">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="{{ old('email', $email) }}" required></div>
                    <div class="mb-3"><label class="form-label">New password (min. 8 characters)</label><input type="password" name="password" class="form-control" required minlength="8"></div>
                    <div class="mb-3"><label class="form-label">Confirm password</label><input type="password" name="password_confirmation" class="form-control" required></div>
                    <button class="btn btn-green w-100">Save password</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
