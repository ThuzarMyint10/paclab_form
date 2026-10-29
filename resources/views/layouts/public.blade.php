<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Laboratory Services') | Pacific Lab Services</title>
    <link rel="icon" href="{{ asset('images/logo.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    @stack('head')
</head>
<body>
<header class="site-header">
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}"><img src="{{ asset('images/logo.png') }}" alt="Pacific Lab" class="logo"></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav"><span class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="nav">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                    <li class="nav-item"><a class="nav-link" href="{{ route('enquiry.create') }}">New Enquiry</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('track') }}">Track Sample</a></li>
                    @auth
                        @if (auth()->user()->isStaff())
                            <li class="nav-item"><a class="btn btn-navy btn-sm" href="{{ route('admin.dashboard') }}">Admin Panel</a></li>
                        @else
                            <li class="nav-item"><a class="nav-link" href="{{ route('portal.dashboard') }}">My Jobs</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('portal.profile') }}">My Account</a></li>
                        @endif
                        <li class="nav-item">
                            <form method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-outline-secondary btn-sm">Log out</button></form>
                        </li>
                    @else
                        <li class="nav-item"><a class="btn btn-green btn-sm px-3" href="{{ route('login') }}">Customer Login</a></li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>
</header>

<main class="container py-4">
    @include('partials.flash')
    @yield('content')
</main>

<footer class="border-top bg-white py-4 mt-5">
    <div class="container small text-muted d-flex flex-wrap justify-content-between gap-2">
        <div>
            <strong class="text-navy">{{ \App\Models\Setting::get('company_name') }}</strong> ({{ \App\Models\Setting::get('trading_name') }}) ·
            {{ str_replace("\n", ', ', \App\Models\Setting::get('address')) }}
        </div>
        <div>Tel {{ \App\Models\Setting::get('phone') }} · {{ \App\Models\Setting::get('email') }} · <a href="{{ route('admin.login') }}" class="text-muted">Staff login</a></div>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
