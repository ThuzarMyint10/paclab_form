@extends('layouts.admin')
@section('title', $test->exists ? 'Edit test' : 'Add test')
@section('content')
<a href="{{ route('admin.lab-tests.index') }}" class="small">← Price list</a>
<h3 class="text-navy mt-1 mb-3">{{ $test->exists ? 'Edit test' : 'Add test to price list' }}</h3>
<div class="card" style="max-width:760px">
    <div class="card-body">
        <form method="POST" action="{{ $test->exists ? route('admin.lab-tests.update', $test) : route('admin.lab-tests.store') }}">
            @csrf
            @if ($test->exists) @method('PUT') @endif
            <input type="hidden" name="q" value="{{ request('q') }}"><input type="hidden" name="category_filter" value="{{ request('category') }}">
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label required">Category</label><input name="category" list="cats" value="{{ old('category', $test->category) }}" class="form-control" required>
                    <datalist id="cats">@foreach ($categories as $c)<option>{{ $c }}</option>@endforeach</datalist></div>
                <div class="col-md-6"><label class="form-label required">Test item</label><input name="name" value="{{ old('name', $test->name) }}" class="form-control" required></div>
                <div class="col-md-6"><label class="form-label">Matrix</label><input name="matrix" value="{{ old('matrix', $test->matrix) }}" class="form-control"></div>
                <div class="col-md-6"><label class="form-label">Method</label><input name="method" value="{{ old('method', $test->method) }}" class="form-control"></div>
                <div class="col-md-3"><label class="form-label required">Price SGD</label><input type="number" step="0.01" min="0" name="price_sgd" value="{{ old('price_sgd', $test->price_sgd) }}" class="form-control" required></div>
                <div class="col-md-3"><label class="form-label required">Price USD</label><input type="number" step="0.01" min="0" name="price_usd" value="{{ old('price_usd', $test->price_usd) }}" class="form-control" required></div>
                <div class="col-md-3"><label class="form-label">Sort order</label><input type="number" min="0" name="sort_order" value="{{ old('sort_order', $test->sort_order) }}" class="form-control"></div>
                <div class="col-md-3 d-flex align-items-end"><div class="form-check"><input type="hidden" name="is_active" value="0"><input class="form-check-input" type="checkbox" name="is_active" value="1" id="active" @checked(old('is_active', $test->is_active))><label class="form-check-label" for="active">Active (shown to customers)</label></div></div>
            </div>
            <button class="btn btn-green mt-4">Save</button>
        </form>
    </div>
</div>
@endsection
