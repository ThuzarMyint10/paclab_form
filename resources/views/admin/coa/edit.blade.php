@extends('layouts.admin')
@section('title', 'COA '.$coa->report_number)
@section('content')
<a href="{{ route('admin.enquiries.show', $enquiry) }}" class="small">← Back to {{ $enquiry->reference }}</a>
<div class="d-flex justify-content-between align-items-center mt-1 mb-3">
    <h3 class="text-navy mb-0">Certificate of Analysis · {{ $coa->report_number }}
        <span class="badge text-bg-{{ $coa->isReleased() ? 'success' : 'secondary' }}">{{ $coa->isReleased() ? 'Released '.$coa->released_at->format('d M Y') : 'Draft' }}</span>
    </h3>
    <a href="{{ route('admin.enquiries.document', [$enquiry, 'coa']) }}" target="_blank" class="btn btn-sm btn-outline-secondary">Preview PDF</a>
</div>

<form method="POST" action="{{ route('admin.coa.update', $enquiry) }}">
    @csrf @method('PUT')
    <div class="card mb-4">
        <div class="card-body row g-3">
            <div class="col-md-3"><label class="form-label">Report no.</label><input name="report_number" value="{{ old('report_number', $coa->report_number) }}" class="form-control" required></div>
            <div class="col-md-3"><label class="form-label">Report date</label><input type="date" name="report_date" value="{{ old('report_date', $coa->report_date->format('Y-m-d')) }}" class="form-control" required></div>
            <div class="col-md-3"><label class="form-label">Samples received date</label><input type="date" name="samples_received_date" value="{{ old('samples_received_date', optional($coa->samples_received_date)->format('Y-m-d')) }}" class="form-control"></div>
            <div class="col-md-3"><label class="form-label">Contact</label><input name="contact_name" value="{{ old('contact_name', $coa->contact_name) }}" class="form-control"></div>
            <div class="col-md-3"><label class="form-label">Signatory name</label><input name="signatory_name" value="{{ old('signatory_name', $coa->signatory_name) }}" class="form-control"></div>
            <div class="col-md-3"><label class="form-label">Signatory title</label><input name="signatory_title" value="{{ old('signatory_title', $coa->signatory_title) }}" class="form-control"></div>
            <div class="col-md-6"><label class="form-label">Remarks (printed on report)</label><input name="remarks" value="{{ old('remarks', $coa->remarks) }}" class="form-control"></div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">Results</div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>Lab code / Sample</th><th>Parameter</th><th>Method (as printed)</th><th style="width:110px">Unit</th><th style="width:150px">Result</th></tr></thead>
                <tbody>
                @foreach ($enquiry->samples as $s)
                    @foreach ($s->items as $item)
                        <tr>
                            <td class="small">@if ($loop->first)<strong>{{ $s->lab_code ?: '—' }}</strong><div class="text-muted">{{ $s->description }}</div>@endif</td>
                            <td>{{ $item->test_name }}</td>
                            <td><input name="results[{{ $item->id }}][result_method]" value="{{ $item->result_method ?? ($item->method ? 'Pacific Lab Method, '.$item->method : '') }}" class="form-control form-control-sm"></td>
                            <td><input name="results[{{ $item->id }}][result_unit]" value="{{ $item->result_unit }}" class="form-control form-control-sm" list="units"></td>
                            <td><input name="results[{{ $item->id }}][result_value]" value="{{ $item->result_value }}" class="form-control form-control-sm"></td>
                        </tr>
                    @endforeach
                @endforeach
                </tbody>
            </table>
            <datalist id="units"><option>%</option><option>mg/kg</option><option>µg/kg</option><option>IU/kg</option><option>IU/g</option><option>FTU/g</option><option>U/g</option><option>ppm</option><option>ppb</option><option>cfu/g</option><option>g/100g</option><option>kcal/100g</option><option>Not Detected</option></datalist>
        </div>
    </div>

    <div class="d-flex gap-2">
        <button class="btn btn-outline-secondary" name="action" value="save">Save results</button>
        <button class="btn btn-green" name="action" value="release" onclick="return confirm('Release the COA and email it to {{ $enquiry->email }}?')">
            {{ $coa->isReleased() ? 'Save & re-send COA' : 'Save & Release COA to customer' }}
        </button>
    </div>
</form>
@endsection
