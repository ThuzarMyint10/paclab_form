@extends('layouts.admin')
@section('title', 'Customer Logins')
@section('content')
<h3 class="text-navy mb-1">Customer Logins</h3>
<p class="small text-muted">Accounts are created automatically when a new email submits an enquiry. Customers only ever see their own jobs.</p>
<div class="card mb-3"><div class="card-body py-2">
    <form class="row g-2"><div class="col-md-6"><input name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Name, email or company"></div><div class="col-auto"><button class="btn btn-sm btn-navy">Search</button></div></form>
</div></div>
<div class="card">
    <div class="table-responsive">
        <table class="table table-sm table-hover align-middle mb-0">
            <thead><tr><th>Name</th><th>Email</th><th>Company</th><th>Account</th><th>Jobs</th><th>Last login</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse ($customers as $c)
                <tr>
                    <td>{{ $c->name }}</td>
                    <td>{{ $c->email }}</td>
                    <td class="small">{{ $c->company_name }}</td>
                    <td class="small">{{ $c->company?->name ?? '—' }}</td>
                    <td>{{ $c->enquiries_count }}</td>
                    <td class="small">{{ $c->last_login_at ? $c->last_login_at->diffForHumans() : 'never' }}</td>
                    <td>{!! $c->is_active ? '<span class="badge text-bg-success">Active</span>' : '<span class="badge text-bg-secondary">Disabled</span>' !!}</td>
                    <td class="text-end"><a href="{{ route('admin.customers.edit', $c) }}" class="btn btn-sm btn-outline-primary">Open</a></td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted py-4">No customers yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if ($customers->hasPages())<div class="card-body">{{ $customers->links() }}</div>@endif
</div>
@endsection
