@php $S = \App\Models\Setting::class; @endphp
<!doctype html>
<html>
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width"></head>
<body style="margin:0;padding:0;background:#f4f6f9;font-family:Segoe UI,Arial,Helvetica,sans-serif;color:#1f2933;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6f9;padding:24px 0;">
    <tr><td align="center">
        <table width="620" cellpadding="0" cellspacing="0" style="max-width:620px;width:100%;background:#ffffff;border-radius:10px;overflow:hidden;">
            <tr><td style="padding:20px 28px;border-bottom:4px solid #5bb031;">
                <img src="{{ asset('images/logo.png') }}" alt="Pacific Lab" height="44" style="display:block;height:44px;">
            </td></tr>
            <tr><td style="padding:28px;font-size:15px;line-height:1.6;">
                @yield('body')
            </td></tr>
            <tr><td style="padding:18px 28px;background:#17365d;color:#cfd9e6;font-size:12px;line-height:1.5;">
                <strong style="color:#fff;">{{ $S::get('company_name') }}</strong> ({{ $S::get('trading_name') }})<br>
                {{ str_replace("\n", ', ', $S::get('address')) }}<br>
                Tel {{ $S::get('phone') }} · {{ $S::get('email') }} · {{ $S::get('website') }}
            </td></tr>
        </table>
        <div style="font-size:11px;color:#8a96a3;margin-top:10px;">This is an automated message from the Pacific Lab Services customer portal.</div>
    </td></tr>
</table>
</body>
</html>
