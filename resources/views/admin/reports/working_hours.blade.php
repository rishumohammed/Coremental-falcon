@extends('layouts.app')

@section('page-title', 'Working Hours Report')

@section('content')
<div class="container-fluid pt-2">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card-white p-4">
                <form action="{{ url('admin/reports/working-hours') }}" method="GET" class="row">
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
                    <div class="col-md-3 mb-3">
                        <label class="font-weight-bold text-dark small">Designation</label>
                        <select name="designation_id" class="form-control">
                            <option value="">All Designations</option>
                            @foreach($designations as $desig)
                                <option value="{{ $desig->id }}" {{ request('designation_id') == $desig->id ? 'selected' : '' }}>{{ $desig->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="font-weight-bold text-dark small">Shift Type</label>
                        <select name="shift_id" class="form-control">
                            <option value="">All Shifts</option>
                            @foreach($shifts as $shift)
                                <option value="{{ $shift->id }}" {{ request('shift_id') == $shift->id ? 'selected' : '' }}>{{ $shift->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="font-weight-bold text-dark small">Location</label>
                        <select name="location_id" class="form-control">
                            <option value="">All Locations</option>
                            @foreach($locations as $loc)
                                <option value="{{ $loc->id }}" {{ request('location_id') == $loc->id ? 'selected' : '' }}>{{ $loc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if(request('view_type') == 'monthly')
                    <div class="col-md-6 mb-3">
                        <label class="font-weight-bold text-dark small">Select Month</label>
                        <input type="month" name="month_filter" class="form-control" value="{{ request('month_filter', date('Y-m')) }}">
                    </div>
                    @else
                    <div class="col-md-3 mb-3">
                        <label class="font-weight-bold text-dark small">From Date</label>
                        <input type="date" name="from_date" class="form-control" value="{{ request('from_date', date('Y-m-d')) }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="font-weight-bold text-dark small">To Date</label>
                        <input type="date" name="to_date" class="form-control" value="{{ request('to_date', date('Y-m-d')) }}">
                    </div>
                    @endif

                    <div class="col-md-3 mb-3">
                        <label class="font-weight-bold text-dark small">View Type</label>
                        <select name="view_type" class="form-control" onchange="this.form.submit()">
                            <option value="daily" {{ request('view_type') == 'daily' ? 'selected' : '' }}>Daily</option>
                            <option value="monthly" {{ request('view_type') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                            <option value="total" {{ request('view_type') == 'total' ? 'selected' : '' }}>Total Summary</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="font-weight-bold text-dark small">Sort By</label>
                        <select name="sort_by" class="form-control">
                            <option value="date" {{ request('sort_by') == 'date' ? 'selected' : '' }}>Date / Period</option>
                            <option value="name" {{ request('sort_by') == 'name' ? 'selected' : '' }}>Employee Name</option>
                            <option value="hours" {{ request('sort_by') == 'hours' ? 'selected' : '' }}>Total Hours</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="font-weight-bold text-dark small">Sort Direction</label>
                        <select name="sort_dir" class="form-control">
                            <option value="desc" {{ request('sort_dir') == 'desc' ? 'selected' : '' }}>Descending (Z-A / Highest / Newest)</option>
                            <option value="asc" {{ request('sort_dir') == 'asc' ? 'selected' : '' }}>Ascending (A-Z / Lowest / Oldest)</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3 d-flex align-items-end">
                        <div class="d-flex w-100">
                            <button type="submit" class="btn ui-btn ui-btn-primary flex-grow-1">
                                <i class="fas fa-filter mr-1"></i> Filter
                            </button>
                            <a href="{{ url('admin/reports/working-hours') }}" class="btn ui-btn btn-light ml-2 px-3" title="Reset Filters">
                                <i class="fas fa-undo text-secondary"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card-white p-3 d-flex justify-content-between align-items-center" style="background-color: #f0fdf4; border: 1px solid #bbf7d0;">
                <div>
                    <h6 class="mb-0 text-success font-weight-bold"><i class="fas fa-clock mr-2"></i>Grand Total Working Hours</h6>
                    <small class="text-muted">Total for all filtered employees across selected period</small>
                </div>
                <h3 class="mb-0 text-success font-weight-bold">{{ $grandTotalHours }}</h3>
            </div>
        </div>
    </div>

    <div class="card-white p-0">
        <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
            <h5 class="mb-0 font-weight-bold text-dark">Calculated Hours</h5>
            <a href="{{ request()->fullUrlWithQuery(['export' => 1]) }}" class="btn btn-light border btn-sm">
                <i class="fas fa-file-excel text-success mr-1"></i> Export Excel
            </a>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover mb-0" style="min-width: 1500px;">
                <thead class="bg-light">
                    <tr>
                        <th style="min-width: 120px;">
                            @if($viewType == 'monthly') Month 
                            @elseif($viewType == 'total') Period
                            @else Date @endif
                        </th>
                        <th style="min-width: 120px;">Employee ID</th>
                        <th style="min-width: 200px;">Name</th>
                        <th style="min-width: 150px;">Department</th>
                        <th style="min-width: 150px;">Designation</th>
                        <th style="min-width: 120px;">Shift</th>
                        <th style="min-width: 150px;">Location</th>
                        @if($viewType == 'daily')
                            <th style="min-width: 100px;">Check In</th>
                            <th style="min-width: 100px;">Check Out</th>
                        @endif
                        <th style="min-width: 120px;">Total Hours</th>
                        <th style="min-width: 120px;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($paginatedRows as $row)
                        @php $pairsCount = count($row->pairs); @endphp
                        @foreach($row->pairs as $index => $pair)
                            <tr class="{{ $row->status !== 'Complete' ? 'table-warning' : '' }}">
                                @if($index === 0)
                                    <td rowspan="{{ $pairsCount }}">
                                        @if($viewType == 'monthly')
                                            {{ date('F Y', strtotime($row->raw_date)) }}
                                        @elseif($viewType == 'total')
                                            <span class="font-weight-bold">{{ $row->date }}</span>
                                        @else
                                            {{ date('M d, Y', strtotime($row->raw_date)) }}
                                        @endif
                                    </td>
                                    <td rowspan="{{ $pairsCount }}">{{ $row->employee->employee_id }}</td>
                                    <td rowspan="{{ $pairsCount }}" class="font-weight-bold text-dark">
                                        {{ $row->employee->name }}
                                    </td>
                                    <td rowspan="{{ $pairsCount }}">
                                        @if($row->employee->department)
                                            <span class="badge badge-info">{{ $row->employee->department->name }}</span>
                                        @else
                                            <span class="text-muted small">N/A</span>
                                        @endif
                                    </td>
                                    <td rowspan="{{ $pairsCount }}">
                                        @if($row->employee->designation)
                                            <span class="badge badge-secondary">{{ $row->employee->designation->name }}</span>
                                        @else
                                            <span class="text-muted small">N/A</span>
                                        @endif
                                    </td>
                                    <td rowspan="{{ $pairsCount }}">
                                        @if($row->employee->shift)
                                            <span class="badge badge-light border">{{ $row->employee->shift->name }}</span>
                                        @else
                                            <span class="text-muted small">N/A</span>
                                        @endif
                                    </td>
                                    <td rowspan="{{ $pairsCount }}">
                                        @if($row->employee->location)
                                            <span class="text-dark small">{{ $row->employee->location->name }}</span>
                                        @else
                                            <span class="text-muted small">N/A</span>
                                        @endif
                                    </td>
                                @endif
                                
                                @if($viewType == 'daily')
                                    <td>
                                        @if($pair['in'])
                                            <span class="text-success"><i class="fas fa-sign-in-alt mr-1"></i>{{ date('h:i A', strtotime($pair['in'])) }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($pair['out'])
                                            <span class="text-danger"><i class="fas fa-sign-out-alt mr-1"></i>{{ date('h:i A', strtotime($pair['out'])) }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                @endif
                                
                                @if($index === 0)
                                    <td rowspan="{{ $pairsCount }}" class="font-weight-bold">{{ $row->formatted_time }}</td>
                                    <td rowspan="{{ $pairsCount }}">
                                        @if($row->status !== 'Complete')
                                            <span class="badge badge-danger">{{ $row->status }}</span>
                                        @else
                                            <span class="badge badge-success">Complete</span>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">No records found for the selected date range.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">
            {{ $paginatedRows->links() }}
        </div>
    </div>
</div>
@endsection
