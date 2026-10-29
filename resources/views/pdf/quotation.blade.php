<!doctype html>
<html><head><meta charset="utf-8">@include('pdf._style')</head>
<body>
@php
    $pct = fn ($v) => rtrim(rtrim(number_format((float) $v, 2), '0'), '.');
@endphp
<table>
    <tr>
        <td style="width:55%">@if ($logo)<img src="{{ $logo }}" style="height:62px">@endif</td>
        <td class="right">
            <div style="font-size:22px;font-weight:bold;" class="navy">QUOTATION</div>
            <table style="width:auto;margin-left:auto;margin-top:6px;">
                <tr><td class="muted right" style="padding:1px 8px;">Reference</td><td class="bold navy right">{{ $e->quotation_number ?: 'DRAFT – '.$e->reference }}</td></tr>
                <tr><td class="muted right" style="padding:1px 8px;">Date</td><td class="bold navy right">{{ ($e->quotation_date ?? now())->format('d-M-y') }}</td></tr>
                <tr><td class="muted right" style="padding:1px 8px;">Valid Until</td><td class="bold navy right">{{ ($e->quotation_valid_until ?? now()->addDays((int) \App\Models\Setting::get('quotation_valid_days', 90)))->format('d F Y') }}</td></tr>
                <tr><td class="muted right" style="padding:1px 8px;">Enquiry</td><td class="navy right">{{ $e->reference }}</td></tr>
            </table>
        </td>
    </tr>
</table>
<div style="border-top:3px solid #5bb031;margin:12px 0 14px;"></div>

<table>
    <tr>
        <td style="width:58%;padding-right:20px;">
            <div class="label">Quotation for</div>
            <div style="background:#f2f4f7;padding:6px 8px;margin-top:3px;line-height:1.55;">
                <div class="bold navy">{{ $e->company_name }}</div>
                Attn: {{ $e->contact_person }}<br>
                {!! nl2br(e($e->address)) !!}<br>
                {{ $e->country }}<br>
                {{ $e->email }}{{ $e->phone ? ' · '.$e->phone : '' }}
            </div>
        </td>
        <td>
            <div class="label">Issued by</div>
            <div style="padding:6px 0;line-height:1.55;">
                <div class="bold navy">{{ $co['name'] }}</div>
                {!! nl2br(e($co['address'])) !!}<br>
                {{ $co['website'] }}
            </div>
        </td>
    </tr>
</table>

<table class="lines" style="margin-top:16px;">
    <thead>
        <tr>
            <th style="width:26px">S/N</th><th style="width:30%">Test Sample Description</th><th>Test Required</th>
            <th class="right" style="width:62px">Unit ({{ $e->currency }})</th><th class="center" style="width:30px">Qty</th><th class="right" style="width:78px">Amount ({{ $e->currency }})</th>
        </tr>
    </thead>
    <tbody>
    @foreach ($e->samples as $s)
        @foreach ($s->items as $item)
            <tr>
                <td>{{ $loop->first ? $loop->parent->iteration : '' }}</td>
                <td>@if ($loop->first){{ $s->description }}@if ($s->sample_type) ({{ $s->sample_type }})@endif<br>@if ($s->urgent)<b style="color:#d63939;">URGENT</b>@else<span class="muted">Standard (7–9 working days)</span>@endif @endif</td>
                <td>{{ $item->display_name }}</td>
                <td class="right">{{ number_format($item->unit_price, 2) }}</td>
                <td class="center">{{ $item->quantity }}</td>
                <td class="right bold navy">{{ number_format($item->amount, 2) }}</td>
            </tr>
        @endforeach
    @endforeach
    </tbody>
</table>

<table style="margin-top:10px;">
    <tr>
        <td style="width:55%"></td>
        <td>
            <table>
                <tr><td class="right muted" style="padding:3px 8px;">Subtotal</td><td class="right bold navy" style="width:90px;padding:3px 6px;">{{ $e->money($e->subtotal) }}</td></tr>
                @if ($e->surcharge_amount > 0)
                    <tr><td class="right muted" style="padding:3px 8px;">Urgent surcharge ({{ $pct($e->surcharge_percent) }}% on urgent samples)</td><td class="right bold navy" style="padding:3px 6px;">+{{ $e->money($e->surcharge_amount) }}</td></tr>
                @endif
                @if ($e->discount_amount > 0)
                    <tr><td class="right muted" style="padding:3px 8px;">Discount ({{ $pct($e->discount_percent) }}%{{ $e->surcharge_amount > 0 ? ' on standard samples' : '' }})</td><td class="right bold navy" style="padding:3px 6px;">-{{ $e->money($e->discount_amount) }}</td></tr>
                @endif
                <tr style="background:#17365d;color:#fff;"><td class="right bold" style="padding:6px 8px;font-size:11px;">AMOUNT DUE</td><td class="right bold" style="padding:6px 6px;font-size:11px;">{{ $e->money($e->total) }}</td></tr>
            </table>
        </td>
    </tr>
</table>

<div class="label" style="margin-top:18px;">Notes</div>
<ul style="margin:4px 0 0 14px;padding:0;line-height:1.6;">
    @if ($e->payment_terms)<li>Payment terms: {{ $e->payment_terms }}.</li>@endif
    @foreach (preg_split('/\r?\n/', trim($e->quotation_notes ?: \App\Models\Setting::get('quotation_notes'))) as $line)
        @if (trim($line) !== '')<li>{{ trim($line) }}</li>@endif
    @endforeach
    @if ($e->isUrgent())<li>Urgent service requested for samples marked URGENT — surcharge of {{ $pct($e->surcharge_percent) }}% applied to those samples; discounts do not apply to urgent samples.</li>@endif
</ul>

<div class="footer">
    {{ $co['name'] }} · {{ str_replace("\n", ', ', $co['address']) }} · {{ $co['website'] }}<br>
    <i>This is a computer-generated document. No signature is required.</i>
</div>
</body></html>
