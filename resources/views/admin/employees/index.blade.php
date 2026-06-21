@extends('layouts.app')

@section('page-title', 'Employee Catalog')

@section('content')
<div class="container-fluid pt-2">
    <!-- Header Area -->
    <div class="d-flex justify-content-end align-items-center mb-3">
        <div>
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
        <form class="filter-bar" method="GET" action="{{ url('admin/employees') }}">
            <div class="search-input-wrapper">
                <i class="fas fa-search"></i>
                <input type="text" name="search" list="employee_names" class="ui-input-search" value="{{ request('search') }}" placeholder="Search by name, ID, or department..." autocomplete="off">
                <datalist id="employee_names">
                    @foreach($allEmployees as $emp)
                        <option value="{{ $emp->name }}">{{ $emp->employee_id }}</option>
                    @endforeach
                </datalist>
            </div>
            <div class="filter-selects">
                <select name="department_id" class="ui-select" onchange="this.form.submit()">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </select>
                <select name="status" class="ui-select" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Active</option>
                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Locked</option>
                </select>
                <a href="{{ url('admin/employees') }}" class="btn ui-btn btn-light px-3" title="Reset Filters"><i class="fas fa-undo text-secondary"></i></a>
                <button type="submit" class="btn ui-btn ui-btn-primary d-none">Search</button>
            </div>
        </form>
    </div>

    <!-- Data Table Card -->
    <div class="card-white p-0 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-ui mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Employee</th>
                        <th>Person ID</th>
                        <th>Face IDs</th>
                        <th>Locked?</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @php
                $i=($rows->currentPage()-1)*$rows->perPage()+1;
                @endphp
                @foreach($rows as $row)
                <tr>
                    <td><span class="text-muted">{{$i++}}</span></td>
                    <td>
                        <div class="font-weight-bold text-dark">{{$row->name}}</div>
                        <div class="text-muted small">
                            ID: {{$row->employee_id}}
                            @if($row->department)
                                <span class="badge badge-info ml-2">{{$row->department->name}}</span>
                            @endif
                        </div>
                    </td>
                    <td><span class="badge badge-light">{{$row->person_id}}</span></td>
                    <td><small class="text-muted">{!!$row->face_ids?implode('<br/>', $row->face_ids):'None'!!}</small></td>
                    <td>
                        @if($row->is_locked)
                            <span class="badge badge-danger">Yes</span>
                        @else
                            <span class="badge badge-success">No</span>
                        @endif
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
                    <td colspan="7" class="text-center py-5 text-muted">
                        <i class="fas fa-inbox fa-3x mb-3 opacity-50"></i>
                        <h5>No employees found</h5>
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
