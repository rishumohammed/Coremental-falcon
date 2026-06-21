<table>
    <thead>
        <tr>
            <th></th>
            <th>Login</th>     
            <th>Employee ID</th>
            <th>Employee Name</th>                                
            <th>Department</th>
            <th>Designation</th>
            <th>Shift Type</th>
            <th>Work Location</th>
            <th>Type</th>
            <th>Entry Type</th>
            <th>Location</th>
            <th>Address</th>
            <th>Device</th>
            <th>Date</th>
            <th>Time</th>                               
        </tr>
    </thead>
    <tbody>
    @php
    $i=1;
    @endphp
    @foreach($rows as $row)
    <tr>
        <td>{{$i++}}</td>
        <td>{{$row->user->username ?? ''}}</td>
        <td>{{$row->employee->employee_id}}</td>
        <td>{{$row->employee->name}}</td>                            
        <td>{{$row->employee->department->name ?? ''}}</td>
        <td>{{$row->employee->designation->name ?? ''}}</td>
        <td>{{$row->employee->shift->name ?? ''}}</td>
        <td>{{$row->employee->location->name ?? ''}}</td>
        <td>{{$row->type_label}}</td>    
        <td>{{$row->entry_type_label}}</td>  o
        <td>
            <a href="https://maps.google.com?q={{urlencode($row->lat.','.$row->lng)}}">{{$row->lat.','.$row->lng}}</a>
        </td>
        <td>
            <a href="https://maps.google.com?q={{urlencode($row->address)}}">{{$row->address}}</a>
        </td>
        <td>{{$row->device}}</td>
        <td>{{date('d-m-Y', strtotime($row->created_at->timezone(timezone())))}}</td>
        <td>{{date('H:i:s', strtotime($row->created_at->timezone(timezone())))}}</td>
    </tr>
    @endforeach
    </tbody>
</table>