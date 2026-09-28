<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ \Cache::rememberForever('st_app_fallback_text', fn() => \App\Setting::where('key', 'app_fallback_text')->first()->val ?? 'Falcon') }}</title>

    <!-- Scripts -->
    @stack('head')    
    <script src="{{ asset('js/app.js') }}"></script>    
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    
    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">    
    <link href="{{ asset('css/admin.css') }}" rel="stylesheet">
    <style>
    .py-4>.container{
        max-width:99% !important;
    }
    .admin-main {
        min-width: 0 !important;
        max-width: 100% !important;
        overflow: hidden !important; /* Force constraint */
    }
    main {
        min-width: 0 !important;
        max-width: 100% !important;
        overflow-x: hidden !important;
    }
    .table-responsive {
        width: 100% !important;
        max-width: 100% !important;
        display: block !important;
        overflow-x: auto !important;
        overflow-y: visible !important;
        scrollbar-width: thin; /* Firefox */
        scrollbar-color: #cbd5e1 #f1f1f1; /* Firefox */
    }
    .table-responsive table {
        white-space: nowrap;
    }
    .table-responsive table thead th {
        background-color: #f8f9fa; /* Matches bg-light */
        border-bottom: 2px solid #e2e8f0;
    }
    </style>
</head>
<body>
    <div id="app" class="admin-wrapper">
        
        <!-- Premium Topbar -->
        @auth
        <nav class="admin-topbar">
            <div class="topbar-left d-flex align-items-center h-100">
                <a class="topbar-brand" href="{{ url('/') }}">
                    @php
                        $appLogo = \Cache::rememberForever('st_app_logo', fn() => \App\Setting::where('key', 'app_logo')->first()->val ?? '');
                        $appFallback = \Cache::rememberForever('st_app_fallback_text', fn() => \App\Setting::where('key', 'app_fallback_text')->first()->val ?? 'Falcon');
                    @endphp
                    
                    @if($appLogo)
                        <img src="{{ asset($appLogo) }}" alt="{{ $appFallback }}" style="max-height: 40px; max-width: 200px;">
                    @else
                        {{ $appFallback }}
                    @endif
                </a>
            </div>
            <div class="topbar-right pr-4">
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a id="navbarDropdown" class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                            <i class="fas fa-user-circle mr-2" style="font-size: 1.2rem;"></i> {{ Auth::user()->name }}
                        </a>

                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item" href="{{ route('logout') }}"
                               onclick="event.preventDefault();
                                             document.getElementById('logout-form').submit();">
                                {{ __('Logout') }}
                            </a>

                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </div>
                    </li>
                </ul>
            </div>
        </nav>
        @endauth

        <!-- Sidebar Navigation -->
        @auth
        <aside class="admin-sidebar">
            <ul class="nav flex-column">
                @if(\Auth::user()->type == 'admin')
                
                <li class="nav-item @if(\Request::is('admin')) active @endif mt-2">
                    <a class="nav-link" href="{{ url('admin') }}"><i class="fas fa-th-large"></i> {{ __('Dashboard') }}</a>
                </li>

                <div class="nav-category">Attendance</div>
                <li class="nav-item @if(\Request::is('admin/employees/attendance')) active @endif">
                    <a class="nav-link" href="{{ url('admin/employees/attendance') }}"><i class="fas fa-clock"></i> {{ __('Employee Attendance') }}</a>
                </li>
                <li class="nav-item @if(\Request::is('admin/salesman/meeting-attendance')) active @endif">
                    <a class="nav-link" href="{{ url('admin/salesman/meeting-attendance') }}"><i class="fas fa-calendar-check"></i> {{ __('Salesman Attendance') }}</a>
                </li>
                <li class="nav-item @if(\Request::is('admin/reports/manual-entry')) active @endif">
                    <a class="nav-link" href="{{ url('admin/reports/manual-entry') }}"><i class="fas fa-keyboard"></i> {{ __('Manual Entry') }}</a>
                </li>

                <div class="nav-category">Management</div>
                <li class="nav-item @if(\Request::is('admin/employees')) active @endif">
                    <a class="nav-link" href="{{ url('admin/employees') }}"><i class="fas fa-users"></i> {{ __('Employees') }}</a>
                </li>
                <li class="nav-item @if(\Request::is('admin/users')) active @endif">
                    <a class="nav-link" href="{{ url('admin/users') }}"><i class="fas fa-user-shield"></i> {{ __('Users') }}</a>
                </li>

                <div class="nav-category">Reports</div>
                <li class="nav-item @if(\Request::is('admin/reports/working-hours')) active @endif">
                    <a class="nav-link" href="{{ url('admin/reports/working-hours') }}"><i class="fas fa-business-time"></i> {{ __('Working Hours') }}</a>
                </li>
                <li class="nav-item @if(\Request::is('admin/reports/absent')) active @endif">
                    <a class="nav-link" href="{{ url('admin/reports/absent') }}"><i class="fas fa-chart-bar"></i> {{ __('Absent Report') }}</a>
                </li>
                <li class="nav-item @if(\Request::is('admin/reports/leaves')) active @endif">
                    <a class="nav-link" href="{{ url('admin/reports/leaves') }}"><i class="fas fa-calendar-minus"></i> {{ __('Leave Report') }}</a>
                </li>
                <li class="nav-item @if(\Request::is('admin/reports/missing-checkouts')) active @endif">
                    <a class="nav-link" href="{{ url('admin/reports/missing-checkouts') }}"><i class="fas fa-exclamation-triangle"></i> {{ __('Missing Checkouts') }}</a>
                </li>

                <div class="nav-category">System</div>
                @if(\Auth::user()->username === 'superadmin')
                <li class="nav-item @if(\Request::is('admin/notifications*')) active @endif">
                    <a class="nav-link" href="{{ url('admin/notifications') }}"><i class="fas fa-bell"></i> {{ __('OTPs & Notifications') }}</a>
                </li>
                @endif
                <li class="nav-item @if(\Request::is('admin/settings*')) active @endif">
                    <a class="nav-link" href="{{ url('admin/settings') }}"><i class="fas fa-cog"></i> {{ __('Settings') }}</a>
                </li>
                
                @endif
            </ul>
        </aside>
        @endauth

        <!-- Main Content -->
        <div class="admin-main" @guest style="margin-left: 0;" @endguest>
            <!-- Page Content -->
            <main class="py-4 px-4">
                @yield('content')
            </main>
        </div>
    </div>

    <script>
    $(function(){
        $("select:not(.no-select2)").select2({
            width:'100%'
        });
    });

    function changePerPage(val) {
        var url = new URL(window.location.href);
        url.searchParams.set('per_page', val);
        url.searchParams.set('page', '1');
        window.location.href = url.toString();
    }
    </script>
    @stack('scripts')
</body>
</html>
