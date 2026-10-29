@extends('layouts.admin')
@section('title', 'Invoice '.$invoice->invoice_number)
@section('content')
@php $draft = $invoice->status === 'draft'; @endphp
<a href="{{ route('admin.enquiries.show', $invoice->enquiry_id) }}" class="small">← Back to {{ $invoice->enquiry->reference }}</a>
<div class="d-flex flex-wrap justify-content-between align-items-center mt-1 mb-3 gap-2">
    <h3 class="text-navy mb-0">Tax Invoice {{ $invoice->invoice_number }}
        <span class="badge text-bg-{{ ['draft' => 'secondary', 'issued' => 'warning', 'paid' => 'success', 'void' => 'dark'][$invoice->status] ?? 'secondary' }}">{{ $invoice->statusLabel() }}</span>
    </h3>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.invoices.pdf', $invoice) }}" target="_blank" class="btn btn-sm btn-outline-secondary">Preview PDF</a>
        @if ($invoice->status === 'issued')
            <form method="POST" action="{{ route('admin.invoices.status', $invoice) }}">@csrf<input type="hidden" name="status" value="paid"><button class="btn btn-sm btn-green">Mark as paid</button></form>
        @endif
        @if ($invoice->status === 'paid')
            <form method="POST" action="{{ route('admin.invoices.status', $invoice) }}">@csrf<input type="hidden" name="status" value="issued"><button class="btn btn-sm btn-outline-secondary">Mark as unpaid</button></form>
        @endif
        @if (in_array($invoice->status, ['draft', 'issued']))
            <form method="POST" action="{{ route('admin.invoices.status', $invoice) }}" onsubmit="return confirm('Void this invoice?')">@csrf<input type="hidden" name="status" value="void"><button class="btn btn-sm btn-outline-danger">Void</button></form>
        @endif
    </div>
</div>

@unless ($draft)
    <div class="alert alert-secondary py-2">This invoice has been issued{{ $invoice->issued_at ? ' on '.$invoice->issued_at->format('d M Y') : '' }} and can no longer be edited. To correct it, void it and generate a new one from the job.</div>
@endunless

<form method="POST" action="{{ route('admin.invoices.update', $invoice) }}">
    @csrf @method('PUT')
    <fieldset @disabled(! $draft)>
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <label class="form-label">Sold to</label>
                    <textarea name="bill_to" rows="6" class="form-control mb-3">{{ old('bill_to', $invoice->bill_to) }}</textarea>
                    <div class="row g-2">
                        <div class="col-6"><label class="form-label">Invoice date</label><input type="date" name="invoice_date" value="{{ $invoice->invoice_date->format('Y-m-d') }}" class="form-control form-control-sm"></div>
                        <div class="col-6"><label class="form-label">Currency</label>
                            <select name="currency" class="form-select form-select-sm">@foreach (['SGD', 'USD'] as $c)<option @selected($invoice->currency === $c)>{{ $c }}</option>@endforeach</select>
                        </div>
                        <div class="col-6"><label class="form-label">Payment terms</label><input name="payment_terms" value="{{ $invoice->payment_terms }}" class="form-control form-control-sm"></div>
                        <div class="col-6"><label class="form-label">Your ref (PO no.)</label><input name="po_number" value="{{ $invoice->po_number }}" class="form-control form-control-sm"></div>
                        <div class="col-12"><label class="form-label">Our ref</label><input name="our_ref" value="{{ $invoice->our_ref }}" class="form-control form-control-sm"></div>
                        <div class="col-6"><label class="form-label">Discount %</label><input type="number" step="0.01" min="0" max="100" name="discount_percent" value="{{ $invoice->discount_percent }}" class="form-control form-control-sm"></div>
                        <div class="col-6"><label class="form-label">GST %</label><input type="number" step="0.01" min="0" max="100" name="gst_percent" value="{{ $invoice->gst_percent }}" class="form-control form-control-sm"></div>
                        <div class="col-12"><label class="form-label">Notes</label><textarea name="notes" rows="2" class="form-control form-control-sm">{{ $invoice->notes }}</textarea></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card">
                <div class="table-responsive">
                    <table class="table align-middle mb-0" id="lines">
                        <thead><tr><th>Description</th><th style="width:80px">Qty</th><th style="width:130px" class="text-end">Rate</th><th style="width:120px" class="text-end">Amount</th></tr></thead>
                        <tbody>
                        @foreach ($invoice->items as $i => $line)
                            <tr>
                                <td><input name="items[{{ $i }}][description]" value="{{ $line->description }}" class="form-control form-control-sm"></td>
                                <td><input type="number" min="1" name="items[{{ $i }}][quantity]" value="{{ $line->quantity }}" class="form-control form-control-sm"></td>
                                <td><input type="number" step="0.01" min="0" name="items[{{ $i }}][rate]" value="{{ $line->rate }}" class="form-control form-control-sm text-end"></td>
                                <td class="text-end">{{ number_format($line->amount, 2) }}</td>
                            </tr>
                        @endforeach
                        @if ($draft)
                            @for ($j = $invoice->items->count(); $j < $invoice->items->count() + 3; $j++)
                                <tr>
                                    <td><input name="items[{{ $j }}][description]" class="form-control form-control-sm" placeholder="Extra line (optional)"></td>
                                    <td><input type="number" min="1" name="items[{{ $j }}][quantity]" value="1" class="form-control form-control-sm"></td>
                                    <td><input type="number" step="0.01" min="0" name="items[{{ $j }}][rate]" class="form-control form-control-sm text-end"></td>
                                    <td></td>
                                </tr>
                            @endfor
                        @endif
                        </tbody>
                        @php $sym = \App\Models\Enquiry::symbolFor($invoice->currency); @endphp
                        <tfoot>
                            <tr><td colspan="3" class="text-end">TOTAL</td><td class="text-end">{{ $sym }}{{ number_format($invoice->total, 2) }}</td></tr>
                            <tr><td colspan="3" class="text-end">LESS: DISCOUNT @ {{ rtrim(rtrim(number_format($invoice->discount_percent, 2), '0'), '.') }}%</td><td class="text-end">{{ number_format($invoice->discount_amount, 2) }}</td></tr>
                            <tr><td colspan="3" class="text-end">SUB-TOTAL</td><td class="text-end">{{ number_format($invoice->subtotal, 2) }}</td></tr>
                            <tr><td colspan="3" class="text-end">ADD GST @ {{ rtrim(rtrim(number_format($invoice->gst_percent, 2), '0'), '.') }}%</td><td class="text-end">{{ number_format($invoice->gst_amount, 2) }}</td></tr>
                            <tr class="fw-bold text-navy"><td colspan="3" class="text-end">GRAND TOTAL</td><td class="text-end fs-5">{{ $sym }}{{ number_format($invoice->grand_total, 2) }}</td></tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            @if ($draft)
                <div class="d-flex gap-2 mt-3 justify-content-end">
                    <button class="btn btn-outline-secondary" name="action" value="save">Save &amp; recalculate</button>
                    <button class="btn btn-green" name="action" value="issue" onclick="return confirm('Issue this invoice and email it to {{ $invoice->enquiry->email }}?')">Save, Issue &amp; Email to customer</button>
                </div>
                <div class="small text-muted text-end mt-1">To remove a line, clear its description.</div>
            @endif
        </div>
    </div>
    </fieldset>
</form>
@endsection
