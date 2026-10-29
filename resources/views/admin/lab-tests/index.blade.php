@extends('layouts.admin')
@section('title', 'Price List')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div>
        <h3 class="text-navy mb-0">Standard Price List</h3>
        <div class="small text-muted">Pre-approved prices used to calculate every quotation automatically.</div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.lab-tests.export') }}" class="btn btn-sm btn-outline-secondary">⬇ Export CSV</a>
        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#importBox">⬆ Import CSV</button>
        <a href="{{ route('admin.lab-tests.create') }}" class="btn btn-sm btn-green">+ Add test</a>
    </div>
</div>
<div class="collapse mb-3" id="importBox">
    <div class="card card-body">
        <form method="POST" action="{{ route('admin.lab-tests.import') }}" enctype="multipart/form-data" class="row g-2 align-items-end">
            @csrf
            <div class="col-md-6"><label class="form-label small">CSV file (same columns as the export: id, category, name, matrix, method, price_sgd, price_usd, is_active)</label><input type="file" name="file" accept=".csv,.txt" class="form-control form-control-sm" required></div>
            <div class="col-md-3"><button class="btn btn-sm btn-navy">Import</button></div>
            <div class="col-12 small text-muted">Tip: Export → edit prices in Excel → Save As “CSV UTF-8” → Import. Rows with an id update that test; rows without an id are added as new tests.</div>
        </form>
    </div>
</div>
<div class="card mb-3"><div class="card-body py-2">
    <form class="row g-2">
        <div class="col-md-5"><input name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Search test, matrix or method"></div>
        <div class="col-md-4">
            <select name="category" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">All categories</option>
                @foreach ($categories as $c)<option @selected(request('category') === $c)>{{ $c }}</option>@endforeach
            </select>
        </div>
        <div class="col-md-3"><button class="btn btn-sm btn-navy">Filter</button> <a href="{{ route('admin.lab-tests.index') }}" class="btn btn-sm btn-link">Reset</a></div>
    </form>
</div></div>
<div class="card">
    <div class="table-responsive">
        <table class="table table-sm table-hover align-middle mb-0">
            <thead><tr><th>Category</th><th>Test item</th><th>Matrix</th><th>Method</th><th class="text-end">SGD</th><th class="text-end">USD</th><th>Active</th><th></th></tr></thead>
            <tbody>
            @forelse ($tests as $t)
                <tr class="{{ $t->is_active ? '' : 'text-muted' }}">
                    <td class="small">{{ $t->category }}</td>
                    <td>{{ $t->name }}</td>
                    <td class="small">{{ $t->matrix }}</td>
                    <td class="small">{{ $t->method }}</td>
                    <td class="text-end">{{ number_format($t->price_sgd, 2) }}</td>
                    <td class="text-end">{{ number_format($t->price_usd, 2) }}</td>
                    <td>{!! $t->is_active ? '<span class="badge text-bg-success">Yes</span>' : '<span class="badge text-bg-secondary">No</span>' !!}</td>
                    <td class="text-end text-nowrap">
                        <a href="{{ route('admin.lab-tests.edit', [$t] + request()->only('q', 'category')) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                        <form method="POST" action="{{ route('admin.lab-tests.destroy', $t) }}" class="d-inline" onsubmit="return confirm('Remove this test from the price list?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">✕</button></form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted py-4">No tests found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if ($tests->hasPages())<div class="card-body">{{ $tests->links() }}</div>@endif
</div>
@endsection
