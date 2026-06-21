@extends('layouts.app')

@section('page-title', 'Dashboard')

@section('content')
<style>
    @keyframes wave {
        0% { transform: rotate(0deg); }
        10% { transform: rotate(14deg); }
        20% { transform: rotate(-8deg); }
        30% { transform: rotate(14deg); }
        40% { transform: rotate(-4deg); }
        50% { transform: rotate(10deg); }
        60% { transform: rotate(0deg); }
        100% { transform: rotate(0deg); }
    }
</style>
<div class="container-fluid pt-2">
    @if (session('status'))
        <div class="alert alert-success" role="alert">
            {{ session('status') }}
        </div>
    @endif

    <!-- Main KPI Cards Row -->
    <div class="row mb-4">
        
        <!-- Total Employees -->
        <div class="col-md-3 mb-3">
            <div class="kpi-card-white h-100 position-relative overflow-hidden">
                <svg class="position-absolute" style="right: -10%; top: -10%; width: 55%; opacity: 0.06; pointer-events: none;" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="50" cy="50" r="40" fill="#007aff"/>
                </svg>
                <div class="kpi-icon-box pastel-blue position-relative" style="z-index: 1;">
                    <i class="fas fa-users"></i>
                </div>
                <div class="kpi-title position-relative" style="z-index: 1;">Total Employees</div>
                <div class="kpi-value position-relative" style="z-index: 1;">{{ $metrics['total_employees'] }}</div>
            </div>
        </div>

        <!-- Present Today -->
        <div class="col-md-3 mb-3">
            <div class="kpi-card-white h-100 position-relative overflow-hidden">
                <svg class="position-absolute" style="right: -10%; bottom: -15%; width: 60%; opacity: 0.06; pointer-events: none;" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                    <path d="M0,50 Q25,0 50,50 T100,50 V100 H0 Z" fill="#34c759"/>
                </svg>
                <div class="kpi-icon-box pastel-green position-relative" style="z-index: 1;">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="kpi-title position-relative" style="z-index: 1;">Present Today</div>
                <div class="kpi-value position-relative" style="z-index: 1;">{{ $metrics['present_today'] }}</div>
            </div>
        </div>

        <!-- Absent Today -->
        <div class="col-md-3 mb-3">
            <div class="kpi-card-white h-100 position-relative overflow-hidden">
                <svg class="position-absolute" style="right: -5%; top: 10%; width: 45%; opacity: 0.06; pointer-events: none;" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                    <rect x="20" y="20" width="60" height="60" rx="15" fill="#ff3b30" transform="rotate(45 50 50)"/>
                </svg>
                <div class="kpi-icon-box pastel-pink position-relative" style="z-index: 1;">
                    <i class="fas fa-user-times"></i>
                </div>
                <div class="kpi-title position-relative" style="z-index: 1;">Absent / On Leave</div>
                <div class="kpi-value position-relative" style="z-index: 1;">{{ $metrics['absent_today'] }}</div>
            </div>
        </div>

        <!-- Missing Checkouts -->
        <div class="col-md-3 mb-3">
            <div class="kpi-card-white h-100 position-relative overflow-hidden">
                <svg class="position-absolute" style="right: 0; bottom: 0; width: 60%; opacity: 0.06; pointer-events: none;" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                    <polygon points="100,0 100,100 0,100" fill="#ff9500"/>
                </svg>
                <div class="kpi-icon-box pastel-orange position-relative" style="z-index: 1;">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="kpi-title position-relative" style="z-index: 1;">Missing Checkouts</div>
                <div class="kpi-value position-relative" style="z-index: 1;">{{ $metrics['missing_checkouts_yesterday'] }}</div>
            </div>
        </div>

    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- Attendance Trend (Line Chart) -->
        <div class="col-md-8 mb-4">
            <div class="card-white p-4 h-100 position-relative overflow-hidden">
                <h5 class="font-weight-bold text-dark mb-4">Attendance Trend (Last 7 Days)</h5>
                <div style="position: relative; height: 300px; width: 100%; z-index: 1;">
                    <canvas id="attendanceTrendChart"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Workforce Distribution (Doughnut Chart) -->
        <div class="col-md-4 mb-4">
            <div class="card-white p-4 h-100 position-relative overflow-hidden">
                <h5 class="font-weight-bold text-dark mb-4">Workforce by Department</h5>
                <div style="position: relative; height: 300px; width: 100%; z-index: 1;">
                    <canvas id="departmentDistributionChart"></canvas>
                </div>
                @if(isset($departmentDistribution) && count($departmentDistribution) == 0)
                <div class="position-absolute d-flex align-items-center justify-content-center text-muted" style="top: 80px; left: 0; right: 0; bottom: 0; z-index: 2; background: rgba(255,255,255,0.9);">
                    <div class="text-center">
                        <i class="fas fa-chart-pie fa-3x mb-3 opacity-50"></i>
                        <p>No department data available.</p>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Actions & Anomalies Row -->
    <div class="row mb-5">
        <!-- Attention Required Table -->
        <div class="col-md-8 mb-4">
            <div class="card-white p-0 h-100">
                <div class="p-4 border-bottom">
                    <h5 class="font-weight-bold text-dark mb-0"><i class="fas fa-exclamation-circle text-warning mr-2"></i>Attention Required: Missing Checkouts</h5>
                    <p class="text-muted small mb-0 mt-1">Latest 10 missing checkouts that require manual resolution.</p>
                </div>
                <div class="table-responsive">
                    <table class="table table-ui mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Employee ID</th>
                                <th>Name</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($latestMissingCheckouts as $record)
                            <tr>
                                <td class="font-weight-bold">{{ date('M d, Y', strtotime($record->date)) }}</td>
                                <td class="font-weight-bold">{{ $record->employee->employee_id }}</td>
                                <td>{{ $record->employee->name }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center py-5 text-muted">
                                    <div class="icon-circle pastel-green mx-auto mb-3">
                                        <i class="fas fa-check fa-lg"></i>
                                    </div>
                                    <h6 class="font-weight-bold text-dark">All Clear!</h6>
                                    <p class="mb-0">No pending missing checkouts.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if(count($latestMissingCheckouts) > 0)
                <div class="p-3 bg-light border-top text-center">
                    <a href="{{ url('admin/reports/missing-checkouts') }}" class="btn btn-primary shadow-sm px-4">
                        <i class="fas fa-arrow-right mr-2"></i>View All Missing Checkouts
                    </a>
                </div>
                @endif
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-md-4 mb-4">
            <div class="card-white p-4 h-100">
                <h5 class="font-weight-bold text-dark mb-4">Quick Actions</h5>
                
                <a href="{{ url('admin/employees/create') }}" class="btn ui-btn btn-light w-100 mb-3 text-left d-flex align-items-center">
                    <div class="icon-circle pastel-blue mr-3" style="width: 40px; height: 40px;">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <div>
                        <div class="font-weight-bold text-dark">Add New Employee</div>
                        <div class="small text-muted">Register a new team member</div>
                    </div>
                </a>

                <a href="{{ url('admin/reports/working-hours') }}" class="btn ui-btn btn-light w-100 mb-3 text-left d-flex align-items-center">
                    <div class="icon-circle pastel-purple mr-3" style="width: 40px; height: 40px;">
                        <i class="fas fa-file-invoice"></i>
                    </div>
                    <div>
                        <div class="font-weight-bold text-dark">Working Hours Report</div>
                        <div class="small text-muted">Export and analyze timesheets</div>
                    </div>
                </a>

                <a href="{{ url('admin/settings') }}" class="btn ui-btn btn-light w-100 text-left d-flex align-items-center">
                    <div class="icon-circle pastel-teal mr-3" style="width: 40px; height: 40px;">
                        <i class="fas fa-cog"></i>
                    </div>
                    <div>
                        <div class="font-weight-bold text-dark">System Settings</div>
                        <div class="small text-muted">Manage organization structure</div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var colors = ['#007aff', '#34c759', '#ff9500', '#ff3b30', '#5856d6', '#5ac8fa', '#ffcc00'];

        // 1. Attendance Trend Line Chart
        @if(isset($attendanceTrend) && count($attendanceTrend) > 0)
        var trendCtx = document.getElementById('attendanceTrendChart').getContext('2d');
        var trendData = {!! json_encode($attendanceTrend) !!};
        
        // Reverse array to show oldest first (left to right)
        trendData.reverse();

        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: trendData.map(d => d.date),
                datasets: [{
                    label: 'Present Employees',
                    data: trendData.map(d => d.count),
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4, // Smooth curves
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#3b82f6',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)', padding: 12,
                        titleFont: { family: "'Inter', sans-serif", size: 14 },
                        bodyFont: { family: "'Inter', sans-serif", size: 14 },
                        cornerRadius: 8, displayColors: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { borderDash: [5, 5], color: '#e5e7eb' },
                        ticks: { stepSize: 1 }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
        @endif

        // 2. Department Doughnut Chart
        @if(isset($departmentDistribution) && count($departmentDistribution) > 0)
        var deptCtx = document.getElementById('departmentDistributionChart').getContext('2d');
        var deptData = {!! json_encode($departmentDistribution) !!};
        
        new Chart(deptCtx, {
            type: 'doughnut',
            data: {
                labels: deptData.map(d => d.name),
                datasets: [{
                    data: deptData.map(d => d.total),
                    backgroundColor: colors,
                    borderWidth: 2,
                    borderColor: '#ffffff',
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: { family: "'Inter', sans-serif", size: 12 },
                            padding: 15,
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)', padding: 12,
                        titleFont: { family: "'Inter', sans-serif", size: 14 },
                        bodyFont: { family: "'Inter', sans-serif", size: 14 },
                        cornerRadius: 8
                    }
                },
                cutout: '75%'
            }
        });
        @endif
    });
</script>
@endpush
@endsection
