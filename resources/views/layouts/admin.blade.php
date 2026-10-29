<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex">
    <title>@yield('title', 'Admin') | PacLab Admin</title>
    <link rel="icon" href="{{ asset('images/logo.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    @stack('head')
</head>
<body>
@php $u = auth()->user(); @endphp
<div class="d-flex admin-shell">
    <aside class="admin-sidebar">
        <div class="brand"><a href="{{ route('admin.dashboard') }}"><img src="{{ asset('images/logo.png') }}" alt="Pacific Lab"></a></div>
        <nav class="nav flex-column p-2">
            <div class="nav-section">Operations</div>
            <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">Dashboard</a>
            <a class="nav-link {{ request()->routeIs('admin.enquiries.*', 'admin.coa.*') ? 'active' : '' }}" href="{{ route('admin.enquiries.index') }}">Enquiries &amp; Jobs</a>
            <a class="nav-link {{ request()->routeIs('admin.invoices.*') ? 'active' : '' }}" href="{{ route('admin.invoices.index') }}">Invoices</a>
            <div class="nav-section">Master data</div>
            <a class="nav-link {{ request()->routeIs('admin.lab-tests.*') ? 'active' : '' }}" href="{{ route('admin.lab-tests.index') }}">Price List (Tests)</a>
            <a class="nav-link {{ request()->routeIs('admin.companies.*') ? 'active' : '' }}" href="{{ route('admin.companies.index') }}">Customer Companies</a>
            <a class="nav-link {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}" href="{{ route('admin.customers.index') }}">Customer Logins</a>
            @if ($u->isAdmin())
                <div class="nav-section">Administration</div>
                <a class="nav-link {{ request()->routeIs('admin.tracking-statuses.*') ? 'active' : '' }}" href="{{ route('admin.tracking-statuses.index') }}">Tracking Statuses</a>
                <a class="nav-link {{ request()->routeIs('admin.staff.*') ? 'active' : '' }}" href="{{ route('admin.staff.index') }}">Staff Logins</a>
                <a class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}" href="{{ route('admin.settings.edit') }}">Settings</a>
            @endif
        </nav>
    </aside>
    <div class="admin-main">
        <div class="admin-topbar px-4 py-2 d-flex align-items-center justify-content-between">
            <form action="{{ route('admin.enquiries.index') }}" class="d-none d-md-block" style="width:340px">
                <input name="q" class="form-control form-control-sm" placeholder="Search reference, quotation, company, email…" value="{{ request('q') }}">
            </form>
            <div class="d-flex align-items-center gap-3 small">
                <a href="{{ route('home') }}" target="_blank">View website ↗</a>
                <a href="{{ route('admin.account') }}">{{ $u->name }} <span class="text-muted">({{ \App\Models\User::ROLES[$u->role] ?? $u->role }})</span></a>
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-sm btn-outline-secondary">Log out</button></form>
            </div>
        </div>
        <div class="p-4">
            @include('partials.flash')
            @yield('content')
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
