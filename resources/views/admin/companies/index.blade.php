@extends('layouts.admin')
@section('title', 'Customer Companies')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h3 class="text-navy mb-0">Customer Companies</h3>
        <div class="small text-muted">Agreed currency, discount and payment terms — applied automatically when an enquiry is linked to the company.</div>
    </div>
    <a href="{{ route('admin.companies.create') }}" class="btn btn-sm btn-green">+ Add company</a>
</div>
<div class="card mb-3"><div class="card-body py-2">
    <form class="row g-2"><div class="col-md-6"><input name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Search company or country"></div><div class="col-auto"><button class="btn btn-sm btn-navy">Search</button></div></form>
</div></div>
<div class="card">
    <div class="table-responsive">
        <table class="table table-sm table-hover align-middle mb-0">
            <thead><tr><th>Company</th><th>Country</th><th>Currency</th><th class="text-end">Discount</th><th>Payment terms</th><th>Notes</th><th>Jobs</th><th></th></tr></thead>
            <tbody>
            @forelse ($companies as $c)
                <tr class="{{ $c->is_active ? '' : 'text-muted' }}">
                    <td>{{ $c->name }}</td>
                    <td>{{ $c->country }}</td>
                    <td>{{ $c->currency }}</td>
                    <td class="text-end">{{ $c->discount_percent > 0 ? rtrim(rtrim(number_format($c->discount_percent, 2), '0'), '.').'%' : '—' }}</td>
                    <td class="small">{{ $c->payment_terms }}</td>
                    <td class="small">{{ $c->notes }}</td>
                    <td>{{ $c->enquiries_count }}</td>
                    <td class="text-end"><a href="{{ route('admin.companies.edit', $c) }}" class="btn btn-sm btn-outline-primary">Edit</a></td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted py-4">No companies found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if ($companies->hasPages())<div class="card-body">{{ $companies->links() }}</div>@endif
</div>
@endsection
