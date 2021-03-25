@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    {{ __('Users') }}
                    <a href="{{url('admin/users/create')}}" class="btn btn-success btn-sm float-right" >Add</a>
                </div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <table class="table" >
                        <thead>
                            <tr>
                                <th></th>
                                <th>Type</th>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Location</th>
                                <th>Geo Location</th>
                                <th class='d-none' >Group ID</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        @php
                        $i=($rows->currentPage()-1)*$rows->perPage()+1;
                        @endphp
                        @foreach($rows as $row)
                        <tr>
                            <td>{{$i++}}</td>
                            <td>{{$row->type}}</td>
                            <td>{{$row->name}}</td>
                            <td>{{$row->username}}</td>                            
                            <td>{{$row->location}}</td>
                            <td>{{$row->geo_location}}</td>
                            <td class='d-none' >{{$row->group_id}}</td>
                            <td>
                                <a href="{{url('admin/users/edit/'.$row->id)}}" class="btn btn-primary btn-sm m-1" >Edit</a> 
                                <a href="{{url('admin/users/'.$row->id.'/assigned-employees')}}" class="btn btn-secondary btn-sm m-1" >Assigned Employees</a> 
                                <a href="{{url('admin/users/delete/'.$row->id)}}" 
                                   onclick="return confirm('Delete?');"
                                   class="btn btn-danger btn-sm m-1" >Delete</a> 
                            </td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                    {{$rows->render()}}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
