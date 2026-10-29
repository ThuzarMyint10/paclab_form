@extends('layouts.admin')
@section('title', $enquiry->reference)
@section('content')
@php $e = $enquiry; @endphp
@php $locked = ! $e->canEditPricing(); @endphp

{{-- Header --}}
<div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
    <div>
        <a href="{{ route('admin.enquiries.index') }}" class="small">← All enquiries</a>
        <h3 class="text-navy mb-1 mt-1">{{ $e->reference }}
            @include('partials.status-badge', ['enquiry' => $e])
            @if ($e->isUrgent())<span class="badge text-bg-danger">{{ strtoupper($e->turnaroundLabel()) }}</span>@endif
        </h3>
        <div class="small text-muted">
            {{ $e->company_name }} · {{ $e->contact_person }} · <a href="mailto:{{ $e->email }}">{{ $e->email }}</a> ·
            received {{ $e->created_at->format('d M Y H:i') }} · {{ $e->currency }}
            @if ($e->company) · <span class="badge text-bg-light border">Account: {{ $e->company->name }} ({{ rtrim(rtrim(number_format($e->company->discount_percent, 2), '0'), '.') }}% / {{ $e->company->payment_terms }})</span>@endif
        </div>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('admin.enquiries.document', [$e, 'quotation']) }}" target="_blank" class="btn btn-sm btn-outline-secondary">Quotation PDF{{ $e->quotation_number ? '' : ' (draft)' }}</a>
        @if ($e->ssf_number)
            <a href="{{ route('admin.enquiries.document', [$e, 'ssf']) }}" target="_blank" class="btn btn-sm btn-outline-secondary">Sample Submission Form</a>
        @endif
        @if ($e->po_file)
            <a href="{{ route('admin.enquiries.po-file', $e) }}" class="btn btn-sm btn-outline-secondary">Customer PO</a>
        @endif
        @if ($e->isApprovedJob())
            <a href="{{ route('admin.coa.edit', $e) }}" class="btn btn-sm btn-outline-primary">COA / Results</a>
            <form method="POST" action="{{ route('admin.invoices.store', $e) }}" onsubmit="return confirm('Generate a new draft invoice from this job?')">
                @csrf <button class="btn btn-sm btn-navy">Generate Invoice</button>
            </form>
        @endif
    </div>
</div>

{{-- What to do next --}}
@php
    $next = match ($e->status) {
        'submitted' => ['info', 'Step 1 — Check the prices below (calculated from the standard price list), amend if a discount applies, then click “Save & Send Quotation”.'],
        'quoted' => ['info', 'Quotation '.$e->quotation_number.' was emailed on '.optional($e->quoted_at)->format('d M Y').'. Waiting for the customer to approve or decline. You can still revise and re-send it, or record the customer’s reply yourself.'],
        'approved' => ['primary', 'Approved — Sample Submission Form '.$e->ssf_number.' was generated and emailed. When the samples arrive, update the tracking status (e.g. “Sample Received – via Courier”).'],
        'declined' => ['secondary', 'The customer declined this quotation. The process has ended — no further transactions are possible.'],
        'sample_received', 'in_progress', 'on_hold' => ['warning', 'Samples are in the lab. Keep the tracking status up to date; enter results under “COA / Results” when testing is complete.'],
        'completed', 'reported', 'dispatched' => ['success', 'Testing is complete. Release the COA and generate the invoice if not done yet.'],
        default => null,
    };
@endphp
@if ($next)
    <div class="alert alert-{{ $next[0] }} py-2">{{ $next[1] }}</div>
@endif
@if ($e->decline_reason)
    <div class="alert alert-secondary py-2"><strong>Decline reason:</strong> {{ $e->decline_reason }}</div>
@endif

<div class="row g-4">
    <div class="col-xl-8">

        {{-- PRICING / QUOTATION --}}
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Pricing &amp; quotation {{ $e->quotation_number ? '· '.$e->quotation_number : '' }}</span>
                @unless ($locked)
                    <button form="priceListForm" class="btn btn-sm btn-outline-secondary" onclick="return confirm('Reset every line to the standard price list and the company\'s agreed discount?')">↺ Reset to standard price list</button>
                @endunless
            </div>
            <div class="card-body">
                @if ($locked)
                    @include('partials.quotation-table', ['enquiry' => $e])
                    <div class="small text-muted mt-2">Prices are locked because the customer has responded to the quotation.</div>
                @else
                    <form method="POST" action="{{ route('admin.enquiries.pricing', $e) }}" id="pricingForm">
                        @csrf @method('PUT')
                        <div class="table-responsive">
                            <table class="table table-sm align-middle">
                                <thead>
                                    <tr><th>Sample &amp; turnaround</th><th>Test</th><th class="text-end">List ({{ $e->currency }})</th><th>Unit price</th><th>Qty</th><th class="text-end">Amount</th><th></th></tr>
                                </thead>
                                <tbody>
                                @foreach ($e->samples as $s)
                                    @forelse ($s->items as $item)
                                        <tr class="{{ $item->isAmended() ? 'amended' : '' }}" data-sample="{{ $s->id }}">
                                            <td class="small">@if ($loop->first)<strong>{{ $s->description }}</strong><div class="text-muted">{{ $s->quantity }} sample(s)</div>
                                                <select name="sample_turnaround[{{ $s->id }}]" data-sample="{{ $s->id }}" class="form-select form-select-sm mt-1 js-ta {{ $s->urgent ? 'border-danger text-danger fw-semibold' : '' }}" style="width:120px">
                                                    <option value="standard" @selected(! $s->urgent)>Standard</option>
                                                    <option value="urgent" @selected($s->urgent)>Urgent</option>
                                                </select>@endif</td>
                                            <td class="small">{{ $item->test_name }}<div class="text-muted">{{ collect([$item->matrix, $item->method])->filter()->implode(' – ') }}</div></td>
                                            <td class="text-end small">{{ number_format($item->list_price, 2) }}</td>
                                            <td><input type="number" step="0.01" min="0" name="items[{{ $item->id }}][unit_price]" value="{{ $item->unit_price }}" class="form-control form-control-sm price-input js-price" data-list="{{ $item->list_price }}"></td>
                                            <td><input type="number" min="1" name="items[{{ $item->id }}][quantity]" value="{{ $item->quantity }}" class="form-control form-control-sm js-qty" style="width:70px"></td>
                                            <td class="text-end js-amount">{{ number_format($item->amount, 2) }}</td>
                                            <td><button form="rm{{ $item->id }}" class="btn btn-sm btn-link text-danger p-0" title="Remove" onclick="return confirm('Remove this test?')">✕</button></td>
                                        </tr>
                                    @empty
                                        <tr><td class="small"><strong>{{ $s->description }}</strong></td><td colspan="6" class="small text-muted">No tests — add one below.</td></tr>
                                    @endforelse
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="small text-muted mb-3">Highlighted rows have been amended from the standard list price. Tip: to give a line discount, simply type the new unit price. Urgent samples get a +{{ rtrim(rtrim(number_format(\App\Models\Setting::get('urgent_surcharge_percent'), 2), '0'), '.') }}% surcharge and no discount; the overall discount applies to standard samples only.</div>

                        <div class="row g-3">
                            <div class="col-md-7">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label">Discount % <span class="text-muted small">(standard samples)</span></label>
                                        <input type="number" step="0.01" min="0" max="100" name="discount_percent" id="discountPct" value="{{ $e->discount_percent }}" class="form-control form-control-sm">
                                        @if ($e->company)
                                            <div class="form-text">Agreed for {{ $e->company->name }}: {{ rtrim(rtrim(number_format($e->company->discount_percent, 2), '0'), '.') }}%{{ $e->company->notes ? ' ('.$e->company->notes.')' : '' }}</div>
                                        @else
                                            <div class="form-text">Not linked to a customer account — link one under “Customer details” to apply the agreed discount.</div>
                                        @endif
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label">Payment terms</label>
                                        <input name="payment_terms" value="{{ $e->payment_terms }}" class="form-control form-control-sm" list="termsList">
                                        <datalist id="termsList"><option>30 DAYS</option><option>45 DAYS</option><option>60 DAYS</option><option>TT 30 DAYS</option><option>TT IN ADVANCE</option><option>CASH ON DELIVERY</option></datalist>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Notes printed on the quotation</label>
                                        <textarea name="quotation_notes" rows="3" class="form-control form-control-sm" placeholder="Leave blank to use the standard notes from Settings">{{ $e->quotation_notes }}</textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <table class="table table-sm mb-2">
                                    <tr><td>Subtotal</td><td class="text-end" id="tSub">{{ $e->money($e->subtotal) }}</td></tr>
                                    <tr id="rSur" @if ((float) $e->surcharge_amount <= 0) style="display:none" @endif><td>Urgent surcharge ({{ rtrim(rtrim(number_format(\App\Models\Setting::get('urgent_surcharge_percent'), 2), '0'), '.') }}% on urgent samples)</td><td class="text-end" id="tSur">+{{ $e->money($e->surcharge_amount) }}</td></tr>
                                    <tr><td>Discount <span class="small text-muted">(standard samples)</span></td><td class="text-end text-danger" id="tDisc">-{{ $e->money($e->discount_amount) }}</td></tr>
                                    <tr class="fw-bold text-navy"><td>AMOUNT DUE</td><td class="text-end fs-5" id="tTotal">{{ $e->money($e->total) }}</td></tr>
                                </table>
                                <div class="d-grid gap-2">
                                    <button class="btn btn-outline-secondary btn-sm" name="action" value="save">Save prices</button>
                                    <button class="btn btn-green" name="action" value="send" onclick="return confirm('Save and email the quotation to {{ $e->email }}?')">
                                        {{ $e->quotation_number ? 'Save & Re-send Revised Quotation' : 'Save & Send Quotation to Customer' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>

                    {{-- standalone forms referenced by buttons above (forms cannot be nested) --}}
                    <form method="POST" action="{{ route('admin.enquiries.price-list', $e) }}" id="priceListForm">@csrf</form>
                    @foreach ($e->items as $item)
                        <form method="POST" action="{{ route('admin.enquiries.items.remove', [$e, $item]) }}" id="rm{{ $item->id }}">@csrf @method('DELETE')</form>
                    @endforeach

                    <hr>
                    <div class="section-title mb-2">Add a test</div>
                    <form method="POST" action="{{ route('admin.enquiries.items.add', $e) }}" class="row g-2 align-items-end">
                        @csrf
                        <div class="col-md-2">
                            <label class="form-label small">Sample</label>
                            <select name="enquiry_sample_id" class="form-select form-select-sm">
                                @foreach ($e->samples as $s)<option value="{{ $s->id }}">{{ \Illuminate\Support\Str::limit($s->description, 30) }}</option>@endforeach
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small">Test from price list</label>
                            <input class="form-control form-control-sm mb-1" id="addSearch" placeholder="Search…">
                            <select name="lab_test_id" id="addTest" class="form-select form-select-sm"><option value="">— or type a custom test on the right —</option></select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Custom test / price</label>
                            <input name="test_name" class="form-control form-control-sm mb-1" placeholder="Custom name">
                            <input name="unit_price" type="number" step="0.01" min="0" class="form-control form-control-sm" placeholder="Price">
                        </div>
                        <div class="col-md-1">
                            <label class="form-label small">Qty</label>
                            <input name="quantity" type="number" min="1" class="form-control form-control-sm" placeholder="auto">
                        </div>
                        <div class="col-md-1 d-grid"><button class="btn btn-sm btn-navy">Add</button></div>
                    </form>
                @endif
            </div>
        </div>

        {{-- CUSTOMER RESPONSE (staff can record it) --}}
        @if ($e->status === 'quoted')
            <div class="card mb-4">
                <div class="card-header">Record customer response (if the customer replied by email / phone)</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.enquiries.response', $e) }}" enctype="multipart/form-data" class="row g-2 align-items-end">
                        @csrf
                        <div class="col-md-2">
                            <label class="form-label small">Response</label>
                            <select name="response" class="form-select form-select-sm" id="respSel">
                                <option value="approve">Approved</option>
                                <option value="decline">Declined</option>
                            </select>
                        </div>
                        <div class="col-md-3 resp-approve"><label class="form-label small">Approved by</label><input name="approved_by_name" class="form-control form-control-sm" value="{{ $e->contact_person }}"></div>
                        <div class="col-md-2 resp-approve"><label class="form-label small">PO number</label><input name="po_number" class="form-control form-control-sm"></div>
                        <div class="col-md-3 resp-approve"><label class="form-label small">PO document</label><input type="file" name="po_document" class="form-control form-control-sm"></div>
                        <div class="col-md-8 resp-decline" style="display:none"><label class="form-label small">Reason</label><input name="decline_reason" class="form-control form-control-sm"></div>
                        <div class="col-md-2 d-grid"><button class="btn btn-sm btn-navy" onclick="return confirm('Record this response? Approval generates the Sample Submission Form and emails the customer.')">Record</button></div>
                    </form>
                </div>
            </div>
        @endif

        {{-- SAMPLES --}}
        <div class="card mb-4">
            <div class="card-header">Samples @if ($e->po_number)<span class="float-end small fw-normal">Customer PO: <strong>{{ $e->po_number }}</strong></span>@endif</div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.enquiries.samples', $e) }}">
                    @csrf @method('PUT')
                    <div class="table-responsive">
                        <table class="table table-sm table-light-head align-middle">
                            <thead><tr><th>Lab code</th><th>Description</th><th>Type</th><th>Batch</th><th>Qty</th><th>Storage</th></tr></thead>
                            <tbody>
                            @foreach ($e->samples as $s)
                                <tr>
                                    <td style="width:110px"><input name="samples[{{ $s->id }}][lab_code]" value="{{ $s->lab_code }}" class="form-control form-control-sm" placeholder="on receipt"></td>
                                    <td class="min-w-desc"><input name="samples[{{ $s->id }}][description]" value="{{ $s->description }}" class="form-control form-control-sm" required></td>
                                    <td><input name="samples[{{ $s->id }}][sample_type]" value="{{ $s->sample_type }}" class="form-control form-control-sm"></td>
                                    <td><input name="samples[{{ $s->id }}][batch_no]" value="{{ $s->batch_no }}" class="form-control form-control-sm"></td>
                                    <td style="width:75px"><input type="number" min="1" name="samples[{{ $s->id }}][quantity]" value="{{ $s->quantity }}" class="form-control form-control-sm"></td>
                                    <td><input name="samples[{{ $s->id }}][storage]" value="{{ $s->storage }}" class="form-control form-control-sm"></td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    @unless ($e->isDeclined())
                        <button class="btn btn-sm btn-outline-secondary" name="action" value="save">Save samples</button>
                        @if ($e->isApprovedJob())
                            <button class="btn btn-sm btn-outline-primary" name="action" value="assign_codes">Save &amp; auto-assign lab codes</button>
                        @endif
                    @endunless
                </form>
            </div>
        </div>

        {{-- INVOICES & COA --}}
        @if ($e->isApprovedJob())
            <div class="card mb-4">
                <div class="card-header">Invoices &amp; Certificate of Analysis</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-7">
                            <div class="section-title mb-2">Invoices</div>
                            @forelse ($e->invoices as $inv)
                                <div class="d-flex justify-content-between align-items-center border rounded p-2 mb-2">
                                    <div>
                                        <strong>{{ $inv->invoice_number }}</strong>
                                        <span class="badge text-bg-{{ ['draft' => 'secondary', 'issued' => 'warning', 'paid' => 'success', 'void' => 'dark'][$inv->status] ?? 'secondary' }}">{{ $inv->statusLabel() }}</span>
                                        <div class="small text-muted">{{ $inv->invoice_date->format('d M Y') }} · {{ \App\Models\Enquiry::symbolFor($inv->currency) }}{{ number_format($inv->grand_total, 2) }}</div>
                                    </div>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('admin.invoices.pdf', $inv) }}" target="_blank" class="btn btn-sm btn-outline-secondary">PDF</a>
                                        <a href="{{ route('admin.invoices.edit', $inv) }}" class="btn btn-sm btn-outline-primary">Open</a>
                                    </div>
                                </div>
                            @empty
                                <p class="small text-muted">No invoice yet. Use <strong>Generate Invoice</strong> (top right) — it is created from the approved quotation.</p>
                            @endforelse
                        </div>
                        <div class="col-md-5">
                            <div class="section-title mb-2">Certificate of Analysis</div>
                            @if ($e->coa)
                                <div class="border rounded p-2">
                                    <strong>{{ $e->coa->report_number }}</strong>
                                    <span class="badge text-bg-{{ $e->coa->isReleased() ? 'success' : 'secondary' }}">{{ $e->coa->isReleased() ? 'Released' : 'Draft' }}</span>
                                    <div class="mt-2 d-flex gap-1">
                                        <a href="{{ route('admin.enquiries.document', [$e, 'coa']) }}" target="_blank" class="btn btn-sm btn-outline-secondary">PDF</a>
                                        <a href="{{ route('admin.coa.edit', $e) }}" class="btn btn-sm btn-outline-primary">Edit results</a>
                                    </div>
                                </div>
                            @else
                                <a href="{{ route('admin.coa.edit', $e) }}" class="btn btn-sm btn-outline-primary">Enter results &amp; create COA</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div class="col-xl-4">
        {{-- TRACKING --}}
        <div class="card mb-4">
            <div class="card-header">Update tracking status</div>
            <div class="card-body">
                @if ($e->isApprovedJob())
                    <form method="POST" action="{{ route('admin.enquiries.tracking', $e) }}">
                        @csrf
                        <label class="form-label">New status</label>
                        <select name="tracking_status_id" id="trackSel" class="form-select mb-2" required>
                            <option value="">— choose —</option>
                            @foreach ($trackingStatuses as $ts)
                                <option value="{{ $ts->id }}" data-notify="{{ $ts->notify_customer ? 1 : 0 }}" data-code="{{ $ts->code }}">{{ $ts->label }}</option>
                            @endforeach
                        </select>
                        <div id="courierFields" class="row g-2 mb-2" style="display:none">
                            <div class="col-6"><input name="courier_name" class="form-control form-control-sm" placeholder="Courier (DHL, FedEx…)" value="{{ $e->courier_name }}"></div>
                            <div class="col-6"><input name="courier_tracking_no" class="form-control form-control-sm" placeholder="AWB / tracking no."></div>
                        </div>
                        <textarea name="remarks" class="form-control form-control-sm mb-2" rows="2" placeholder="Remarks (optional) — e.g. sample condition, photos sent"></textarea>
                        <div class="form-check small">
                            <input type="hidden" name="notify" value="0">
                            <input class="form-check-input" type="checkbox" name="notify" value="1" id="notify" checked>
                            <label class="form-check-label" for="notify">Email this update to the customer</label>
                        </div>
                        <div class="form-check small mb-3">
                            <input type="hidden" name="internal_only" value="0">
                            <input class="form-check-input" type="checkbox" name="internal_only" value="1" id="internalOnly">
                            <label class="form-check-label" for="internalOnly">Internal note only (hidden from customer)</label>
                        </div>
                        <button class="btn btn-green w-100">Update status</button>
                    </form>
                @elseif ($e->isDeclined())
                    <p class="text-muted small mb-0">Quotation declined — process ended.</p>
                @else
                    <p class="text-muted small mb-0">Tracking updates become available after the customer approves the quotation.</p>
                @endif
                <hr>
                @include('partials.timeline', ['updates' => $e->trackingUpdates, 'staffView' => true])
            </div>
        </div>

        {{-- CUSTOMER DETAILS --}}
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between">
                <span>Customer details</span>
                @if ($e->customer)<a class="small" href="{{ route('admin.customers.edit', $e->customer) }}">Portal login ↗</a>@endif
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.enquiries.details', $e) }}">
                    @csrf @method('PUT')
                    <div class="mb-2">
                        <label class="form-label small">Customer account (discount &amp; terms)</label>
                        <select name="company_id" class="form-select form-select-sm">
                            <option value="">— not linked (standard price) —</option>
                            @foreach ($companies as $c)
                                <option value="{{ $c->id }}" @selected($e->company_id == $c->id)>{{ $c->name }} · {{ rtrim(rtrim(number_format($c->discount_percent, 2), '0'), '.') }}% · {{ $c->payment_terms }}</option>
                            @endforeach
                        </select>
                    </div>
                    @foreach (['company_name' => 'Company', 'contact_person' => 'Contact', 'email' => 'Email', 'phone' => 'Telephone', 'mobile' => 'Mobile', 'fax' => 'Fax', 'country' => 'Country'] as $f => $label)
                        <div class="mb-2"><label class="form-label small mb-0">{{ $label }}</label><input name="{{ $f }}" value="{{ $e->$f }}" class="form-control form-control-sm"></div>
                    @endforeach
                    <div class="mb-2"><label class="form-label small mb-0">Address</label><textarea name="address" rows="2" class="form-control form-control-sm">{{ $e->address }}</textarea></div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label small mb-0">Currency</label>
                            <select name="currency" class="form-select form-select-sm" @disabled($locked)>
                                @foreach (['SGD', 'USD'] as $c)<option @selected($e->currency === $c)>{{ $c }}</option>@endforeach
                            </select>
                            @if ($locked)<input type="hidden" name="currency" value="{{ $e->currency }}">@endif
                        </div>
                        <div class="col-6">
                            <label class="form-label small mb-0">Turnaround</label>
                            <div class="form-control form-control-sm bg-light">{{ $e->turnaroundLabel() }}</div>
                            <div class="form-text">Set per sample in the pricing table.</div>
                        </div>
                    </div>
                    <div class="mb-2"><label class="form-label small mb-0">Special instructions</label><textarea name="special_instructions" rows="2" class="form-control form-control-sm">{{ $e->special_instructions }}</textarea></div>
                    <details class="mb-2 small">
                        <summary>Reporting / invoice address</summary>
                        @foreach (['report_name' => 'Report to - name', 'report_company' => 'Report company', 'report_address' => 'Report address', 'invoice_name' => 'Invoice to - name', 'invoice_company' => 'Invoice company', 'invoice_address' => 'Invoice address'] as $f => $label)
                            <label class="form-label small mb-0 mt-1">{{ $label }}</label>
                            <input name="{{ $f }}" value="{{ $e->$f }}" class="form-control form-control-sm">
                        @endforeach
                    </details>
                    <div class="mb-2"><label class="form-label small mb-0">Internal notes (staff only)</label><textarea name="internal_notes" rows="2" class="form-control form-control-sm">{{ $e->internal_notes }}</textarea></div>
                    @unless ($e->isDeclined())
                        <button class="btn btn-sm btn-outline-secondary w-100">Save details</button>
                    @endunless
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    // Live recalculation of the pricing table
    const sym = @json($e->currencySymbol());
    const surPct = @json((float) \App\Models\Setting::get('urgent_surcharge_percent'));
    const money = n => sym + n.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    function recalc() {
        let sub = 0, urgentSub = 0;
        document.querySelectorAll('#pricingForm tbody tr').forEach(tr => {
            const p = tr.querySelector('.js-price'), q = tr.querySelector('.js-qty');
            const ta = document.querySelector(`.js-ta[data-sample="${tr.dataset.sample}"]`);
            if (!p) return;
            const amt = (parseFloat(p.value) || 0) * Math.max(1, parseInt(q.value) || 1);
            tr.querySelector('.js-amount').textContent = amt.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            tr.classList.toggle('amended', Math.abs((parseFloat(p.value) || 0) - parseFloat(p.dataset.list)) > 0.001);
            const isUrgent = ta && ta.value === 'urgent';
            sub += amt;
            if (isUrgent) urgentSub += amt;
        });
        const el = id => document.getElementById(id);
        if (!el('tSub')) return;
        el('tSub').textContent = money(sub);
        const s = urgentSub * surPct / 100;
        const d = (sub - urgentSub) * (parseFloat(el('discountPct').value) || 0) / 100;
        el('rSur').style.display = s > 0 ? '' : 'none';
        el('tSur').textContent = '+' + money(s);
        el('tDisc').textContent = '-' + money(d);
        el('tTotal').textContent = money(sub + s - d);
        document.querySelectorAll('.js-ta').forEach(t => ['border-danger', 'text-danger', 'fw-semibold'].forEach(c => t.classList.toggle(c, t.value === 'urgent')));
    }
    document.querySelectorAll('.js-price, .js-qty, #discountPct').forEach(i => i.addEventListener('input', recalc));
    document.querySelectorAll('.js-ta').forEach(i => i.addEventListener('change', recalc));

    // Add-test picker
    const TESTS = @json($testsJson);
    const cur = @json($e->currency);
    const search = document.getElementById('addSearch'), sel = document.getElementById('addTest');
    function fill() {
        if (!sel) return;
        const q = (search.value || '').toLowerCase();
        const list = TESTS.filter(t => !q || (t.n + ' ' + t.mm + ' ' + t.c).toLowerCase().includes(q));
        sel.innerHTML = '<option value="">— or type a custom test on the right —</option>' + list.map(t =>
            `<option value="${t.id}">${t.n.replace(/</g, '&lt;')} — ${t.mm.replace(/</g, '&lt;')} (${(cur === 'USD' ? t.usd : t.sgd).toFixed(2)})</option>`).join('');
    }
    if (search) { search.addEventListener('input', fill); fill(); }

    // Tracking form helpers
    const ts = document.getElementById('trackSel');
    if (ts) ts.addEventListener('change', () => {
        const o = ts.selectedOptions[0];
        document.getElementById('notify').checked = o && o.dataset.notify === '1';
        const code = (o && o.dataset.code) || '';
        document.getElementById('courierFields').style.display = /courier|post|sent|subcontract/.test(code) ? '' : 'none';
    });
    const internal = document.getElementById('internalOnly');
    if (internal) internal.addEventListener('change', () => { if (internal.checked) document.getElementById('notify').checked = false; });

    // Response form
    const rs = document.getElementById('respSel');
    if (rs) rs.addEventListener('change', () => {
        document.querySelectorAll('.resp-approve').forEach(x => x.style.display = rs.value === 'approve' ? '' : 'none');
        document.querySelectorAll('.resp-decline').forEach(x => x.style.display = rs.value === 'decline' ? '' : 'none');
    });
})();
</script>
@endpush
