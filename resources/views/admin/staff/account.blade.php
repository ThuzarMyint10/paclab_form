@extends('layouts.admin')
@section('title', 'My account')
@section('content')
<h3 class="text-navy mb-3">My account</h3>
<div class="card" style="max-width:560px">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.account.update') }}">
            @csrf @method('PUT')
            <div class="mb-2"><label class="form-label">Email</label><input class="form-control" value="{{ $member->email }}" disabled></div>
            <div class="mb-3"><label class="form-label">Name</label><input name="name" value="{{ old('name', $member->name) }}" class="form-control" required></div>
            <div class="small text-muted mb-2">Change password (leave blank to keep)</div>
            <div class="mb-2"><label class="form-label">Current password</label><input type="password" name="current_password" class="form-control"></div>
            <div class="row g-2 mb-3">
                <div class="col"><label class="form-label">New password</label><input type="password" name="password" class="form-control"></div>
                <div class="col"><label class="form-label">Confirm</label><input type="password" name="password_confirmation" class="form-control"></div>
            </div>
            <button class="btn btn-green">Save</button>
        </form>
    </div>
</div>
@endsection
