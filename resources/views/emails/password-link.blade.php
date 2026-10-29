@extends('emails.layout')
@section('body')
<p>Dear {{ $user->name }},</p>
@if ($newAccount)
    <p>A customer portal account has been set up for you at Pacific Lab Services. Please choose your password using the button below.</p>
@else
    <p>We received a request to reset the password for your Pacific Lab portal account.</p>
@endif
@include('emails._button', ['url' => route('password.reset', ['token' => $token, 'email' => $user->email]), 'label' => $newAccount ? 'Set my password' : 'Reset my password'])
<p style="font-size:13px;color:#6b7785;">This link is valid for 72 hours. If you did not request it, you can ignore this email.</p>
<p>Kind regards,<br><strong>Pacific Lab Services</strong></p>
@endsection
