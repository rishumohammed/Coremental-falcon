<table>
    <thead>
        <tr>
            <th></th>
            <th>Salesman</th>                                
            <th>Type</th>
            <th>Entry Type</th>                          
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
    $i=1;
    @endphp
    @foreach($rows as $row)
    <tr>
        <td>{{$i++}}</td>
        <td>{{$row->salesman->employee_id.' - '.$row->salesman->name}}</td>                            
        <td>{{$row->type_label}}</td>                            
        <td>{{$row->entry_type_label}}</td> 
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