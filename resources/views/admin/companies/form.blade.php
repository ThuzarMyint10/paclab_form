@extends('layouts.admin')
@section('title', $company->exists ? 'Edit company' : 'Add company')
@section('content')
<a href="{{ route('admin.companies.index') }}" class="small">← Customer companies</a>
<h3 class="text-navy mt-1 mb-3">{{ $company->exists ? $company->name : 'Add customer company' }}</h3>
<div class="card" style="max-width:760px">
    <div class="card-body">
        <form method="POST" action="{{ $company->exists ? route('admin.companies.update', $company) : route('admin.companies.store') }}">
            @csrf
            @if ($company->exists) @method('PUT') @endif
            <div class="row g-3">
                <div class="col-md-8"><label class="form-label required">Company name</label><input name="name" value="{{ old('name', $company->name) }}" class="form-control" required></div>
                <div class="col-md-4"><label class="form-label">Country</label><input name="country" value="{{ old('country', $company->country) }}" class="form-control"></div>
                <div class="col-md-3"><label class="form-label required">Currency</label>
                    <select name="currency" class="form-select">@foreach (['SGD', 'USD'] as $cur)<option @selected(old('currency', $company->currency) === $cur)>{{ $cur }}</option>@endforeach</select></div>
                <div class="col-md-3"><label class="form-label required">Discount %</label><input type="number" step="0.01" min="0" max="100" name="discount_percent" value="{{ old('discount_percent', $company->discount_percent) }}" class="form-control" required></div>
                <div class="col-md-6"><label class="form-label">Payment terms</label><input name="payment_terms" value="{{ old('payment_terms', $company->payment_terms) }}" class="form-control" list="termsList">
                    <datalist id="termsList"><option>30 DAYS</option><option>45 DAYS</option><option>60 DAYS</option><option>TT 30 DAYS</option><option>TT IN ADVANCE</option><option>CASH ON DELIVERY</option></datalist></div>
                <div class="col-md-12"><label class="form-label">Address</label><textarea name="address" rows="2" class="form-control">{{ old('address', $company->address) }}</textarea></div>
                <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" value="{{ old('email', $company->email) }}" class="form-control"></div>
                <div class="col-md-6"><label class="form-label">Phone</label><input name="phone" value="{{ old('phone', $company->phone) }}" class="form-control"></div>
                <div class="col-md-12"><label class="form-label">Notes (e.g. package offer, exclusions)</label><input name="notes" value="{{ old('notes', $company->notes) }}" class="form-control"></div>
                <div class="col-md-12"><div class="form-check"><input type="hidden" name="is_active" value="0"><input class="form-check-input" type="checkbox" name="is_active" value="1" id="active" @checked(old('is_active', $company->is_active ?? true))><label class="form-check-label" for="active">Active</label></div></div>
            </div>
            <div class="d-flex justify-content-between mt-4">
                <button class="btn btn-green">Save</button>
            </div>
        </form>
        @if ($company->exists)
            <form method="POST" action="{{ route('admin.companies.destroy', $company) }}" onsubmit="return confirm('Delete this company? Past enquiries keep their details.')" class="mt-2 text-end">
                @csrf @method('DELETE')<button class="btn btn-sm btn-link text-danger">Delete company</button>
            </form>
        @endif
    </div>
</div>
@endsection
