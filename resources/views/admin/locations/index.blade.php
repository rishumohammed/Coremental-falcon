@extends('layouts.app')

@section('page-title', 'Manage Locations')

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
            <h5 class="mb-0 text-dark font-weight-bold">Locations</h5>
        </div>
        <a href="{{ url('admin/Locations/create') }}" class="btn ui-btn ui-btn-primary">
            <i class="fas fa-plus mr-1"></i> Add Location
        </a>
    </div>

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
                        <td class="font-weight-bold text-dark align-middle">{{$row->name}}</td>
                        <td>
                            <div class="d-flex justify-content-center align-items-center">
                                <a href="{{ url('admin/Locations/edit/'.$row->id) }}" class="btn btn-sm btn-light border mr-2" title="Edit">
                                    <i class="fas fa-edit text-primary"></i>
                                </a>
                                <a href="{{ url('admin/Locations/delete/'.$row->id) }}" class="btn btn-sm btn-light border" title="Delete" onclick="return confirm('Are you sure you want to delete this Location?')">
                                    <i class="fas fa-trash text-danger"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                    @if(count($rows) == 0)
                    <tr>
                        <td colspan="2" class="text-center py-4 text-muted">No Locations found.</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
