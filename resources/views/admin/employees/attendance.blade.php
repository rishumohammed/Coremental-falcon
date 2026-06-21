@extends('layouts.app')

@section('page-title', 'Employee Attendance')

@section('content')
<div class="container-fluid pt-2">

    @if (session('status'))
        <div class="alert alert-success mb-4" role="alert">
            {{ session('status') }}
        </div>
    @endif

    <!-- Search & Filter Card -->
    <div class="card-white filter-card">
        <form method="GET">
            <div class="d-flex flex-column flex-md-row mb-3">
                <!-- Text Search -->
                <div class="search-input-wrapper flex-grow-1 mr-md-3 mb-2 mb-md-0">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" list="employee_names" class="ui-input-search" value="{{ request('search') }}" placeholder="Search employee name or ID..." autocomplete="off">
                    <datalist id="employee_names">
                        @foreach($employees as $emp)
                            <option value="{{ $emp->name }}">{{ $emp->employee_id }}</option>
                        @endforeach
                    </datalist>
                </div>
                <!-- Type Select -->
                <div class="w-100 mr-md-3 mb-2 mb-md-0" style="max-width: 200px;">
                    <select class="form-control" name="type">
                        <option value=''>All Types</option>
                        <option value='0' @if(request('type') === '0') selected @endif>Check In</option>
                        <option value='1' @if(request('type') === '1') selected @endif>Check Out</option>
                    </select>
                </div>
                <!-- Employee Select -->
                <div class="w-100" style="max-width: 300px;">
                    <select class="form-control" name="employee_id">
                        <option value=''>All Employees</option>
                        @foreach($employees as $row)
                        <option value='{{$row->id}}' @if($row->id == old('employee_id', request('employee_id'))) selected @endif >{{$row->employee_id.' - '.$row->name}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-3 mb-2 mb-md-0">
                    <select class="form-control" name="department_id">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" @if(request('department_id') == $dept->id) selected @endif>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-2 mb-md-0">
                    <select class="form-control" name="designation_id">
                        <option value="">All Designations</option>
                        @foreach($designations as $desig)
                            <option value="{{ $desig->id }}" @if(request('designation_id') == $desig->id) selected @endif>{{ $desig->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-2 mb-md-0">
                    <select class="form-control" name="shift_id">
                        <option value="">All Shifts</option>
                        @foreach($shifts as $shift)
                            <option value="{{ $shift->id }}" @if(request('shift_id') == $shift->id) selected @endif>{{ $shift->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-2 mb-md-0">
                    <select class="form-control" name="location_id">
                        <option value="">All Locations</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" @if(request('location_id') == $loc->id) selected @endif>{{ $loc->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            
            <div class="d-flex align-items-center justify-content-between flex-wrap">
                <div class="d-flex align-items-center flex-wrap">
                    <div class="mr-3 d-flex align-items-center mb-2 mb-md-0">
                        <span class="text-muted mr-2 small font-weight-bold text-uppercase">From:</span>
                        <input type="date" class="ui-select bg-white" name="from_date" value="{{old('from_date', request('from_date'))}}" />
                    </div>
                    <div class="mr-3 d-flex align-items-center mb-2 mb-md-0">
                        <span class="text-muted mr-2 small font-weight-bold text-uppercase">To:</span>
                        <input type="date" class="ui-select bg-white" name="to_date" value="{{old('to_date', request('to_date'))}}" />
                    </div>
                </div>
                <div class="d-flex align-items-center mt-2 mt-md-0">
                    <button type="submit" name="action" value="filter" class="btn ui-btn ui-btn-primary mr-2">
                        <i class="fas fa-filter mr-1"></i> Filter
                    </button>
                    <a href="{{ url('admin/employees/attendance') }}" class="btn ui-btn btn-light px-3 mr-2" title="Reset Filters"><i class="fas fa-undo text-secondary"></i></a>
                    <button type="submit" name="export" value="1" class="btn ui-btn btn-light border">
                        <i class="fas fa-file-export mr-1"></i> Export
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Data Table Card -->
    <div class="card-white p-0 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-ui mb-0" style="min-width: 1400px;">
                <thead>
                    <tr>
                        <th style="width: 80px;">Photo</th>
                        <th style="min-width: 200px;">Employee</th>
                        <th style="min-width: 150px;">Department</th>
                        <th style="min-width: 150px;">Designation</th>
                        <th style="min-width: 120px;">Shift</th>
                        <th style="min-width: 150px;">Work Loc.</th>
                        <th style="min-width: 150px;">Status</th>
                        <th style="min-width: 250px;">Location & Device</th>
                        <th style="min-width: 150px;">Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($rows as $row)
                <tr>
                    <td>
                        @if($row->photo)
                        <a href="{{$row->photo_url}}" target="_blank">
                            <img src="{{$row->photo_url}}" class="rounded shadow-sm" style="width:80px; height:80px; object-fit:cover; border: 2px solid #e5e7eb;" />
                        </a>
                        @else
                        <div class="rounded bg-light d-flex align-items-center justify-content-center text-muted" style="width:80px; height:80px; border: 1px dashed #cbd5e1;">
                            <i class="fas fa-camera text-black-50 fa-lg"></i>
                        </div>
                        @endif
                    </td>
                    <td>
                        <div>
                            <div class="font-weight-bold text-dark" style="font-size: 1rem;">{{optional($row->employee)->name ?? 'Unknown Employee'}}</div>
                            <div class="text-muted small mt-1">
                                <span class="mr-2"><i class="fas fa-id-badge"></i> {{optional($row->employee)->employee_id ?? 'N/A'}}</span>
                                <span><i class="fas fa-user-circle"></i> {{optional($row->user)->username ?? 'N/A'}}</span>
                            </div>
                        </div>
                    </td>
                    <td>
                        @if(optional($row->employee)->department)
                            <span class="badge badge-info">{{ $row->employee->department->name }}</span>
                        @else
                            <span class="text-muted small">N/A</span>
                        @endif
                    </td>
                    <td>
                        @if(optional($row->employee)->designation)
                            <span class="badge badge-secondary">{{ $row->employee->designation->name }}</span>
                        @else
                            <span class="text-muted small">N/A</span>
                        @endif
                    </td>
                    <td>
                        @if(optional($row->employee)->shift)
                            <span class="badge badge-light border">{{ $row->employee->shift->name }}</span>
                        @else
                            <span class="text-muted small">N/A</span>
                        @endif
                    </td>
                    <td>
                        @if(optional($row->employee)->location)
                            <span class="text-dark small">{{ $row->employee->location->name }}</span>
                        @else
                            <span class="text-muted small">N/A</span>
                        @endif
                    </td>
                    <td>
                        <div class="mb-1">
                            @if($row->type_label == 'Check In')
                                <span class="text-success font-weight-bold"><i class="fas fa-sign-in-alt mr-1"></i> Check In</span>
                            @else
                                <span class="text-danger font-weight-bold"><i class="fas fa-sign-out-alt fa-flip-horizontal mr-1"></i> Check Out</span>
                            @endif
                        </div>
                        <div><span class="badge badge-secondary px-2 py-1">{{$row->entry_type_label}}</span></div>
                    </td>
                    <td>
                        <div class="d-flex align-items-start">
                            @if($row->lat && $row->lng)
                            <a href="https://maps.google.com?q={{urlencode($row->lat.','.$row->lng)}}" target="_blank" class="btn btn-sm btn-light mr-2 mt-1 rounded-circle flex-shrink-0" title="View Map" style="min-width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border: 1px solid #e2e8f0;">
                                <i class="fas fa-map-marker-alt text-danger"></i>
                            </a>
                            @else
                            <div class="btn btn-sm btn-light mr-2 mt-1 rounded-circle flex-shrink-0" style="min-width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center; opacity: 0.5; border: 1px solid #cbd5e1;">
                                <i class="fas fa-map-marker-alt text-secondary"></i>
                            </div>
                            @endif
                            <div>
                                <div class="text-dark mb-1" style="line-height: 1.4; font-size: 0.9rem;">
                                    {{ $row->address ?: 'Location address not available' }}
                                </div>
                                @if($row->device)
                                <div class="text-muted small"><i class="fas fa-mobile-alt mr-1"></i> {{$row->device}}</div>
                                @else
                                <div class="text-muted small"><i class="fas fa-laptop mr-1"></i> Unknown Device</div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="font-weight-bold text-dark">{{date('d M, Y', strtotime($row->created_at->timezone(timezone())))}}</div>
                        <div class="text-primary font-weight-bold small mt-1"><i class="far fa-clock"></i> {{date('h:i A', strtotime($row->created_at->timezone(timezone())))}}</div>
                    </td>
                </tr>
                @endforeach
                @if(count($rows) == 0)
                <tr>
                    <td colspan="9" class="text-center py-5 text-muted">
                        <i class="fas fa-clipboard-list fa-3x mb-3 opacity-50"></i>
                        <h5>No attendance records found</h5>
                    </td>
                </tr>
                @endif
                </tbody>
            </table>
        </div>
        <div class="p-3 border-top">
            {{$rows->render()}}
        </div>
    </div>
</div>
@endsection
