@extends('layouts.app')

@section('page-title', 'Meeting Attendance')

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
                    <input type="text" name="search" class="ui-input-search" value="{{ request('search') }}" placeholder="Search purpose, notes...">
                </div>
                <!-- Type Select -->
                <div class="w-100 mr-md-3 mb-2 mb-md-0" style="max-width: 200px;">
                    <select class="form-control" name="type">
                        <option value=''>All Types</option>
                        <option value='0' @if(request('type') === '0') selected @endif>Check In</option>
                        <option value='1' @if(request('type') === '1') selected @endif>Check Out</option>
                    </select>
                </div>
                <!-- Salesman Select -->
                <div class="w-100" style="max-width: 300px;">
                    <select class="form-control" name="salesman_id">
                        <option value=''>All Employees</option>
                        @foreach($salesmans as $row)
                        <option value='{{$row->id}}' @if($row->id == old('salesman_id', request('salesman_id'))) selected @endif >{{$row->employee_id.' - '.$row->name}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            
            <div class="d-flex align-items-center justify-content-between flex-wrap">
                <div class="d-flex align-items-center flex-wrap">
                    <div class="mr-3 d-flex align-items-center mb-2 mb-md-0">
                        <span class="text-muted mr-2 small font-weight-bold text-uppercase">From:</span>
                        <input type="date" class="ui-select" name="from_date" value="{{old('from_date', request('from_date'))}}" />
                    </div>
                    <div class="mr-3 d-flex align-items-center mb-2 mb-md-0">
                        <span class="text-muted mr-2 small font-weight-bold text-uppercase">To:</span>
                        <input type="date" class="ui-select" name="to_date" value="{{old('to_date', request('to_date'))}}" />
                    </div>
                </div>
                <div class="d-flex align-items-center mt-2 mt-md-0">
                    <button type="submit" name="action" value="filter" class="btn ui-btn ui-btn-primary mr-2">
                        <i class="fas fa-filter mr-1"></i> Filter
                    </button>
                    <a href="{{ url('admin/salesman/attendance') }}" class="btn ui-btn btn-light px-3 mr-2" title="Reset Filters"><i class="fas fa-undo text-secondary"></i></a>
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
            <table class="table table-ui mb-0">
                <thead>
                    <tr>
                        <th style="width: 100px;">Photo</th>
                        <th style="width: 20%">Employee</th>
                        <th style="width: 25%">Visit Details</th>
                        <th style="width: 15%">Status</th>
                        <th style="width: 25%">Location</th>
                        <th style="width: 10%">Timestamp</th>
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
                            <div class="font-weight-bold text-dark" style="font-size: 1rem;">{{$row->salesman ? $row->salesman->name : '-'}}</div>
                            <div class="text-muted small mt-1">
                                <i class="fas fa-id-badge mr-1"></i> {{$row->salesman ? $row->salesman->employee_id : '-'}}
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="mb-1 text-dark"><strong>{{$row->customer_name}}</strong></div>
                        <div class="text-muted mb-1" style="font-size: 0.9rem;">{{$row->purpose}}</div>
                        @if($row->meeting_notes)
                        <div class="small bg-light p-2 rounded text-secondary" style="border-left: 3px solid #cbd5e1;">
                            {{$row->meeting_notes}}
                        </div>
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
                        <div class="font-weight-bold text-dark">{{date('d M, y', strtotime($row->created_at->timezone(timezone())))}}</div>
                        <div class="text-primary font-weight-bold small mt-1">{{date('h:i A', strtotime($row->created_at->timezone(timezone())))}}</div>
                    </td>
                </tr>
                @endforeach
                @if(count($rows) == 0)
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">
                        <i class="fas fa-calendar-times fa-3x mb-3 opacity-50"></i>
                        <h5>No meeting attendance found</h5>
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
