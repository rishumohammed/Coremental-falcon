@extends('layouts.app')

@section('page-title', 'Manual Entry')

@section('content')
<div class="container-fluid pt-2">
    @if (session('status'))
        <div class="alert alert-success mb-4" role="alert">
            {{ session('status') }}
        </div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger mb-4">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card-white p-4 shadow-sm border-0" style="border-radius: 12px;">
                <h4 class="mb-4 font-weight-bold text-dark border-bottom pb-3"><i class="fas fa-sign-in-alt text-primary mr-2"></i>Add Manual Check-In</h4>
                
                <form action="{{ url('admin/reports/manual-checkin') }}" method="POST">
                    @csrf
                    
                    <div class="form-group mb-4">
                        <label class="font-weight-bold text-dark small mb-2">Select Employee <span class="text-danger">*</span></label>
                        <select name="employee_id" class="form-control" required style="width: 100%;">
                            <option value="">-- Search by Name or ID --</option>
                            @foreach($allEmployees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->employee_id }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-4">
                                <label class="font-weight-bold text-dark small mb-2">Date <span class="text-danger">*</span></label>
                                <input type="date" name="date" class="form-control" required value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-4">
                                <label class="font-weight-bold text-dark small mb-2">Check-In Time <span class="text-danger">*</span></label>
                                <input type="time" name="time" class="form-control" required value="09:00">
                            </div>
                        </div>
                    </div>

                    <div class="form-group mt-3 mb-0 text-right">
                        <button type="submit" class="btn ui-btn ui-btn-primary px-4 py-2">
                            <i class="fas fa-save mr-2"></i> Save Check-In
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
