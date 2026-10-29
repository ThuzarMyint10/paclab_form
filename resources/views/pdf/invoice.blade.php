<!doctype html>
<html><head><meta charset="utf-8">@include('pdf._style')
<style>
    body { font-family: "DejaVu Serif", serif; font-size: 10px; }
    * { font-family: "DejaVu Serif", serif; }
    .meta td { padding: 1.5px 3px; }
    .inv th { border-top: 1.5px solid #333; border-bottom: 1.5px solid #333; padding: 5px 6px; text-align: left; font-size: 9.5px; }
    .inv td { padding: 4px 6px; }
    .vline { border-left: 1px solid #333; }
</style>
</head>
<body>
@php
    $pct = fn ($v) => rtrim(rtrim(number_format((float) $v, 2), '0'), '.');
@endphp
<table>
    <tr>
        <td style="width:60%">@if ($logo)<img src="{{ $logo }}" style="height:58px">@endif
            <div style="font-size:18px;font-weight:bold;margin-top:14px;letter-spacing:.5px;">TAX INVOICE</div>
        </td>
        <td class="right" style="font-size:8.5px;line-height:1.45;">
            <b>{{ $co['name'] }}</b><br>{!! nl2br(e($co['address'])) !!}<br>T: {{ $co['phone'] }}<br>F: {{ $co['fax'] }}<br>{{ $co['website'] }}
        </td>
    </tr>
</table>

<table style="margin-top:8px;">
    <tr>
        <td style="width:52%;line-height:1.45;">
            <b>SOLD TO:</b><br>
            {!! nl2br(e(strtoupper($inv->bill_to))) !!}
        </td>
        <td>
            <table class="meta">
                <tr><td style="width:44%">Co Registration No.</td><td style="width:4%">:</td><td>{{ $co['reg_no'] }}</td></tr>
                <tr><td>GST Registration No.</td><td>:</td><td>{{ $co['gst_no'] }}</td></tr>
                <tr><td>Invoice Number</td><td>:</td><td><b>{{ $inv->invoice_number }}</b></td></tr>
                <tr><td>Invoice Date</td><td>:</td><td>{{ $inv->invoice_date->format('d-M-Y') }}</td></tr>
                <tr><td>Payment Terms</td><td>:</td><td>{{ $inv->payment_terms }}</td></tr>
                <tr><td>Currency</td><td>:</td><td>{{ $inv->currency }}</td></tr>
                <tr><td>Your Ref</td><td>:</td><td>{{ $inv->po_number ? 'PO NO: '.$inv->po_number : '' }}</td></tr>
                <tr><td>Our Ref</td><td>:</td><td>{{ $inv->our_ref }}</td></tr>
            </table>
        </td>
    </tr>
</table>

<table class="inv" style="margin-top:14px;">
    <thead><tr><th>DESCRIPTION</th><th style="width:60px"></th><th class="right vline" style="width:70px;text-align:right;">RATE</th><th class="right vline" style="width:85px;text-align:right;">AMOUNT</th></tr></thead>
    <tbody>
    @foreach ($inv->items as $line)
        <tr>
            <td>{{ $line->description }}</td>
            <td class="right">{{ $line->quantity }} {{ $line->quantity > 1 ? 'Tests' : 'Test' }}</td>
            <td class="right vline">{{ number_format($line->rate, 2) }}</td>
            <td class="right vline">{{ number_format($line->amount, 2) }}</td>
        </tr>
    @endforeach
    @for ($pad = $inv->items->count(); $pad < 14; $pad++)
        <tr><td>&nbsp;</td><td></td><td class="vline"></td><td class="vline"></td></tr>
    @endfor
    </tbody>
</table>

<table style="margin-top:6px;border-top:1px solid #333;">
    <tr><td style="width:48%"></td><td class="bold" style="padding:4px;">TOTAL</td><td class="right bold" style="width:85px;padding:4px 6px;">{{ number_format($inv->total, 2) }}</td></tr>
    <tr><td></td><td class="bold" style="padding:4px;">LESS: DISCOUNT @ &nbsp;{{ $pct($inv->discount_percent) }}%</td><td class="right bold" style="padding:4px 6px;">{{ number_format($inv->discount_amount, 2) }}</td></tr>
    <tr><td></td><td class="bold" style="padding:4px;">SUB-TOTAL</td><td class="right bold" style="padding:4px 6px;">{{ number_format($inv->subtotal, 2) }}</td></tr>
    <tr><td></td><td class="bold" style="padding:4px;">ADD GST @ &nbsp;{{ $pct($inv->gst_percent) }}%</td><td class="right bold" style="padding:4px 6px;">{{ number_format($inv->gst_amount, 2) }}</td></tr>
    <tr><td></td><td class="bold" style="padding:4px;">GRAND TOTAL ({{ $inv->currency }})</td><td class="right bold" style="padding:4px 6px;border-top:1px solid #333;border-bottom:3px double #333;">{{ number_format($inv->grand_total, 2) }}</td></tr>
</table>

@if ($inv->notes)
    <div style="margin-top:10px;font-size:9px;"><b>Notes:</b> {!! nl2br(e($inv->notes)) !!}</div>
@endif

<table style="margin-top:22px;border-top:1px solid #9aa5b1;padding-top:6px;font-size:8.5px;">
    <tr>
        <td style="width:62%;line-height:1.5;padding-top:6px;">{!! nl2br(e($co['bank'])) !!}</td>
        <td class="right" style="padding-top:6px;">THIS IS COMPUTER GENERATED.<br>NO SIGNATURE REQUIRED.</td>
    </tr>
</table>
</body></html>
