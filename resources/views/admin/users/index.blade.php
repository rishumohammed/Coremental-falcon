@extends('layouts.app')

@section('page-title', 'User Management')

@section('content')
<div class="container-fluid pt-2">
    <!-- Header Area -->
    <div class="d-flex justify-content-end align-items-center mb-3">
        <div>
            <a href="{{url('admin/users/create')}}" class="btn ui-btn ui-btn-primary">
                <i class="fas fa-plus mr-1"></i> Add User
            </a>
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success mb-4" role="alert">
            {{ session('status') }}
        </div>
    @endif

    <!-- Search & Filter Card -->
    <div class="card-white filter-card">
        <form class="filter-bar" method="GET" action="{{ url('admin/users') }}">
            <div class="search-input-wrapper">
                <i class="fas fa-search"></i>
                <input type="text" name="search" class="ui-input-search" value="{{ request('search') }}" placeholder="Search users by name, username...">
            </div>
            <div class="filter-selects">
                <select name="role" class="ui-select" onchange="this.form.submit()">
                    <option value="">All Roles</option>
                    @foreach($roles as $r)
                        <option value="{{ $r }}" {{ request('role') == $r ? 'selected' : '' }}>{{ ucfirst($r) }}</option>
                    @endforeach
                </select>
                <select name="location" class="ui-select" onchange="this.form.submit()">
                    <option value="">All Locations</option>
                    @foreach($locations as $l)
                        <option value="{{ $l }}" {{ request('location') == $l ? 'selected' : '' }}>{{ $l }}</option>
                    @endforeach
                </select>
                <a href="{{ url('admin/users') }}" class="btn ui-btn btn-light px-3" title="Reset Filters"><i class="fas fa-undo text-secondary"></i></a>
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
                        <th>Type</th>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Location</th>
                        <th>Geo Location</th>
                        <th class="d-none">Group ID</th>
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
                        @if($row->type == 'admin')
                            <span class="badge badge-primary px-2 py-1">Admin</span>
                        @else
                            <span class="badge badge-secondary px-2 py-1">{{ ucfirst($row->type) }}</span>
                        @endif
                    </td>
                    <td class="font-weight-bold">{{$row->name}}</td>
                    <td>{{$row->username}}</td>                            
                    <td>{{$row->location}}</td>
                    <td><small class="text-muted">{{$row->geo_location}}</small></td>
                    <td class="d-none">{{$row->group_id}}</td>
                    <td>
                        <div class="d-flex justify-content-end align-items-center flex-nowrap">
                            <a href="{{url('admin/users/edit/'.$row->id)}}" class="btn btn-light btn-sm" title="Edit">
                                <i class="fas fa-edit text-primary"></i>
                            </a> 
                            <a href="{{url('admin/users/'.$row->id.'/assigned-employees')}}" class="btn btn-light btn-sm mx-1" title="Assigned Employees">
                                <i class="fas fa-users text-info"></i>
                            </a> 
                            <a href="{{url('admin/users/delete/'.$row->id)}}" 
                               onclick="return confirm('Delete?');"
                               class="btn btn-light btn-sm" title="Delete">
                                <i class="fas fa-trash text-danger"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @endforeach
                @if(count($rows) == 0)
                <tr>
                    <td colspan="8" class="text-center py-5 text-muted">
                        <i class="fas fa-user-slash fa-3x mb-3 opacity-50"></i>
                        <h5>No users found</h5>
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
