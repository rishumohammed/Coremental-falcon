<table>
    <thead>
        <tr>
            <th>Date</th>
            <th>Employee ID</th>
            <th>Name</th>
            <th>Department</th>
            <th>Check In Time</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($rows as $row)
        <tr>
            <td>{{ date('M d, Y', strtotime($row->date)) }}</td>
            <td>{{ $row->employee->employee_id }}</td>
            <td>{{ $row->employee->name }}</td>
            <td>{{ $row->employee->department->name ?? 'N/A' }}</td>
            <td>{{ date('h:i A', strtotime($row->check_in_time)) }}</td>
            <td>Missing Checkout</td>
        </tr>
        @endforeach
    </tbody>
</table>
