<table>
    <thead>
        <tr>
            <th>Date / Period</th>
            <th>Employee ID</th>
            <th>Employee Name</th>
            <th>Department</th>
            <th>Designation</th>
            <th>Shift Type</th>
            <th>Location</th>
            @if(isset($rows[0]) && $rows[0]->view_type == 'daily')
                <th>Check In</th>
                <th>Check Out</th>
            @endif
            <th>Total Hours</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($rows as $row)
        @php $pairsCount = count($row->pairs); @endphp
        @foreach($row->pairs as $index => $pair)
        <tr>
            @if($index === 0)
                <td rowspan="{{ $pairsCount }}">
                    @if($row->view_type == 'monthly')
                        {{ date('F Y', strtotime($row->raw_date)) }}
                    @elseif($row->view_type == 'total')
                        {{ $row->date }}
                    @else
                        {{ date('M d, Y', strtotime($row->raw_date)) }}
                    @endif
                </td>
                <td rowspan="{{ $pairsCount }}">{{ $row->employee->employee_id }}</td>
                <td rowspan="{{ $pairsCount }}">{{ $row->employee->name }}</td>
                <td rowspan="{{ $pairsCount }}">{{ $row->employee->department->name ?? '' }}</td>
                <td rowspan="{{ $pairsCount }}">{{ $row->employee->designation->name ?? '' }}</td>
                <td rowspan="{{ $pairsCount }}">{{ $row->employee->shift->name ?? '' }}</td>
                <td rowspan="{{ $pairsCount }}">{{ $row->employee->location->name ?? '' }}</td>
            @endif
            
            @if($row->view_type == 'daily')
                <td>{{ $pair['in'] ? date('h:i A', strtotime($pair['in'])) : '' }}</td>
                <td>{{ $pair['out'] ? date('h:i A', strtotime($pair['out'])) : '' }}</td>
            @endif
            
            @if($index === 0)
                <td rowspan="{{ $pairsCount }}">{{ $row->formatted_time }}</td>
                <td rowspan="{{ $pairsCount }}">{{ $row->status }}</td>
            @endif
        </tr>
        @endforeach
        @endforeach
        
        @if(isset($grandTotalMinutes))
        <tr>
            <td colspan="{{ (isset($rows[0]) && $rows[0]->view_type == 'daily') ? '9' : '7' }}" style="text-align: right; font-weight: bold;">Grand Total</td>
            <td style="font-weight: bold;">{{ sprintf('%02d:%02d', floor($grandTotalMinutes / 60), $grandTotalMinutes % 60) }}</td>
            <td></td>
        </tr>
        @endif
    </tbody>
</table>
