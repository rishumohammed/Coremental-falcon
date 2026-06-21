@extends('layouts.app')

@section('page-title', 'Leave Report')

@section('content')
<div class="container-fluid pt-2">
    <!-- Search & Filter Card -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card-white p-4">
                <form action="{{ url('admin/reports/leaves') }}" method="GET" class="row align-items-end">
                    <div class="col-md-3 mb-3">
                        <label class="font-weight-bold text-dark small">Search</label>
                        <input type="text" name="search" list="employee_names" class="form-control" value="{{ request('search') }}" placeholder="Name or ID" autocomplete="off">
                        <datalist id="employee_names">
                            @foreach($allEmployees as $emp)
                                <option value="{{ $emp->name }}">{{ $emp->employee_id }}</option>
                            @endforeach
                        </datalist>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="font-weight-bold text-dark small">Department</label>
                        <select name="department_id" class="form-control">
                            <option value="">All Departments</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="font-weight-bold text-dark small">From Date</label>
                        <input type="date" name="from_date" class="form-control" value="{{ request('from_date', date('Y-m-01')) }}">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="font-weight-bold text-dark small">To Date</label>
                        <input type="date" name="to_date" class="form-control" value="{{ request('to_date', date('Y-m-t')) }}">
                    </div>
                    <div class="col-md-2 mb-3">
                        <div class="d-flex w-100">
                            <button type="submit" class="btn ui-btn ui-btn-primary flex-grow-1">
                                <i class="fas fa-filter mr-1"></i> Filter
                            </button>
                            <a href="{{ url('admin/reports/leaves') }}" class="btn ui-btn btn-light ml-2 px-3" title="Reset Filters">
                                <i class="fas fa-undo text-secondary"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Data Table Card -->
    <div class="card-white p-0">
        <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
            <h5 class="mb-0 font-weight-bold text-dark">Leave Summary</h5>
            <a href="{{ request()->fullUrlWithQuery(['export' => 1]) }}" class="btn btn-light border btn-sm">
                <i class="fas fa-file-excel text-success mr-1"></i> Export Excel
            </a>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Employee ID</th>
                        <th>Name</th>
                        <th>Department</th>
                        @foreach($allLeaveTypes as $lt)
                            <th class="text-center">{{ $lt->name }}</th>
                        @endforeach
                        <th class="text-center text-danger">Absent (Unassigned)</th>
                        <th class="text-center bg-light font-weight-bold">Total Leaves</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($paginatedRows as $row)
                        <tr style="cursor: pointer;" data-toggle="collapse" data-target="#details-{{ $row['employee']->id }}" class="accordion-toggle" title="Click to view detailed dates">
                            <td>{{ $row['employee']->employee_id }}</td>
                            <td class="font-weight-bold text-dark">{{ $row['employee']->name }}</td>
                            <td>
                                @if($row['employee']->department)
                                    <span class="badge badge-info">{{ $row['employee']->department->name }}</span>
                                @else
                                    <span class="text-muted small">N/A</span>
                                @endif
                            </td>
                            @foreach($allLeaveTypes as $lt)
                                <td class="text-center">
                                    @if($row['breakdown'][$lt->name] > 0)
                                        <span class="font-weight-bold text-primary">{{ $row['breakdown'][$lt->name] }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            @endforeach
                            <td class="text-center">
                                @if($row['breakdown']['Absent'] > 0)
                                    <span class="font-weight-bold text-danger">{{ $row['breakdown']['Absent'] }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center bg-light font-weight-bold" style="font-size: 1.1rem;">
                                {{ $row['total_leaves'] }}
                            </td>
                        </tr>
                        <tr>
                            <td colspan="{{ 5 + count($allLeaveTypes) }}" class="p-0 border-0">
                                <div class="collapse" id="details-{{ $row['employee']->id }}">
                                    <div class="p-3 bg-light border-bottom" style="box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);">
                                        <h6 class="font-weight-bold text-dark mb-3"><i class="fas fa-list-ul mr-2 text-primary"></i>Specific Leave Dates</h6>
                                        @if(count($row['details']) > 0)
                                            <div class="row">
                                                @foreach($row['details'] as $detail)
                                                    <div class="col-md-3 col-sm-6 mb-2">
                                                        <div class="d-flex align-items-center bg-white p-2 rounded border border-light shadow-sm">
                                                            <i class="far fa-calendar-alt text-muted mr-2"></i>
                                                            <span class="mr-2 text-dark font-weight-bold small">{{ date('M d, Y', strtotime($detail['date'])) }}</span>
                                                            @if($detail['type'] == 'Absent')
                                                                <span class="badge badge-danger ml-auto">Absent</span>
                                                            @else
                                                                <span class="badge badge-info ml-auto">{{ $detail['type'] }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="text-muted small">No leaves recorded.</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 4 + count($allLeaveTypes) }}" class="text-center py-5 text-muted">
                                <i class="fas fa-clipboard-list fa-3x mb-3 opacity-50"></i>
                                <h5>No leave records found for the selected period.</h5>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($paginatedRows->hasPages())
        <div class="p-3 border-top">
            {{ $paginatedRows->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
