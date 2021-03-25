@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    {{ __('Saleman Attendance') }}
                </div>

                <div class="card-body" style="overflow:auto;">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <table class="table" >
                        <thead>
                            <tr>
                                <th></th>
                                <th>Salesman</th>                                
                                <th>Type</th>
                                <th>Entry Type</th>
                                <th>Photo</th>                                
                                <th>Location</th>
                                <th>Address</th>
                                <th>Device</th>
                                <th>Customer</th>
                                <th>Purpose</th>
                                <th>Meeting Notes</th>
                                <th>Date</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                        @php
                        $i=($rows->currentPage()-1)*$rows->perPage()+1;
                        @endphp
                        @foreach($rows as $row)
                        <tr>
                            <td>{{$i++}}</td>
                            <td>{{$row->salesman->employee_id.' - '.$row->salesman->name}}</td>                            
                            <td>{{$row->type_label}}</td>                            
                            <td>{{$row->entry_type_label}}</td>                            
                            <td>
                                @if($row->photo)
                                <img src="{{$row->photo_url}}" style="width:100px;" />
                                @endif
                            </td>
                            <td>
                                <a href="https://maps.google.com?q={{$row->lat.','.$row->lng}}">{{$row->lat.','.$row->lng}}</a>
                            </td>
                            <td>
                                <a href="https://maps.google.com?q={{$row->address}}">{{$row->address}}</a>
                            </td>
                            <td>{{$row->device}}</td>
                            <td>{{$row->customer_name}}</td>
                            <td>{{$row->purpose}}</td>
                            <td>{{$row->meeting_notes}}</td>
                            <td>{{date('Y-m-d', strtotime($row->created_at->timezone(timezone())))}}</td>
                            <td>{{date('H:i:s', strtotime($row->created_at->timezone(timezone())))}}</td>
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
