@extends('layouts.admin')
@section('title', 'Tracking Statuses')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h3 class="text-navy mb-0">Tracking Statuses</h3>
        <div class="small text-muted">The pre-described words staff choose from when updating a job. “System” steps are set automatically by the workflow.</div>
    </div>
    <a href="{{ route('admin.tracking-statuses.create') }}" class="btn btn-sm btn-green">+ Add status</a>
</div>
<div class="card">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th>#</th><th>Status wording</th><th>Message shown to customer</th><th>Moves job to</th><th>Type</th><th>Email customer</th><th>Active</th><th></th></tr></thead>
            <tbody>
            @foreach ($statuses as $s)
                <tr class="{{ $s->is_active ? '' : 'text-muted' }}">
                    <td class="small">{{ $s->sort_order }}</td>
                    <td class="fw-semibold">{{ $s->label }}</td>
                    <td class="small">{{ $s->customer_message }}</td>
                    <td class="small">{{ $s->sets_status ? \App\Models\Enquiry::STATUSES[$s->sets_status] ?? $s->sets_status : '—' }}</td>
                    <td>{!! $s->is_manual ? '<span class="badge text-bg-primary">Staff choice</span>' : '<span class="badge text-bg-light border">System</span>' !!}</td>
                    <td>{{ $s->notify_customer ? 'Yes' : 'No' }}</td>
                    <td>{{ $s->is_active ? 'Yes' : 'No' }}</td>
                    <td class="text-end"><a href="{{ route('admin.tracking-statuses.edit', $s) }}" class="btn btn-sm btn-outline-primary">Edit</a></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
