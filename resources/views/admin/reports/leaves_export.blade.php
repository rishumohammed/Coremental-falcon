<table>
    <thead>
        <tr>
            <th>Employee ID</th>
            <th>Name</th>
            <th>Department</th>
            @foreach($leaveTypes as $lt)
                <th>{{ $lt->name }}</th>
            @endforeach
            <th>Absent (Unassigned)</th>
            <th>Total Leaves</th>
        </tr>
    </thead>
    <tbody>
        @foreach($rows as $row)
        <tr>
            <td>{{ $row['employee']->employee_id }}</td>
            <td>{{ $row['employee']->name }}</td>
            <td>{{ $row['employee']->department->name ?? 'N/A' }}</td>
            @foreach($leaveTypes as $lt)
                <td>{{ $row['breakdown'][$lt->name] }}</td>
            @endforeach
            <td>{{ $row['breakdown']['Absent'] }}</td>
            <td>{{ $row['total_leaves'] }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
