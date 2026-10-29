@extends('layouts.public')
@section('title', 'New Price Request')
@section('content')
@php $p = $prefill; @endphp
<div class="mb-4">
    <div class="section-title">New price request</div>
    <h2 class="text-navy mb-1">Request a quotation</h2>
    <p class="text-muted mb-0">Fill in your details, add your samples and choose the tests you need. Our team will review your request and email you a formal quotation.</p>
</div>

<form method="POST" action="{{ route('enquiry.store') }}" id="enquiryForm" novalidate>
    @csrf
    {{-- spam trap --}}
    <input type="text" name="website" value="" style="position:absolute;left:-5000px" tabindex="-1" autocomplete="off">

    <div class="row g-4">
        <div class="col-lg-8">
            {{-- STEP 1 --}}
            <div class="card mb-4">
                <div class="card-header"><span class="badge bg-navy me-2">1</span>Customer details</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label required">Company name</label>
                            <input name="company_name" class="form-control" value="{{ old('company_name', $p['company_name'] ?? '') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required">Contact person</label>
                            <input name="contact_person" class="form-control" value="{{ old('contact_person', $p['contact_person'] ?? '') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $p['email'] ?? '') }}" required>
                            <div class="form-text">Confirmation, quotation and results are sent here.</div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Telephone</label>
                            <input name="phone" class="form-control" value="{{ old('phone', $p['phone'] ?? '') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Mobile</label>
                            <input name="mobile" class="form-control" value="{{ old('mobile') }}">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label required">Address</label>
                            <textarea name="address" class="form-control" rows="2" required>{{ old('address', $p['address'] ?? '') }}</textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label required">Country</label>
                            <input name="country" class="form-control" value="{{ old('country', $p['country'] ?? '') }}" required>
                            <label class="form-label mt-2">Fax</label>
                            <input name="fax" class="form-control" value="{{ old('fax') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required">Currency</label>
                            @php $cur = old('currency', $p['currency'] ?? 'SGD'); @endphp
                            <div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="currency" id="curSGD" value="SGD" {{ $cur === 'SGD' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="curSGD">SGD</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="currency" id="curUSD" value="USD" {{ $cur === 'USD' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="curUSD">USD</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Turnaround</label>
                            <div class="small text-muted">Choose <strong>Standard</strong> (7–9 working days) or <strong>Urgent</strong> (express service, surcharge applies) for <strong>each sample</strong> in step 2.</div>
                        </div>
                    </div>

                    <details class="mt-3" @if (old('report_address') || old('invoice_address')) open @endif>
                        <summary class="text-navy fw-semibold">Reporting / invoice address (only if different from above)</summary>
                        <div class="row g-3 mt-1">
                            <div class="col-md-6">
                                <div class="small fw-bold text-muted mb-1">Reporting address</div>
                                <input name="report_name" class="form-control form-control-sm mb-2" placeholder="Name" value="{{ old('report_name') }}">
                                <input name="report_company" class="form-control form-control-sm mb-2" placeholder="Company" value="{{ old('report_company') }}">
                                <textarea name="report_address" class="form-control form-control-sm" rows="2" placeholder="Address">{{ old('report_address') }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <div class="small fw-bold text-muted mb-1">Invoice address</div>
                                <input name="invoice_name" class="form-control form-control-sm mb-2" placeholder="Name" value="{{ old('invoice_name') }}">
                                <input name="invoice_company" class="form-control form-control-sm mb-2" placeholder="Company" value="{{ old('invoice_company') }}">
                                <textarea name="invoice_address" class="form-control form-control-sm" rows="2" placeholder="Address">{{ old('invoice_address') }}</textarea>
                            </div>
                        </div>
                    </details>
                </div>
            </div>

            {{-- STEP 2 --}}
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><span class="badge bg-navy me-2">2</span>Samples &amp; tests</span>
                    <button type="button" class="btn btn-sm btn-green" id="addSample">+ Add sample</button>
                </div>
                <div class="card-body" id="samples"></div>
            </div>

            {{-- STEP 3 --}}
            <div class="card mb-4">
                <div class="card-header"><span class="badge bg-navy me-2">3</span>Special instructions &amp; submit</div>
                <div class="card-body">
                    <label class="form-label">Special instructions / notes</label>
                    <textarea name="special_instructions" class="form-control mb-3" rows="3" placeholder="e.g. send pictures of samples on receipt, separate reports per sample…">{{ old('special_instructions') }}</textarea>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="declaration" value="1" id="declaration" {{ old('declaration') ? 'checked' : '' }}>
                        <label class="form-check-label small" for="declaration">
                            <strong>Declaration:</strong> I confirm the information provided is accurate and I am authorised to request this quotation on behalf of the company above.
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card summary-sticky">
                <div class="card-header">Summary</div>
                <div class="card-body">
                    <div class="d-flex justify-content-between"><span>Sample lines</span><strong id="sumSamples">0</strong></div>
                    <div class="d-flex justify-content-between"><span>Tests selected</span><strong id="sumTests">0</strong></div>
                    <div class="d-flex justify-content-between"><span>Urgent samples</span><strong id="sumUrgent">0</strong></div>
                    <div class="small text-danger mt-2" id="urgentNote" style="display:none">Urgent service requested — a surcharge applies to the urgent samples and will be shown on your quotation.</div>
                    <hr>
                    <p class="small text-muted mb-3">Pricing is not shown here. Pacific Lab will prepare your quotation and email it to you for approval.</p>
                    <button class="btn btn-green w-100 btn-lg" type="submit">Submit enquiry</button>
                </div>
            </div>
        </div>
    </div>
</form>

<template id="sampleTpl">
    <div class="card sample-card mb-3 shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <strong class="text-navy">Sample <span class="sample-no"></span></strong>
                <button type="button" class="btn btn-sm btn-outline-danger remove-sample">Remove</button>
            </div>
            <div class="row g-2">
                <div class="col-md-6">
                    <label class="form-label required">Client sample ID / description</label>
                    <input class="form-control f-description" required placeholder="e.g. Broiler Feed – Batch 26070302B">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Sample type</label>
                    <select class="form-select f-sample_type">
                        <option value="">—</option>
                        @foreach (['Feed', 'Premix', 'Feed Additive', 'Pure Material', 'Raw Material', 'Food', 'Water', 'Oil / Fat', 'Milk / Dairy', 'Other'] as $t)
                            <option>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label required">No. of samples</label>
                    <input type="number" min="1" value="1" class="form-control f-quantity">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Batch no.</label>
                    <input class="form-control f-batch_no">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Production date</label>
                    <input type="date" class="form-control f-production_date">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Storage</label>
                    <select class="form-select f-storage">
                        <option value="">—</option>
                        @foreach (config('paclab.storage_options') as $o)
                            <option>{{ $o }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mt-3 p-2 ta-box rounded border">
                <label class="form-label mb-1 required">Turnaround for this sample</label>
                <div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input f-ta-standard" type="radio" value="standard" checked>
                        <label class="form-check-label">Standard (7–9 working days)</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input f-ta-urgent" type="radio" value="urgent">
                        <label class="form-check-label">Urgent (express service, surcharge applies)</label>
                    </div>
                </div>
            </div>

            <div class="mt-3 p-2 test-row">
                <label class="form-label mb-1">Add test</label>
                <div class="row g-2">
                    <div class="col-md-4">
                        <select class="form-select form-select-sm pick-category"><option value="">All categories</option></select>
                    </div>
                    <div class="col-md-8">
                        <input class="form-control form-control-sm pick-search" placeholder="Search test, e.g. Vitamin A, Aflatoxin, Protein…">
                    </div>
                    <div class="col-md-10">
                        <select class="form-select form-select-sm pick-test"></select>
                    </div>
                    <div class="col-md-2 d-grid">
                        <button type="button" class="btn btn-sm btn-navy pick-add">Add</button>
                    </div>
                </div>
                <table class="table table-sm table-light-head mt-2 mb-0 tests-table">
                    <thead><tr><th>Test</th><th>Matrix / Method</th><th></th></tr></thead>
                    <tbody></tbody>
                </table>
                <div class="small text-danger no-tests mt-1">No tests selected yet.</div>
            </div>
        </div>
    </div>
</template>
@endsection

@push('scripts')
<script>
(function () {
    const TESTS = @json($testsJson);
    const OLD = @json(old('samples', []));
    const byId = Object.fromEntries(TESTS.map(t => [t.id, t]));
    const categories = [...new Set(TESTS.map(t => t.c))];
    const wrap = document.getElementById('samples');
    const tpl = document.getElementById('sampleTpl');
    let counter = 0;

    const currency = () => document.querySelector('input[name=currency]:checked')?.value || 'SGD';
    const esc = s => String(s ?? '').replace(/[&<>"]/g, c => ({'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;'}[c]));

    function fillTests(card) {
        const cat = card.querySelector('.pick-category').value;
        const q = card.querySelector('.pick-search').value.trim().toLowerCase();
        const sel = card.querySelector('.pick-test');
        const list = TESTS.filter(t => (!cat || t.c === cat) && (!q || (t.n + ' ' + t.mm + ' ' + t.c).toLowerCase().includes(q)));
        sel.innerHTML = list.length
            ? list.map(t => `<option value="${t.id}">${esc(t.n)}${t.mm ? ' — ' + esc(t.mm) : ''}</option>`).join('')
            : '<option value="">No matching tests</option>';
    }

    function addTest(card, id) {
        const t = byId[id];
        if (!t) return;
        const idx = card.dataset.idx;
        const tbody = card.querySelector('.tests-table tbody');
        if (tbody.querySelector(`tr[data-test="${id}"]`)) return; // no duplicates
        const k = card.dataset.tcount = (parseInt(card.dataset.tcount || '0') + 1);
        const tr = document.createElement('tr');
        tr.dataset.test = id;
        tr.innerHTML = `<td>${esc(t.n)}<input type="hidden" name="samples[${idx}][tests][${k}][lab_test_id]" value="${id}"></td>
            <td class="small text-muted">${esc(t.mm)}</td>
            <td class="text-end"><button type="button" class="btn btn-sm btn-link text-danger p-0 remove-test">✕</button></td>`;
        tbody.appendChild(tr);
        tr.querySelector('.remove-test').onclick = () => { tr.remove(); refresh(); };
        refresh();
    }

    function addSample(data = {}) {
        const idx = counter++;
        const node = tpl.content.firstElementChild.cloneNode(true);
        node.dataset.idx = idx;
        ['description', 'sample_type', 'quantity', 'batch_no', 'production_date', 'storage'].forEach(f => {
            const el = node.querySelector('.f-' + f);
            el.name = `samples[${idx}][${f}]`;
            if (data[f] !== undefined && data[f] !== null) el.value = data[f];
        });
        // turnaround radios: unique name + ids per sample
        const rStd = node.querySelector('.f-ta-standard'), rUrg = node.querySelector('.f-ta-urgent');
        [rStd, rUrg].forEach(r => {
            r.name = `samples[${idx}][turnaround]`;
            r.id = `ta${idx}_${r.value}`;
            r.nextElementSibling.htmlFor = r.id;
            r.onchange = refresh;
        });
        if (data.turnaround === 'urgent') rUrg.checked = true;
        const catSel = node.querySelector('.pick-category');
        categories.forEach(c => catSel.insertAdjacentHTML('beforeend', `<option>${esc(c)}</option>`));
        catSel.onchange = () => fillTests(node);
        node.querySelector('.pick-search').oninput = () => fillTests(node);
        node.querySelector('.pick-add').onclick = () => addTest(node, node.querySelector('.pick-test').value);
        node.querySelector('.f-quantity').oninput = refresh;
        node.querySelector('.remove-sample').onclick = () => {
            if (wrap.children.length > 1 || confirm('Remove the only sample?')) { node.remove(); refresh(); }
        };
        wrap.appendChild(node);
        fillTests(node);
        Object.values(data.tests || {}).forEach(t => addTest(node, t.lab_test_id));
        refresh();
    }

    function refresh() {
        let tests = 0;
        [...wrap.children].forEach((card, i) => {
            card.querySelector('.sample-no').textContent = i + 1;
            const rows = card.querySelectorAll('.tests-table tbody tr');
            card.querySelector('.no-tests').style.display = rows.length ? 'none' : '';
            tests += rows.length;
        });
        document.getElementById('sumSamples').textContent = wrap.children.length;
        document.getElementById('sumTests').textContent = tests;
        let urgent = 0;
        [...wrap.children].forEach(card => {
            const u = card.querySelector('.f-ta-urgent').checked;
            if (u) urgent++;
            card.querySelector('.ta-box').classList.toggle('ta-urgent', u);
        });
        document.getElementById('sumUrgent').textContent = urgent;
        document.getElementById('urgentNote').style.display = urgent ? '' : 'none';
    }

    document.getElementById('addSample').onclick = () => addSample();
    document.querySelectorAll('input[name=currency]').forEach(r => r.onchange = () => {
        [...wrap.children].forEach(fillTests);
        refresh();
    });

    document.getElementById('enquiryForm').addEventListener('submit', e => {
        const cards = [...wrap.children];
        const missing = cards.find(c => !c.querySelector('.tests-table tbody tr') || !c.querySelector('.f-description').value.trim());
        if (!cards.length || missing) {
            e.preventDefault();
            alert('Please describe each sample and add at least one test to it.');
            (missing || wrap).scrollIntoView({behavior: 'smooth', block: 'center'});
        } else if (!document.getElementById('declaration').checked) {
            e.preventDefault();
            alert('Please tick the declaration before submitting.');
        }
    });

    const oldSamples = Object.values(OLD || {});
    oldSamples.length ? oldSamples.forEach(s => addSample(s)) : addSample();
})();
</script>
@endpush
