@extends('layouts.app')

@section('page-title', 'Missing Checkouts Report')

@section('content')
<div class="container-fluid pt-2">
    @if (session('status'))
        <div class="alert alert-success mb-4" role="alert">
            {{ session('status') }}
        </div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger mb-4">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Search & Filter Card -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card-white p-4">
                <form action="{{ url('admin/reports/missing-checkouts') }}" method="GET" class="row align-items-end">
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
                        <select name="department_id" class="form-control" onchange="this.form.submit()">
                            <option value="">All Departments</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3 d-flex justify-content-end">
                        <button type="submit" class="btn ui-btn ui-btn-primary">
                            <i class="fas fa-filter mr-1"></i> Filter
                        </button>
                        <a href="{{ url('admin/reports/missing-checkouts') }}" class="btn ui-btn btn-light ml-2 px-3" title="Reset Filters">
                            <i class="fas fa-undo text-secondary"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Data Table Card -->
    <div class="card-white p-0">
        <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
            <h5 class="mb-0 font-weight-bold text-dark">Missing Checkouts</h5>
            <a href="{{ request()->fullUrlWithQuery(['export' => 1]) }}" class="btn btn-light border btn-sm">
                <i class="fas fa-file-excel text-success mr-1"></i> Export Excel
            </a>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Date</th>
                        <th>Employee ID</th>
                        <th>Name</th>
                        <th>Department</th>
                        <th>Check In Time</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($paginatedRows as $row)
                        <tr>
                            <td class="font-weight-bold">{{ date('M d, Y', strtotime($row->date)) }}</td>
                            <td>{{ $row->employee->employee_id }}</td>
                            <td class="font-weight-bold text-dark">{{ $row->employee->name }}</td>
                            <td>
                                @if($row->employee->department)
                                    <span class="badge badge-info">{{ $row->employee->department->name }}</span>
                                @else
                                    <span class="text-muted small">N/A</span>
                                @endif
                            </td>
                            <td>
                                <span class="text-success"><i class="fas fa-sign-in-alt mr-1"></i>{{ date('h:i A', strtotime($row->check_in_time)) }}</span>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#manualCheckoutModal-{{ $row->employee->id }}-{{ $row->date }}">
                                    <i class="fas fa-plus mr-1"></i> Add Checkout
                                </button>

                                <!-- Manual Checkout Modal -->
                                <div class="modal fade" id="manualCheckoutModal-{{ $row->employee->id }}-{{ $row->date }}" tabindex="-1" role="dialog" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content text-left">
                                            <div class="modal-header">
                                                <h5 class="modal-title font-weight-bold"><i class="fas fa-clock mr-2 text-primary"></i>Add Manual Checkout</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <form action="{{ url('admin/reports/manual-checkout') }}" method="POST">
                                                @csrf
                                                <div class="modal-body bg-light">
                                                    <div class="alert alert-info small mb-3">
                                                        You are adding a manual checkout for <strong>{{ $row->employee->name }}</strong> on <strong>{{ date('M d, Y', strtotime($row->date)) }}</strong>.
                                                    </div>
                                                    <input type="hidden" name="employee_id" value="{{ $row->employee->id }}">
                                                    <input type="hidden" name="date" value="{{ $row->date }}">
                                                    
                                                    <div class="form-group">
                                                        <label class="font-weight-bold">Checkout Time</label>
                                                        <input type="time" name="time" class="form-control" required value="17:00">
                                                        <small class="text-muted">Enter the time the employee left.</small>
                                                    </div>
                                                </div>
                                                <div class="modal-footer p-2 border-top">
                                                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn ui-btn ui-btn-primary"><i class="fas fa-save mr-1"></i> Save Checkout</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fas fa-check-circle fa-3x mb-3 text-success opacity-50"></i>
                                <h5>No missing checkouts found!</h5>
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
