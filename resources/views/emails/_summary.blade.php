{{-- compact list of samples & tests --}}
<table width="100%" cellpadding="6" cellspacing="0" style="border-collapse:collapse;font-size:13px;margin:12px 0;">
    <tr style="background:#17365d;color:#fff;"><th align="left">Sample</th><th align="left">Test</th>@if (! empty($withPrice))<th align="right">Amount</th>@endif</tr>
    @foreach ($enquiry->samples as $s)
        @foreach ($s->items as $item)
            <tr style="border-bottom:1px solid #e3e8ef;">
                <td>@if ($loop->first){{ $s->description.' (×'.$s->quantity.')' }}@if ($s->urgent) <strong style="color:#d63939;">[URGENT]</strong>@endif @endif</td>
                <td>{{ $item->display_name }}</td>
                @if (! empty($withPrice))<td align="right">{{ number_format($item->amount, 2) }}</td>@endif
            </tr>
        @endforeach
    @endforeach
</table>
