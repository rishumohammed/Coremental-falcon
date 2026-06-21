<table>
    <thead>
        <tr>
            <th>Employee Name</th>
            <th>Employee ID</th>
            <th>Absent Date</th>
            <th>Leave Type</th>
        </tr>
    </thead>
    <tbody>
        @foreach($rows as $row)
        <tr>
            <td>{{$row->employee->name}}</td>
            <td>{{$row->employee->employee_id}}</td>
            <td>{{date('d M, Y', strtotime($row->date))}}</td>
            <td>{{$row->leave_type_name}}</td>
        </tr>
        @endforeach
    </tbody>
</table>
