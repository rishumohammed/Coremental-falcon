@extends('layouts.app')

@section('page-title', 'Absent Report')

@section('content')

<div class="row">
    <div class="col-md-12">
        <div class="card-white p-3 mb-4">
            <form method="GET">
                <div class="d-flex flex-column flex-md-row mb-3">
                    <!-- Text Search -->
                    <div class="search-input-wrapper flex-grow-1 mr-md-3 mb-2 mb-md-0">
                        <i class="fas fa-search"></i>
                        <input type="text" name="search" list="employee_names" class="ui-input-search" value="{{ request('search') }}" placeholder="Search employee name or ID..." autocomplete="off">
                        <datalist id="employee_names">
                            @foreach($allEmployees as $emp)
                                <option value="{{ $emp->name }}">{{ $emp->employee_id }}</option>
                            @endforeach
                        </datalist>
                    </div>
                </div>
                
                <div class="d-flex align-items-center justify-content-between flex-wrap">
                    <div class="d-flex align-items-center flex-wrap">
                        <div class="mr-3 d-flex align-items-center mb-2 mb-md-0">
                            <span class="text-muted mr-2 small font-weight-bold text-uppercase">From:</span>
                            <input type="date" class="ui-select bg-white" name="from_date" value="{{ request('from_date') ?: date('Y-m-d', strtotime('-1 day')) }}" />
                        </div>
                        <div class="mr-3 d-flex align-items-center mb-2 mb-md-0">
                            <span class="text-muted mr-2 small font-weight-bold text-uppercase">To:</span>
                            <input type="date" class="ui-select bg-white" name="to_date" value="{{ request('to_date') ?: date('Y-m-d', strtotime('-1 day')) }}" />
                        </div>
                    </div>
                    <div class="d-flex align-items-center mt-2 mt-md-0">
                        <button type="submit" name="action" value="filter" class="btn ui-btn ui-btn-primary mr-2">
                            <i class="fas fa-filter mr-1"></i> Filter
                        </button>
                        <a href="{{ url('admin/reports/absent') }}" class="btn ui-btn btn-light px-3 mr-2" title="Reset Filters"><i class="fas fa-undo text-secondary"></i></a>
                        <button type="submit" name="export" value="1" class="btn ui-btn btn-light border">
                            <i class="fas fa-file-export mr-1"></i> Export
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Data Table Card -->
    <div class="col-md-12">
        <div class="card-white p-0 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-ui mb-0">
                    <thead>
                        <tr>
                            <th style="width: 35%">Employee</th>
                            <th style="width: 20%">Employee ID</th>
                            <th style="width: 20%">Absent Date</th>
                            <th style="width: 25%">Assign Leave Type</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($paginatedRows as $row)
                    <tr>
                        <td>
                            <div>
                                <div class="font-weight-bold text-dark" style="font-size: 1rem;">{{$row->employee->name}}</div>
                            </div>
                        </td>
                        <td>
                            <div class="text-muted">
                                <span class="mr-2"><i class="fas fa-id-badge"></i> {{$row->employee->employee_id}}</span>
                            </div>
                        </td>
                        <td>
                            <div class="font-weight-bold text-danger"><i class="far fa-calendar-times mr-1"></i> {{date('d M, Y', strtotime($row->date))}}</div>
                        </td>
                        <td>
                            <select class="form-control leave-type-select" data-emp-id="{{$row->employee->id}}" data-date="{{$row->date}}" style="background-color: #f8fafc; border: 1px solid #e5e7eb;">
                                <option value="">-- No Leave Assigned --</option>
                                @foreach($leaveTypes as $lt)
                                    <option value="{{$lt->id}}" @if($row->leave_type_id == $lt->id) selected @endif>{{$lt->name}}</option>
                                @endforeach
                            </select>
                        </td>
                    </tr>
                    @endforeach
                    @if(count($paginatedRows) == 0)
                    <tr>
                        <td colspan="4" class="text-center py-5 text-muted">
                            <i class="fas fa-check-circle text-success fa-3x mb-3 opacity-50"></i>
                            <h5>No absences found for this date range.</h5>
                        </td>
                    </tr>
                    @endif
                    </tbody>
                </table>
            </div>
            
            @if(count($paginatedRows) > 0)
            <div class="p-3 border-top">
                {{ $paginatedRows->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('.leave-type-select').on('change', function() {
        var empId = $(this).data('emp-id');
        var date = $(this).data('date');
        var leaveTypeId = $(this).val();
        var originalBg = $(this).css('background-color');
        var selectEl = $(this);

        selectEl.css('background-color', '#fffbeb'); // yellow flash

        $.ajax({
            url: "{{ url('admin/reports/assign-leave') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                employee_id: empId,
                date: date,
                leave_type_id: leaveTypeId
            },
            success: function(response) {
                if (leaveTypeId) {
                    selectEl.css('background-color', '#ecfdf5'); // green flash
                } else {
                    selectEl.css('background-color', originalBg);
                }
            },
            error: function() {
                alert('An error occurred while saving the leave type. Please try again.');
                selectEl.css('background-color', '#fef2f2'); // red flash
            }
        });
    });
});
</script>
@endpush
@endsection
