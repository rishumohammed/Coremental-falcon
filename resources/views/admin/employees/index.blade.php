@extends('layouts.app')

@section('page-title', 'Employee Catalog')

@section('content')
<div class="container-fluid pt-2">
    <!-- Header Area -->
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap" style="gap: 10px;">
        <h4 class="font-weight-bold text-dark mb-0">Employee Catalog</h4>
        <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
            <a href="{{ url('admin/employees/sample-format') }}" class="btn btn-outline-secondary" title="Download Sample Excel Import Template">
                <i class="fas fa-file-download mr-1"></i> Sample Format
            </a>
            <button type="button" class="btn btn-outline-primary" data-toggle="modal" data-target="#importEmployeeModal" title="Import Employees from Excel/CSV">
                <i class="fas fa-file-upload mr-1"></i> Import
            </button>
            <a href="{{ url('admin/employees/export?' . http_build_query(request()->all())) }}" class="btn btn-outline-success" title="Export Filtered/All Employees to Excel">
                <i class="fas fa-file-excel mr-1"></i> Export
            </a>
            <a href="{{url('admin/employees/create')}}" class="btn ui-btn ui-btn-primary">
                <i class="fas fa-plus mr-1"></i> Add Employee
            </a>
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success mb-4" role="alert">
            {{ session('status') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger mb-4" role="alert">
            {{ session('error') }}
        </div>
    @endif

    <!-- Search & Filter Card -->
    <div class="card-white filter-card">
        <form method="GET" action="{{ url('admin/employees') }}">
            <!-- Row 1 -->
            <div class="row mb-3">
                <!-- Text Search -->
                <div class="col-md-2 mb-2 mb-md-0">
                    <div class="search-input-wrapper h-100 w-100">
                        <i class="fas fa-search"></i>
                        <input type="text" name="search" list="employee_names" class="ui-input-search w-100" value="{{ request('search') }}" placeholder="Search by name, ID..." autocomplete="off">
                        <datalist id="employee_names">
                            @foreach($allEmployees as $emp)
                                <option value="{{ $emp->name }}">{{ $emp->employee_id }}</option>
                            @endforeach
                        </datalist>
                    </div>
                </div>
                <!-- Status Select -->
                <div class="col-md-2 mb-2 mb-md-0">
                    <select class="form-control" name="status" style="height: 100%; min-height: 42px;">
                        <option value=''>All Status</option>
                        <option value='0' @if(request('status') === '0') selected @endif>Open</option>
                        <option value='1' @if(request('status') === '1') selected @endif>Locked</option>
                    </select>
                </div>
                <!-- Department Select -->
                <div class="col-md-2 mb-2 mb-md-0">
                    <select class="form-control" name="department_id" style="height: 100%; min-height: 42px;">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" @if(request('department_id') == $dept->id) selected @endif>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                <!-- Division Select -->
                <div class="col-md-2 mb-2 mb-md-0">
                    <select class="form-control" name="division_id" style="height: 100%; min-height: 42px;">
                        <option value="">All Divisions</option>
                        @foreach($divisions as $div)
                            <option value="{{ $div->id }}" @if(request('division_id') == $div->id) selected @endif>{{ $div->name }}</option>
                        @endforeach
                    </select>
                </div>
                <!-- Designation Select -->
                <div class="col-md-2 mb-2 mb-md-0">
                    <select class="form-control" name="designation_id" style="height: 100%; min-height: 42px;">
                        <option value="">All Designations</option>
                        @foreach($designations as $desig)
                            <option value="{{ $desig->id }}" @if(request('designation_id') == $desig->id) selected @endif>{{ $desig->name }}</option>
                        @endforeach
                    </select>
                </div>
                <!-- Shift Select -->
                <div class="col-md-2 mb-2 mb-md-0">
                    <select class="form-control" name="shift_id" style="height: 100%; min-height: 42px;">
                        <option value="">All Shifts</option>
                        @foreach($shifts as $shift)
                            <option value="{{ $shift->id }}" @if(request('shift_id') == $shift->id) selected @endif>{{ $shift->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            
            <!-- Row 2 -->
            <div class="row align-items-center">
                <!-- Location Select -->
                <div class="col-md-3 mb-2 mb-md-0">
                    <select class="form-control" name="location_id" style="height: 100%; min-height: 42px;">
                        <option value="">All Locations</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" @if(request('location_id') == $loc->id) selected @endif>{{ $loc->name }}</option>
                        @endforeach
                    </select>
                </div>
                <!-- Empty spacing replacing Date Range -->
                <div class="col-md-5 d-flex align-items-center mb-2 mb-md-0">
                    
                </div>
                <!-- Buttons -->
                <div class="col-md-4 d-flex justify-content-md-end align-items-center">
                    <button type="submit" name="action" value="filter" class="btn ui-btn ui-btn-primary mr-2">
                        <i class="fas fa-filter mr-1"></i> Filter
                    </button>
                    <a href="{{ url('admin/employees') }}" class="btn ui-btn btn-light px-3" title="Reset Filters"><i class="fas fa-undo text-secondary"></i></a>
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
                        <th style="width: 50px;">Photo</th>
                        <th style="min-width: 200px;">Employee</th>
                        <th style="min-width: 150px;">Department</th>
                        <th style="min-width: 150px;">Division</th>
                        <th style="min-width: 150px;">Designation</th>
                        <th style="min-width: 120px;">Shift</th>
                        <th style="min-width: 150px;">Work Loc.</th>
                        <th style="min-width: 150px;">Status</th>
                        <th style="min-width: 150px;">Person ID</th>
                        <th style="min-width: 150px;">Face IDs</th>
                        <th style="min-width: 150px;" class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($rows as $row)
                <tr>
                    <td>
                        <div class="rounded bg-light d-flex align-items-center justify-content-center text-muted" style="width:38px; height:38px; border: 1px dashed #cbd5e1;">
                            <i class="fas fa-user text-black-50" style="font-size: 0.9rem;"></i>
                        </div>
                    </td>
                    <td>
                        <div>
                            <div class="font-weight-bold text-dark" style="font-size: 1rem;">{{$row->name}}</div>
                            <div class="text-muted small mt-1">
                                <span class="mr-2"><i class="fas fa-id-badge"></i> {{$row->employee_id}}</span>
                            </div>
                        </div>
                    </td>
                    <td>
                        @if($row->department)
                            <span class="badge badge-info">{{ $row->department->name }}</span>
                        @else
                            <span class="text-muted small">N/A</span>
                        @endif
                    </td>
                    <td>
                        @if($row->division)
                            <span class="badge badge-secondary">{{ $row->division->name }}</span>
                        @else
                            <span class="text-muted small">N/A</span>
                        @endif
                    </td>
                    <td>
                        @if($row->designation)
                            <span class="badge badge-secondary">{{ $row->designation->name }}</span>
                        @else
                            <span class="text-muted small">N/A</span>
                        @endif
                    </td>
                    <td>
                        @if($row->shift)
                            <span class="badge badge-light border">{{ $row->shift->name }}</span>
                        @else
                            <span class="text-muted small">N/A</span>
                        @endif
                    </td>
                    <td>
                        @if($row->location)
                            <span class="text-dark small">{{ $row->location->name }}</span>
                        @else
                            <span class="text-muted small">N/A</span>
                        @endif
                    </td>
                    <td>
                        @if($row->is_locked)
                            <span class="badge badge-secondary"><i class="fas fa-lock mr-1"></i> Locked</span>
                        @else
                            <span class="badge badge-success"><i class="fas fa-lock-open mr-1"></i> Open</span>
                        @endif
                    </td>
                    <td>
                        <div><span class="badge badge-light border">{{$row->person_id ?: 'N/A'}}</span></div>
                    </td>
                    <td>
                        <small class="text-muted">{!!$row->face_ids?implode('<br/>', $row->face_ids):'None'!!}</small>
                    </td>
                    <td>
                        <div class="d-flex justify-content-end align-items-center flex-nowrap">                         
                            @if(!$row->is_salesman)       
                            <a href="{{url('admin/employees/'.$row->id.'/blocks')}}" class="btn btn-light btn-sm mr-1" title="Manage Blocks">
                                <i class="fas fa-ban" style="color: #ea580c;"></i>
                            </a>

                            <a href="{{url('admin/employees/edit/'.$row->id)}}" class="btn btn-light btn-sm" title="Edit">
                                <i class="fas fa-edit text-primary"></i>
                            </a> 
                                
                            <a href="{{url('admin/employees/delete/'.$row->id)}}" 
                               onclick="return confirm('Delete? You have to delete the person ID from Mobile App first to avoid conflict');"
                               class="btn btn-light btn-sm ml-1" title="Delete">
                                <i class="fas fa-trash text-danger"></i>
                            </a> 
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
                @if(count($rows) == 0)
                <tr>
                    <td colspan="11" class="text-center py-5 text-muted">
                        <i class="fas fa-inbox fa-3x mb-3 opacity-50"></i>
                        <h5>No employees found</h5>
                    </td>
                </tr>
                @endif
                </tbody>
            </table>
        </div>
        <div class="p-3 border-top d-flex flex-wrap justify-content-between align-items-center bg-light" style="gap: 12px;">
            <div class="d-flex align-items-center flex-wrap" style="gap: 10px;">
                <span class="text-muted small">
                    Showing {{ $rows->firstItem() ?? 0 }} to {{ $rows->lastItem() ?? 0 }} of {{ $rows->total() }} entries
                </span>
                <div class="d-inline-flex align-items-center ml-sm-3">
                    <label class="text-muted small mb-0 mr-2 font-weight-bold">Show:</label>
                    <select class="custom-select custom-select-sm no-select2" style="width: auto; height: 32px; font-size: 0.85rem;" onchange="changePerPage(this.value)">
                        <option value="25" {{ request('per_page', 25) == 25 ? 'selected' : '' }}>25 rows</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 rows</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100 rows</option>
                    </select>
                </div>
            </div>
            <div>
                {{ $rows->appends(request()->all())->render() }}
            </div>
        </div>
    </div>
</div>

<!-- Import Employee Modal -->
<div class="modal fade" id="importEmployeeModal" tabindex="-1" role="dialog" aria-labelledby="importEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title font-weight-bold" id="importEmployeeModalLabel">
                    <i class="fas fa-file-import mr-1"></i> Import Employees
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ url('admin/employees/import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="alert alert-info py-2 px-3 small mb-3">
                        <i class="fas fa-info-circle mr-1"></i> <strong>Instructions:</strong>
                        <ul class="mb-1 pl-3 mt-1">
                            <li>Supported formats: <strong>.xlsx, .xls, .csv</strong></li>
                            <li>Required columns: <strong>Employee ID</strong> and <strong>Name</strong>.</li>
                            <li>Existing Employee IDs will be updated; new ones will be inserted.</li>
                            <li>Departments, Divisions, Designations, Shifts, and Locations will be automatically matched or created.</li>
                        </ul>
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-dark mb-1">Select File <span class="text-danger">*</span></label>
                        <div class="custom-file">
                            <input type="file" name="file" class="custom-file-input" id="employeeImportFile" accept=".xlsx, .xls, .csv" required onchange="updateFileName(this)">
                            <label class="custom-file-label" for="employeeImportFile" id="employeeImportFileLabel">Choose file (.xlsx, .xls, .csv)...</label>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                        <span class="text-muted small">Need the template?</span>
                        <a href="{{ url('admin/employees/sample-format') }}" class="btn btn-sm btn-link text-decoration-none">
                            <i class="fas fa-download mr-1"></i> Download Sample Format
                        </a>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4" id="btnSubmitImport">
                        <i class="fas fa-upload mr-1"></i> Upload & Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function updateFileName(input) {
    var fileName = input.files && input.files[0] ? input.files[0].name : "Choose file (.xlsx, .xls, .csv)...";
    var label = document.getElementById('employeeImportFileLabel');
    if (label) {
        label.innerText = fileName;
    }
}
</script>
@endsection
