@extends('layouts.public')
@section('title', 'My Jobs')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div>
        <div class="section-title">Customer portal</div>
        <h2 class="text-navy mb-0">Welcome, {{ auth()->user()->name }}</h2>
    </div>
    <a href="{{ route('enquiry.create') }}" class="btn btn-green">+ New price request</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4"><div class="card stat-card"><div class="card-body"><div class="num">{{ $counts['awaiting'] }}</div><div class="text-muted small">Quotations awaiting your approval</div></div></div></div>
    <div class="col-md-4"><div class="card stat-card"><div class="card-body"><div class="num">{{ $counts['active'] }}</div><div class="text-muted small">Jobs in progress</div></div></div></div>
    <div class="col-md-4"><div class="card stat-card"><div class="card-body"><div class="num">{{ $counts['done'] }}</div><div class="text-muted small">Completed jobs</div></div></div></div>
</div>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>My enquiries &amp; jobs</span>
        <form class="d-flex" style="max-width:280px"><input name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Search reference…"></form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>Reference</th><th>Submitted</th><th>Quotation</th><th>Samples</th><th class="text-end">Amount</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse ($enquiries as $e)
                <tr>
                    <td class="fw-semibold">{{ $e->reference }}</td>
                    <td>{{ $e->created_at->format('d M Y') }}</td>
                    <td>{{ $e->quotation_number ?: '—' }}</td>
                    <td>{{ $e->items_count }} test(s)</td>
                    <td class="text-end">{{ $e->quotation_number ? $e->money($e->total) : '—' }}</td>
                    <td>@include('partials.status-badge', ['enquiry' => $e])</td>
                    <td class="text-end">
                        <a href="{{ route('portal.enquiries.show', $e) }}" class="btn btn-sm {{ $e->status === 'quoted' ? 'btn-green' : 'btn-outline-secondary' }}">
                            {{ $e->status === 'quoted' ? 'Review quotation' : 'View' }}
                        </a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">No enquiries yet. <a href="{{ route('enquiry.create') }}">Submit your first price request</a>.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if ($enquiries->hasPages())<div class="card-body">{{ $enquiries->links() }}</div>@endif
</div>

@if ($invoices->isNotEmpty())
    <div class="card">
        <div class="card-header">Recent invoices</div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>Invoice</th><th>Date</th><th>Job</th><th class="text-end">Amount</th><th>Status</th><th></th></tr></thead>
                <tbody>
                @foreach ($invoices as $inv)
                    <tr>
                        <td class="fw-semibold">{{ $inv->invoice_number }}</td>
                        <td>{{ $inv->invoice_date->format('d M Y') }}</td>
                        <td>{{ $inv->enquiry->reference }}</td>
                        <td class="text-end">{{ \App\Models\Enquiry::symbolFor($inv->currency) }}{{ number_format($inv->grand_total, 2) }}</td>
                        <td><span class="badge text-bg-{{ $inv->status === 'paid' ? 'success' : 'warning' }}">{{ $inv->statusLabel() }}</span></td>
                        <td class="text-end"><a class="btn btn-sm btn-outline-secondary" href="{{ route('portal.invoices.pdf', $inv) }}">⬇ PDF</a></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection
