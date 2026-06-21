@extends('layouts.app')

@section('page-title', 'Manage Leave Types')

@section('content')
<div class="container-fluid pt-2">

    @if (session('status'))
        <div class="alert alert-success mb-4" role="alert">
            {{ session('status') }}
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="d-flex align-items-center">
            <a href="{{ url('admin/settings') }}" class="btn btn-light border mr-3 px-3" title="Back to Settings">
                <i class="fas fa-arrow-left text-secondary"></i>
            </a>
            <h5 class="mb-0 text-dark font-weight-bold">Leave Types</h5>
        </div>
        <a href="{{ url('admin/leave-types/create') }}" class="btn ui-btn ui-btn-primary">
            <i class="fas fa-plus mr-1"></i> Add Leave Type
        </a>
    </div>

    <!-- Data Table Card -->
    <div class="card-white p-0 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-ui mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th style="width: 150px" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>                        
                    @foreach($rows as $row)
                    <tr>
                        <td class="align-middle">
                            <div class="font-weight-bold text-dark">{{$row->name}}</div>
                            @if($row->is_default)
                                <span class="badge badge-success mt-1">Default</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex justify-content-center align-items-center">
                                @if(!$row->is_default)
                                <a href="{{ url('admin/leave-types/set-default/'.$row->id) }}" class="btn btn-sm btn-light border mr-2" title="Set as Default">
                                    <i class="fas fa-star text-warning"></i>
                                </a>
                                @endif
                                <a href="{{ url('admin/leave-types/edit/'.$row->id) }}" class="btn btn-sm btn-light border mr-2" title="Edit">
                                    <i class="fas fa-edit text-primary"></i>
                                </a>
                                <a href="{{ url('admin/leave-types/delete/'.$row->id) }}" class="btn btn-sm btn-light border" title="Delete" onclick="return confirm('Are you sure you want to delete this leave type?')">
                                    <i class="fas fa-trash text-danger"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                    @if(count($rows) == 0)
                    <tr>
                        <td colspan="2" class="text-center py-4 text-muted">No leave types found.</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
