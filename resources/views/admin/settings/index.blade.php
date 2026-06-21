@extends('layouts.app')

@section('page-title', 'System Settings')

@section('content')
<div class="container-fluid pt-2">

    @if (session('status'))
        <div class="alert alert-success mb-4" role="alert">
            {{ session('status') }}
        </div>
    @endif

    <style>
        .feature-card {
            transition: all 0.2s ease-in-out;
            border: 1px solid transparent;
        }
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            border-color: #cbd5e1;
        }
    </style>

    <div class="row mt-3">

        <!-- General Configuration -->
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card card-white h-100 feature-card">
                <div class="card-body p-4 d-flex flex-column align-items-center text-center">
                    <div class="icon-circle mb-3 pastel-blue">
                        <i class="fas fa-cogs fa-lg"></i>
                    </div>
                    <h5 class="card-title font-weight-bold mb-2">General Configuration</h5>
                    <p class="card-text text-muted small mb-4">Configure basic system settings and preferences.</p>
                    <a href="{{ url('admin/settings/general') }}" class="btn ui-btn btn-light mt-auto w-100">Manage Settings</a>
                </div>
            </div>
        </div>

        <!-- Leave Types -->
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card card-white h-100 feature-card">
                <div class="card-body p-4 d-flex flex-column align-items-center text-center">
                    <div class="icon-circle mb-3 pastel-green">
                        <i class="fas fa-calendar-alt fa-lg"></i>
                    </div>
                    <h5 class="card-title font-weight-bold mb-2">Leave Types</h5>
                    <p class="card-text text-muted small mb-4">Define and manage employee leave categories.</p>
                    <a href="{{ url('admin/leave-types') }}" class="btn ui-btn btn-light mt-auto w-100">Manage Leave Types</a>
                </div>
            </div>
        </div>

        <!-- Departments -->
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card card-white h-100 feature-card">
                <div class="card-body p-4 d-flex flex-column align-items-center text-center">
                    <div class="icon-circle mb-3 pastel-purple">
                        <i class="fas fa-sitemap fa-lg"></i>
                    </div>
                    <h5 class="card-title font-weight-bold mb-2">Departments</h5>
                    <p class="card-text text-muted small mb-4">Manage employee departments and structure.</p>
                    <a href="{{ url('admin/departments') }}" class="btn ui-btn btn-light mt-auto w-100">Manage Departments</a>
                </div>
            </div>
        </div>

        <!-- Designations -->
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card card-white h-100 feature-card">
                <div class="card-body p-4 d-flex flex-column align-items-center text-center">
                    <div class="icon-circle mb-3 pastel-pink">
                        <i class="fas fa-user-tag fa-lg"></i>
                    </div>
                    <h5 class="card-title font-weight-bold mb-2">Designations</h5>
                    <p class="card-text text-muted small mb-4">Manage job titles and employee designations.</p>
                    <a href="{{ url('admin/designations') }}" class="btn ui-btn btn-light mt-auto w-100">Manage Designations</a>
                </div>
            </div>
        </div>

        <!-- Shift Types -->
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card card-white h-100 feature-card">
                <div class="card-body p-4 d-flex flex-column align-items-center text-center">
                    <div class="icon-circle mb-3 pastel-orange">
                        <i class="fas fa-clock fa-lg"></i>
                    </div>
                    <h5 class="card-title font-weight-bold mb-2">Shift Types</h5>
                    <p class="card-text text-muted small mb-4">Manage employee working shifts and timings.</p>
                    <a href="{{ url('admin/shifts') }}" class="btn ui-btn btn-light mt-auto w-100">Manage Shifts</a>
                </div>
            </div>
        </div>

        <!-- Locations -->
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card card-white h-100 feature-card">
                <div class="card-body p-4 d-flex flex-column align-items-center text-center">
                    <div class="icon-circle mb-3 pastel-teal">
                        <i class="fas fa-map-marker-alt fa-lg"></i>
                    </div>
                    <h5 class="card-title font-weight-bold mb-2">Locations</h5>
                    <p class="card-text text-muted small mb-4">Manage office branches and work locations.</p>
                    <a href="{{ url('admin/locations') }}" class="btn ui-btn btn-light mt-auto w-100">Manage Locations</a>
                </div>
            </div>
        </div>

        <!-- Weekend Days -->
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card card-white h-100 feature-card">
                <div class="card-body p-4 d-flex flex-column align-items-center text-center">
                    <div class="icon-circle mb-3 pastel-orange">
                        <i class="fas fa-umbrella-beach fa-lg"></i>
                    </div>
                    <h5 class="card-title font-weight-bold mb-2">Weekend Days</h5>
                    <p class="card-text text-muted small mb-4">Set which days of the week are non-working days.</p>
                    <a href="{{ url('admin/settings/weekend') }}" class="btn ui-btn btn-light mt-auto w-100">Configure Weekends</a>
                </div>
            </div>
        </div>

        <!-- Public Holidays -->
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card card-white h-100 feature-card">
                <div class="card-body p-4 d-flex flex-column align-items-center text-center">
                    <div class="icon-circle mb-3 pastel-pink">
                        <i class="fas fa-calendar-day fa-lg"></i>
                    </div>
                    <h5 class="card-title font-weight-bold mb-2">Public Holidays</h5>
                    <p class="card-text text-muted small mb-4">Define public holidays to exclude from absent reports.</p>
                    <a href="{{ url('admin/settings/holidays') }}" class="btn ui-btn btn-light mt-auto w-100">Manage Holidays</a>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
