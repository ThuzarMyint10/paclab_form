<!doctype html>
<html><head><meta charset="utf-8">@include('pdf._style')
<style>
    body { font-size: 10px; }
    .fld td { padding: 4px 2px; }
    .uline { border-bottom: 1px solid #333; }
    .grid th, .grid td { border: 1px solid #333; padding: 5px 6px; }
    .grid th { font-weight: normal; text-align: center; }
    .sec { font-weight: bold; text-decoration: underline; margin: 14px 0 6px; font-size: 10.5px; }
</style>
</head>
<body>
<table>
    <tr>
        <td style="width:60%">@if ($logo)<img src="{{ $logo }}" style="height:58px">@endif</td>
        <td class="right" style="font-size:8px;line-height:1.45;">
            <b>{{ $co['name'] }}</b><br>{!! nl2br(e($co['address'])) !!}<br>
            Phone: {{ $co['phone'] }}<br>Fax: {{ $co['fax'] }}<br>Email: {{ $co['email'] }}<br>{{ $co['website'] }}
        </td>
    </tr>
</table>
<table style="margin-top:8px;">
    <tr>
        <td style="width:30%"></td>
        <td class="center" style="font-size:13px;font-weight:bold;text-decoration:underline;">Sample Submission Form</td>
        <td class="right" style="width:30%;">
            <div style="font-size:12px;font-weight:bold;color:#17365d;">{{ $e->ssf_number }}</div>
            <div class="muted" style="font-size:8px;">Quotation {{ $e->quotation_number }} · {{ $e->reference }}</div>
        </td>
    </tr>
</table>

<table class="fld" style="margin-top:10px;">
    <tr><td style="width:14%">Company</td><td style="width:2%">:</td><td class="uline bold" colspan="4">{{ $e->company_name }}</td></tr>
    <tr><td>Address</td><td>:</td><td class="uline" colspan="4">{!! nl2br(e($e->address)) !!}{{ $e->country ? ', '.$e->country : '' }}</td></tr>
    <tr><td>Telephone</td><td>:</td><td class="uline" style="width:34%">{{ $e->phone }}</td><td style="width:10%;padding-left:14px;">E-mail</td><td style="width:2%">:</td><td class="uline">{{ $e->email }}</td></tr>
    <tr><td>Mobile</td><td>:</td><td class="uline">{{ $e->mobile }}</td><td style="padding-left:14px;">Fax</td><td>:</td><td class="uline">{{ $e->fax }}</td></tr>
    <tr><td>Contact</td><td>:</td><td class="uline">{{ $e->contact_person }}</td><td style="padding-left:14px;">PO No.</td><td>:</td><td class="uline">{{ $e->po_number }}</td></tr>
</table>

<div class="sec">Section I:</div>
<table class="grid">
    <thead>
        <tr><th style="width:38%">Client Sample ID/ Description</th><th style="width:13%">Total Number of Samples</th><th>Analyses Requested</th><th style="width:17%">Storage Requirements<br>(RT, Refrigerate, Freeze)</th></tr>
    </thead>
    <tbody>
    @foreach ($e->samples as $s)
        <tr>
            <td>
                {{ $s->description }}@if ($s->urgent) <b style="color:#d63939;">[URGENT]</b>@endif
                @if ($s->sample_type)<br><span class="muted">Type: {{ $s->sample_type }}</span>@endif
                @if ($s->production_date)<br>Production Date: {{ $s->production_date->format('d.m.Y') }}@endif
                @if ($s->batch_no)<br>Batch No.: {{ $s->batch_no }}@endif
            </td>
            <td class="center">{{ $s->quantity }}</td>
            <td>@foreach ($s->items as $item){{ $item->test_name }}@if ($item->method) <span class="muted">({{ $item->method }})</span>@endif<br>@endforeach</td>
            <td class="center">{{ $s->storage }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
<div style="font-style:italic;font-size:8.5px;margin-top:2px;">Note: Please attach addition sheet if required.</div>

<div class="sec" style="text-decoration:none;">Special Instructions:</div>
<div class="box" style="padding:8px;min-height:36px;">{!! nl2br(e($e->special_instructions)) ?: '&nbsp;' !!}@if ($e->isUrgent())<br><b>URGENT SERVICE REQUESTED – samples marked [URGENT]</b>@endif</div>

@php $r = $e->reportingAddress(); @endphp
@php $i = $e->invoiceAddress(); @endphp
<div class="sec" style="text-decoration:none;">Section II: Kindly fill in this section if different from above.</div>
<table class="grid">
    <tr><th style="width:14%"></th><th>Reporting Address</th><th>Invoice Address</th></tr>
    <tr><td class="bold">Name</td><td>{{ $r['name'] }}</td><td>{{ $i['name'] }}</td></tr>
    <tr><td class="bold">Company</td><td>{{ $r['company'] }}</td><td>{{ $i['company'] }}</td></tr>
    <tr><td class="bold">Address:</td><td style="height:44px;">{!! nl2br(e($r['address'])) !!}</td><td>{!! nl2br(e($i['address'])) !!}</td></tr>
</table>

<table style="margin-top:34px;">
    <tr>
        <td style="width:50%;">Submitted by:<br><br><br>
            <div style="border-top:1px solid #333;width:170px;padding-top:2px;text-align:center;">(Name / Signature)</div>
            <div style="margin-top:4px;">{{ $e->approved_by_name }}</div>
        </td>
        <td class="right" style="padding-top:38px;">Date Submitted: <span style="display:inline-block;border-bottom:1px solid #333;width:130px;">&nbsp;</span></td>
    </tr>
</table>

<div style="margin-top:22px;background:#f2f4f7;padding:7px 9px;font-size:8.5px;line-height:1.5;">
    <b>For office use:</b> Received date ________________ &nbsp; Courier / Post / Hand ________________ &nbsp; Received by ________________ &nbsp; Condition ________________
</div>
<div class="footer">(Generated {{ optional($e->ssf_generated_at)->format('d M Y') }} · {{ $co['trading'] }}) · Page 1 of 1</div>
</body></html>
