@extends('layouts.admin')
@section('title', 'Staff Logins')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="text-navy mb-0">Staff Logins</h3>
    <a href="{{ route('admin.staff.create') }}" class="btn btn-sm btn-green">+ Add staff</a>
</div>
<p class="small text-muted"><strong>Administrator</strong>: everything, including settings, tracking statuses and staff logins. <strong>Lab Staff</strong>: enquiries, pricing, tracking, COA, invoices and master data.</p>
<div class="card">
    <table class="table align-middle mb-0">
        <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Last login</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @foreach ($staff as $m)
            <tr>
                <td>{{ $m->name }}</td>
                <td>{{ $m->email }}</td>
                <td>{{ \App\Models\User::ROLES[$m->role] }}</td>
                <td class="small">{{ $m->last_login_at ? $m->last_login_at->diffForHumans() : 'never' }}</td>
                <td>{!! $m->is_active ? '<span class="badge text-bg-success">Active</span>' : '<span class="badge text-bg-secondary">Disabled</span>' !!}</td>
                <td class="text-end"><a href="{{ route('admin.staff.edit', $m) }}" class="btn btn-sm btn-outline-primary">Edit</a></td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
