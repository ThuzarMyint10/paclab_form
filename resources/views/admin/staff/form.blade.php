@extends('layouts.admin')
@section('title', $member->exists ? 'Edit staff' : 'Add staff')
@section('content')
<a href="{{ route('admin.staff.index') }}" class="small">← Staff logins</a>
<h3 class="text-navy mt-1 mb-3">{{ $member->exists ? $member->name : 'Add staff login' }}</h3>
<div class="card" style="max-width:600px">
    <div class="card-body">
        <form method="POST" action="{{ $member->exists ? route('admin.staff.update', $member) : route('admin.staff.store') }}">
            @csrf
            @if ($member->exists) @method('PUT') @endif
            <div class="mb-2"><label class="form-label required">Name</label><input name="name" value="{{ old('name', $member->name) }}" class="form-control" required></div>
            <div class="mb-2"><label class="form-label required">Email (login)</label><input type="email" name="email" value="{{ old('email', $member->email) }}" class="form-control" required></div>
            <div class="mb-2"><label class="form-label required">Role</label>
                <select name="role" class="form-select">
                    <option value="staff" @selected(old('role', $member->role) === 'staff')>Lab Staff</option>
                    <option value="admin" @selected(old('role', $member->role) === 'admin')>Administrator</option>
                </select>
            </div>
            <div class="row g-2 mb-2">
                <div class="col"><label class="form-label {{ $member->exists ? '' : 'required' }}">Password</label><input type="password" name="password" class="form-control" {{ $member->exists ? '' : 'required' }} placeholder="{{ $member->exists ? 'leave blank to keep' : 'min. 8 characters' }}"></div>
                <div class="col"><label class="form-label">Confirm</label><input type="password" name="password_confirmation" class="form-control"></div>
            </div>
            <div class="form-check mb-3"><input type="hidden" name="is_active" value="0"><input class="form-check-input" type="checkbox" name="is_active" value="1" id="active" @checked(old('is_active', $member->is_active ?? true))><label class="form-check-label" for="active">Login enabled</label></div>
            <button class="btn btn-green">Save</button>
        </form>
    </div>
</div>
@endsection
