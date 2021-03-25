@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    {{ __('Employees') }}
                    <a href="{{url('admin/employees/create')}}" class="btn btn-success btn-sm float-right" >Add</a>
                </div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif

                    <table class="table" >
                        <thead>
                            <tr>
                                <th></th>
                                <th>Employee ID</th>
                                <th>Name</th>
                                <th>Person ID</th>
                                <th>Face IDs</th>
                                <th>Locked?</th>
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
                            <td>{{$row->employee_id}}</td>
                            <td>{{$row->name}}</td>
                            <td>{{$row->person_id}}</td>
                            <td>{!!$row->face_ids?implode('<br/>', $row->face_ids):''!!}</td>
                            <td>{{$row->is_locked?'Yes':'No'}}</td>
                            <td>                         
                                @if(!$row->is_salesman)       
                                <a href="{{url('admin/employees/edit/'.$row->id)}}" 
                                    class="btn btn-primary btn-sm m-1" >Edit</a> 
                                    
                                <a href="{{url('admin/employees/delete/'.$row->id)}}" 
                                   onclick="return confirm('Delete? You have to delete the person ID from Mobile App first to avoid conflict');"
                                   class="btn btn-danger btn-sm m-1" >Delete</a> 
                                @endif
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
