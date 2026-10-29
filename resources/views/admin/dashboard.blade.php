@extends('layouts.admin')
@section('title', 'Dashboard')
@section('content')
<h3 class="text-navy mb-4">Dashboard</h3>
@php
    $tiles = [
        ['submitted', 'New enquiries to price', 'submitted', 'secondary'],
        ['quoted', 'Quotations awaiting customer', 'quoted', 'info'],
        ['approved', 'Approved – awaiting samples', 'approved', 'primary'],
        ['active', 'Samples in the lab', 'active', 'warning'],
    ];
    $inLabCount = ($counts['sample_received'] ?? 0) + ($counts['in_progress'] ?? 0) + ($counts['on_hold'] ?? 0);
@endphp
<div class="row g-3 mb-4">
    @foreach ($tiles as [$key, $label, $filter, $colour])
        <div class="col-sm-6 col-xl-3">
            <a href="{{ route('admin.enquiries.index', ['status' => $filter]) }}" class="card stat-card text-decoration-none border-start border-4 border-{{ $colour }}">
                <div class="card-body">
                    <div class="num">{{ (int) ($key === 'active' ? $inLabCount : ($counts[$key] ?? 0)) }}</div>
                    <div class="text-muted small">{{ $label }}</div>
                </div>
            </a>
        </div>
    @endforeach
</div>

<div class="row g-4">
    @foreach ([
        ['New enquiries — price & send quotation', $newEnquiries, 'created_at'],
        ['Approved — waiting for samples to arrive', $awaitingSamples, 'responded_at'],
        ['In the lab', $inLab, 'updated_at'],
        ['Completed — ready to invoice', $toInvoice, 'completed_at'],
    ] as [$title, $list, $dateField])
        <div class="col-xl-6">
            <div class="card h-100">
                <div class="card-header">{{ $title }} <span class="badge text-bg-light border">{{ $list->count() }}</span></div>
                <div class="list-group list-group-flush">
                    @forelse ($list as $e)
                        <a href="{{ route('admin.enquiries.show', $e) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-semibold">{{ $e->reference }} <span class="text-muted fw-normal">· {{ $e->company_name }}</span></div>
                                <div class="small text-muted">{{ $e->contact_person }} · {{ optional($e->{$dateField})->diffForHumans() }}</div>
                            </div>
                            @include('partials.status-badge', ['enquiry' => $e])
                        </a>
                    @empty
                        <div class="list-group-item text-muted small">Nothing here.</div>
                    @endforelse
                </div>
            </div>
        </div>
    @endforeach
    <div class="col-12">
        <div class="card">
            <div class="card-header">Unpaid invoices</div>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead><tr><th>Invoice</th><th>Date</th><th>Customer</th><th>Terms</th><th class="text-end">Amount</th><th></th></tr></thead>
                    <tbody>
                    @forelse ($unpaid as $inv)
                        <tr>
                            <td>{{ $inv->invoice_number }}</td>
                            <td>{{ $inv->invoice_date->format('d M Y') }}</td>
                            <td>{{ $inv->enquiry->company_name }}</td>
                            <td>{{ $inv->payment_terms }}</td>
                            <td class="text-end">{{ \App\Models\Enquiry::symbolFor($inv->currency) }}{{ number_format($inv->grand_total, 2) }}</td>
                            <td class="text-end"><a href="{{ route('admin.invoices.edit', $inv) }}" class="btn btn-sm btn-outline-secondary">Open</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-muted small">No unpaid invoices.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
