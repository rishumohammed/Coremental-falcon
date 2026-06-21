<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

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
    </style>
</head>
<body>
    <div id="app" class="admin-wrapper">
        
        <!-- Premium Topbar -->
        <nav class="admin-topbar">
            <div class="topbar-left d-flex align-items-center h-100">
                <a class="topbar-brand" href="{{ url('/') }}">
                    {{ config('app.name', 'Falcon') }}
                </a>
            </div>
            <div class="topbar-right pr-4">
                <ul class="navbar-nav">
                    @guest
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                        </li>
                    @else
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
                    @endguest
                </ul>
            </div>
        </nav>

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
                <li class="nav-item @if(\Request::is('admin/settings')) active @endif">
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
    </script>
    @stack('scripts')
</body>
</html>
