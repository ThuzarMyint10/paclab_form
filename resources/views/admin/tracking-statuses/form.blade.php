@extends('layouts.admin')
@section('title', $status->exists ? 'Edit status' : 'Add status')
@section('content')
<a href="{{ route('admin.tracking-statuses.index') }}" class="small">← Tracking statuses</a>
<h3 class="text-navy mt-1 mb-3">{{ $status->exists ? $status->label : 'Add tracking status' }}</h3>
<div class="card" style="max-width:700px">
    <div class="card-body">
        <form method="POST" action="{{ $status->exists ? route('admin.tracking-statuses.update', $status) : route('admin.tracking-statuses.store') }}">
            @csrf
            @if ($status->exists) @method('PUT') @endif
            <div class="mb-3"><label class="form-label required">Status wording</label><input name="label" value="{{ old('label', $status->label) }}" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Message shown to the customer</label><input name="customer_message" value="{{ old('customer_message', $status->customer_message) }}" class="form-control"></div>
            <div class="row g-3 mb-3">
                <div class="col-md-8">
                    <label class="form-label">Moves the job to</label>
                    <select name="sets_status" class="form-select" @disabled($status->exists && ! $status->is_manual)>
                        <option value="">— no change (information only) —</option>
                        @foreach (\App\Models\Enquiry::STATUSES as $k => $v)
                            @continue(in_array($k, ['submitted', 'quoted', 'declined']))
                            <option value="{{ $k }}" @selected(old('sets_status', $status->sets_status) === $k)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4"><label class="form-label">Order</label><input type="number" min="0" name="sort_order" value="{{ old('sort_order', $status->sort_order) }}" class="form-control"></div>
            </div>
            <div class="form-check"><input type="hidden" name="notify_customer" value="0"><input class="form-check-input" type="checkbox" name="notify_customer" value="1" id="n" @checked(old('notify_customer', $status->notify_customer))><label class="form-check-label" for="n">Email the customer by default</label></div>
            @if (! $status->exists || $status->is_manual)
                <div class="form-check mb-3"><input type="hidden" name="is_active" value="0"><input class="form-check-input" type="checkbox" name="is_active" value="1" id="a" @checked(old('is_active', $status->is_active))><label class="form-check-label" for="a">Show in the staff dropdown</label></div>
            @endif
            <button class="btn btn-green mt-2">Save</button>
        </form>
    </div>
</div>
@endsection
