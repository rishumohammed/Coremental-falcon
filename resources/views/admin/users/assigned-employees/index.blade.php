@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div>
                        {{$user->name}} / {{ __('Assigned Employees') }}
                    </div>
                    <div>                        
                        <form method="post">
                            @csrf     
                            <div class="row" >
                                <div class="col-md-10">                   
                                    <select class="form-control mr-2 no-select2" required name="employee_ids[]" 
                                        id="employee_ids" multiple >
                                        @foreach($unassigned_employees as $erow)
                                        <option value="{{$erow->id}}">{{$erow->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">                   
                                    <button type="submit" class="btn btn-success">Assign Employees</button>
                                </div>
                            </div>                            
                        </form>
                    </div>
                </div>

                <div class="card-body">
                    @if (session('status'))
                    <div class="alert alert-success" role="alert">
                        {{ session('status') }}
                    </div>
                    @endif

                    <table class="table">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Employee ID</th>
                                <th>Name</th>
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
                                <td>
                                    <a href="{{url('admin/users/'.$user->id.'/assigned-employees/unassign/'.$row->id)}}"
                                        class="btn btn-primary btn-sm">Delete</a>
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