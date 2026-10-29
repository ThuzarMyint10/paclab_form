@extends('layouts.admin')
@section('title', $customer->name)
@section('content')
<a href="{{ route('admin.customers.index') }}" class="small">← Customer logins</a>
<h3 class="text-navy mt-1 mb-3">{{ $customer->name }}</h3>
<div class="row g-4">
    <div class="col-lg-5">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.customers.update', $customer) }}">
                    @csrf @method('PUT')
                    <div class="mb-2"><label class="form-label">Name</label><input name="name" value="{{ old('name', $customer->name) }}" class="form-control" required></div>
                    <div class="mb-2"><label class="form-label">Email (login)</label><input type="email" name="email" value="{{ old('email', $customer->email) }}" class="form-control" required></div>
                    <div class="mb-2"><label class="form-label">Company (as typed)</label><input name="company_name" value="{{ old('company_name', $customer->company_name) }}" class="form-control"></div>
                    <div class="mb-2"><label class="form-label">Linked customer account</label>
                        <select name="company_id" class="form-select"><option value="">— none —</option>
                            @foreach ($companies as $co)<option value="{{ $co->id }}" @selected($customer->company_id == $co->id)>{{ $co->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="mb-2"><label class="form-label">Phone</label><input name="phone" value="{{ old('phone', $customer->phone) }}" class="form-control"></div>
                    <div class="form-check mb-3"><input type="hidden" name="is_active" value="0"><input class="form-check-input" type="checkbox" name="is_active" value="1" id="active" @checked($customer->is_active)><label class="form-check-label" for="active">Login enabled</label></div>
                    <button class="btn btn-green">Save</button>
                </form>
                <hr>
                <form method="POST" action="{{ route('admin.customers.password-link', $customer) }}">
                    @csrf <button class="btn btn-sm btn-outline-secondary">Email a password set-up link</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header">Enquiries by this customer</div>
            <div class="list-group list-group-flush">
                @forelse ($enquiries as $e)
                    <a href="{{ route('admin.enquiries.show', $e) }}" class="list-group-item list-group-item-action d-flex justify-content-between">
                        <span>{{ $e->reference }} · {{ $e->created_at->format('d M Y') }} · {{ $e->money($e->total) }}</span>
                        @include('partials.status-badge', ['enquiry' => $e])
                    </a>
                @empty
                    <div class="list-group-item text-muted small">No enquiries.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
