@extends('layouts.admin')
@section('title', 'Invoices')
@section('content')
<h3 class="text-navy mb-3">Invoices</h3>
<div class="card mb-3"><div class="card-body py-2">
    <form class="row g-2">
        <div class="col-md-5"><input name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Invoice no., PO no., company or enquiry ref"></div>
        <div class="col-md-3">
            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">All statuses</option>
                @foreach (\App\Models\Invoice::STATUSES as $k => $v)<option value="{{ $k }}" @selected(request('status') === $k)>{{ $v }}</option>@endforeach
            </select>
        </div>
        <div class="col-md-2"><button class="btn btn-sm btn-navy">Filter</button></div>
    </form>
</div></div>
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>Invoice</th><th>Date</th><th>Customer</th><th>Job</th><th>PO</th><th>Terms</th><th class="text-end">Grand total</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse ($invoices as $inv)
                <tr>
                    <td class="fw-semibold">{{ $inv->invoice_number }}</td>
                    <td>{{ $inv->invoice_date->format('d M Y') }}</td>
                    <td>{{ $inv->enquiry->company_name }}</td>
                    <td><a href="{{ route('admin.enquiries.show', $inv->enquiry_id) }}">{{ $inv->enquiry->reference }}</a></td>
                    <td>{{ $inv->po_number ?: '—' }}</td>
                    <td class="small">{{ $inv->payment_terms }}</td>
                    <td class="text-end">{{ \App\Models\Enquiry::symbolFor($inv->currency) }}{{ number_format($inv->grand_total, 2) }}</td>
                    <td><span class="badge text-bg-{{ ['draft' => 'secondary', 'issued' => 'warning', 'paid' => 'success', 'void' => 'dark'][$inv->status] ?? 'secondary' }}">{{ $inv->statusLabel() }}</span></td>
                    <td class="text-end text-nowrap">
                        <a href="{{ route('admin.invoices.pdf', $inv) }}" target="_blank" class="btn btn-sm btn-outline-secondary">PDF</a>
                        <a href="{{ route('admin.invoices.edit', $inv) }}" class="btn btn-sm btn-outline-primary">Open</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="9" class="text-center text-muted py-4">No invoices yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if ($invoices->hasPages())<div class="card-body">{{ $invoices->links() }}</div>@endif
</div>
@endsection
