<!doctype html>
<html><head><meta charset="utf-8">@include('pdf._style')
<style>
    @page { margin: 28px 34px 190px 34px; }
    * { font-family: "DejaVu Sans", sans-serif; }
    body { font-size: 10px; }
    .hdr td { padding: 1px 3px; }
    .res td { padding: 3px 4px; }
</style>
</head>
<body>
<table>
    <tr>
        <td style="width:62%">@if ($logo)<img src="{{ $logo }}" style="height:60px">@endif</td>
        <td class="right" style="font-size:8px;line-height:1.45;">
            {{ $co['name'] }}<br>({{ $co['trading'] }})<br>{!! nl2br(e($co['address'])) !!}<br>Tel: {{ $co['phone'] }}<br>Fax: {{ $co['fax'] }}<br>{{ $co['website'] }}
        </td>
    </tr>
</table>
<div style="border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:center;font-weight:bold;font-size:13px;padding:3px 0;margin-top:6px;">CERTIFICATE OF ANALYSIS</div>

@php $r = $e->reportingAddress(); @endphp
<table class="hdr" style="margin-top:6px;">
    <tr>
        <td style="width:11%;" class="bold right">Company:</td>
        <td style="width:44%;">{!! nl2br(e(strtoupper($r['company']."\n".$r['address']))) !!}</td>
        <td class="bold right" style="width:22%;">Report No:<br>Report Date:<br>Samples Received Date:</td>
        <td>{{ $coa->report_number }}<br>{{ strtoupper($coa->report_date->format('F d, Y')) }}<br>{{ $coa->samples_received_date ? strtoupper($coa->samples_received_date->format('F d, Y')) : '' }}</td>
    </tr>
</table>
<div style="border-top:1px solid #333;margin-top:6px;padding-top:3px;"><span class="bold">&nbsp;Contact:</span> &nbsp;{{ strtoupper($coa->contact_name ?: $r['name']) }}</div>
<div style="border-top:1px solid #333;margin-top:3px;"></div>

@foreach ($e->samples as $s)
    <div style="margin-top:8px;"><b>{{ $s->lab_code }}</b> {{ $s->description }}{{ $s->batch_no ? ' · Batch '.$s->batch_no : '' }}</div>
    <table class="res" style="margin-left:30px;width:94%;">
        @foreach ($s->items as $item)
            <tr>
                <td style="width:62%;"><b>{{ $item->test_name }}</b><br><i>Method: {{ $item->result_method ?: ($item->method ? 'Pacific Lab Method, '.$item->method : '—') }}</i></td>
                <td class="center" style="width:18%;">{{ $item->result_unit }}</td>
                <td class="right">{{ $item->result_value !== null && $item->result_value !== '' ? $item->result_value : 'Pending' }}</td>
            </tr>
        @endforeach
    </table>
@endforeach

@if ($coa->remarks)
    <div style="margin-top:12px;"><b>Remarks:</b> {!! nl2br(e($coa->remarks)) !!}</div>
@endif

<div style="position:fixed;bottom:-165px;left:0;right:0;">
    <table>
        <tr>
            <td style="width:40%;">
                <div style="height:34px;"></div>
                <div style="border-top:1px solid #333;width:170px;text-align:center;padding-top:2px;">{{ $coa->signatory_name }}<br>{{ $coa->signatory_title }}</div>
            </td>
            <td class="right" style="font-size:8px;color:#6b7785;">{{ $coa->isReleased() ? '' : 'DRAFT – NOT FOR RELEASE' }}</td>
        </tr>
    </table>
    <div style="font-style:italic;font-size:8px;line-height:1.45;margin-top:8px;">{!! nl2br(e(\App\Models\Setting::get('coa_disclaimer'))) !!}</div>
</div>
</body></html>
