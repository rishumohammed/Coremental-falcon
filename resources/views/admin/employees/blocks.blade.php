@extends('layouts.app')

@section('page-title', 'Manage Blocks: ' . $row->name)

@section('content')
<div class="container-fluid pt-2">
    @if (session('status'))
        <div class="alert alert-success" role="alert">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card-white p-4">
                <h5 class="font-weight-bold text-dark mb-4">Add New Block</h5>
                <form action="{{ url('admin/employees/'.$row->id.'/blocks') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label>Leave Type (Reason)</label>
                        <select name="leave_type_id" class="form-control" required>
                            <option value="">Select Reason</option>
                            @foreach($leaveTypes as $leave)
                                <option value="{{ $leave->id }}">{{ $leave->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Start Date</label>
                        <input type="date" name="start_date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>End Date</label>
                        <input type="date" name="end_date" class="form-control" required>
                    </div>
                    <button type="submit" class="btn ui-btn ui-btn-primary w-100 mt-2">
                        <i class="fas fa-lock mr-1"></i> Block Employee
                    </button>
                </form>
            </div>
            
            <a href="{{ url('admin/employees') }}" class="btn ui-btn btn-light border w-100 mt-3">
                <i class="fas fa-arrow-left mr-1"></i> Back to Employees
            </a>
        </div>

        <div class="col-md-8">
            <div class="card-white p-0">
                <div class="p-4 border-bottom">
                    <h5 class="font-weight-bold text-dark mb-0">Active & Past Blocks</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Reason</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Status</th>
                                <th class="text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($blocks as $block)
                                <tr>
                                    <td>{{ $block->leaveType->name ?? 'Unknown' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($block->start_date)->format('M d, Y') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($block->end_date)->format('M d, Y') }}</td>
                                    <td>
                                        @if(today()->between(\Carbon\Carbon::parse($block->start_date), \Carbon\Carbon::parse($block->end_date)))
                                            <span class="badge badge-danger">Active</span>
                                        @elseif(today()->lt(\Carbon\Carbon::parse($block->start_date)))
                                            <span class="badge badge-warning">Upcoming</span>
                                        @else
                                            <span class="badge badge-secondary">Past</span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <a href="{{ url('admin/blocks/delete/'.$block->id) }}" 
                                           onclick="return confirm('Are you sure you want to remove this block?');"
                                           class="btn btn-light btn-sm" title="Remove Block">
                                            <i class="fas fa-trash text-danger"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">No blocks found for this employee.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
