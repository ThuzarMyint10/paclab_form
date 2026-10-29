@extends('layouts.public')
@section('title', 'My Account')
@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">My account</div>
            <div class="card-body">
                <form method="POST" action="{{ route('portal.profile.update') }}">
                    @csrf @method('PUT')
                    <div class="mb-3"><label class="form-label">Email (login)</label><input class="form-control" value="{{ $user->email }}" disabled></div>
                    <div class="mb-3"><label class="form-label required">Name</label><input name="name" class="form-control" value="{{ old('name', $user->name) }}" required></div>
                    <div class="mb-3"><label class="form-label">Company</label><input name="company_name" class="form-control" value="{{ old('company_name', $user->company_name) }}"></div>
                    <div class="mb-3"><label class="form-label">Phone</label><input name="phone" class="form-control" value="{{ old('phone', $user->phone) }}"></div>
                    <hr>
                    <div class="small text-muted mb-2">Change password (leave blank to keep the current one)</div>
                    <div class="mb-3"><label class="form-label">Current password</label><input type="password" name="current_password" class="form-control"></div>
                    <div class="row g-2 mb-3">
                        <div class="col"><label class="form-label">New password</label><input type="password" name="password" class="form-control"></div>
                        <div class="col"><label class="form-label">Confirm</label><input type="password" name="password_confirmation" class="form-control"></div>
                    </div>
                    <button class="btn btn-green">Save</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
