@extends('layouts.app')

@section('page-title', 'Assigned Employees - '.$user->name)

@section('content')
<div class="container-fluid pt-2">
    <!-- Header Area -->
    <div class="d-flex justify-content-end align-items-center mb-3">
        <div>
            <a href="{{url('admin/users')}}" class="btn ui-btn btn-light border">
                <i class="fas fa-arrow-left mr-1"></i> Back to Users
            </a>
        </div>
    </div>

    @if (session('status'))
    <div class="alert alert-success mb-4" role="alert">
        {{ session('status') }}
    </div>
    @endif

    <!-- Assign Form Card -->
    <div class="card-white mb-4">
        <h5 class="font-weight-bold mb-3 text-dark">Assign New Employee</h5>
        <form method="post">
            @csrf     
            <div class="row align-items-end" >
                <div class="col-md-9">                   
                    <select class="form-control mr-2 no-select2" required name="employee_ids[]" id="employee_ids" multiple >
                        @foreach($unassigned_employees as $erow)
                        <option value="{{$erow->id}}">{{$erow->name}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">                   
                    <button type="submit" class="btn ui-btn ui-btn-primary w-100">
                        <i class="fas fa-link mr-1"></i> Assign Employees
                    </button>
                </div>
            </div>                            
        </form>
    </div>

    <!-- Data Table Card -->
    <div class="card-white filter-card mb-3">
        <form class="filter-bar" method="GET" action="{{ url('admin/users/'.$user->id.'/assigned-employees') }}">
            <div class="search-input-wrapper flex-grow-1">
                <i class="fas fa-search"></i>
                <input type="text" name="search" class="ui-input-search" value="{{ request('search') }}" placeholder="Search assigned employees by name or ID...">
            </div>
            <div class="filter-selects ml-2">
                <a href="{{ url('admin/users/'.$user->id.'/assigned-employees') }}" class="btn ui-btn btn-light px-3" title="Reset Filters"><i class="fas fa-undo text-secondary"></i></a>
            </div>
            <button type="submit" class="btn ui-btn ui-btn-primary d-none">Search</button>
        </form>
    </div>

    <div class="card-white p-0 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-ui mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Employee ID</th>
                        <th>Name</th>
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
                        <td class="font-weight-bold">{{$row->employee_id}}</td>
                        <td>{{$row->name}}</td>
                        <td class="text-right">
                            <a href="{{url('admin/users/'.$user->id.'/assigned-employees/unassign/'.$row->id)}}"
                                class="btn btn-light btn-sm" title="Remove">
                                <i class="fas fa-unlink text-danger"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                    @if(count($rows) == 0)
                    <tr>
                        <td colspan="4" class="text-center py-5 text-muted">
                            <i class="fas fa-user-minus fa-3x mb-3 opacity-50"></i>
                            <h5>No employees assigned</h5>
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
<div class="modal" id="modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                Assign New Employee
            </div>
            <div class="modal-body">
                Assign New Employee
            </div>
            <div class="modal-header">
                Assign New Employee
            </div>
        </div>
    </div>
</div>
@endsection
@push('head')
<link href="{{asset('vendor/multiselect/multiselect.css')}}" rel="stylesheet" />
<script src="{{asset('vendor/multiselect/multiselect.min.js')}}" ></script>
<style>
    /* example of setting the width for multiselect */
    #employee_ids_multiSelect {
        width: 100%;
    }
</style>
@endpush
@push('scripts')
<script>
document.multiselect('#employee_ids');
</script>
@endpush