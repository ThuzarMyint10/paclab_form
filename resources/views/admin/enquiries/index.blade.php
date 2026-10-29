@extends('layouts.admin')
@section('title', 'Enquiries & Jobs')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="text-navy mb-0">Enquiries &amp; Jobs</h3>
    <a href="{{ route('enquiry.create') }}" target="_blank" class="btn btn-sm btn-outline-secondary">Enter enquiry for a walk-in customer ↗</a>
</div>
<div class="card mb-3">
    <div class="card-body py-2">
        <form class="row g-2 align-items-center">
            <div class="col-md-5"><input name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Reference, quotation no., SSF no., company, contact or email"></div>
            <div class="col-md-4">
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All statuses</option>
                    <option value="active" @selected(request('status') === 'active')>— In progress (all) —</option>
                    @foreach (\App\Models\Enquiry::STATUSES as $k => $v)
                        <option value="{{ $k }}" @selected(request('status') === $k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3"><button class="btn btn-sm btn-navy">Filter</button> <a href="{{ route('admin.enquiries.index') }}" class="btn btn-sm btn-link">Reset</a></div>
        </form>
    </div>
</div>
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>Reference</th><th>Date</th><th>Company</th><th>Contact</th><th>Quotation</th><th>Tests</th><th class="text-end">Total</th><th>Status</th></tr></thead>
            <tbody>
            @forelse ($enquiries as $e)
                <tr onclick="location='{{ route('admin.enquiries.show', $e) }}'" style="cursor:pointer">
                    <td class="fw-semibold"><a href="{{ route('admin.enquiries.show', $e) }}">{{ $e->reference }}</a>@if ($e->isUrgent()) <span class="badge text-bg-danger">URGENT</span>@endif</td>
                    <td class="small">{{ $e->created_at->format('d M Y') }}</td>
                    <td>{{ $e->company_name }}<div class="small text-muted">{{ $e->country }}</div></td>
                    <td class="small">{{ $e->contact_person }}<div class="text-muted">{{ $e->email }}</div></td>
                    <td class="small">{{ $e->quotation_number ?: '—' }}</td>
                    <td>{{ $e->items_count }}</td>
                    <td class="text-end">{{ $e->money($e->total) }}</td>
                    <td>@include('partials.status-badge', ['enquiry' => $e])</td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted py-4">No enquiries found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if ($enquiries->hasPages())<div class="card-body">{{ $enquiries->links() }}</div>@endif
</div>
@endsection
