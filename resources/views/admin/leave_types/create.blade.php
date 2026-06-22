@extends('layouts.app')

@section('page-title', 'Add Leave Type')

@section('content')
<div class="container-fluid pt-2">
        <div class="d-flex align-items-center mb-3">
        <a href="{{ url('admin/leave-types') }}" class="btn btn-light border mr-3 px-3" title="Back to Leave Types">
            <i class="fas fa-arrow-left text-secondary"></i>
        </a>
        <h5 class="mb-0 text-dark font-weight-bold">Add Leave Type</h5>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="card-white p-4">
                <form action="{{ url('admin/leave-types/create') }}" method="POST">
                    @csrf
                    <div class="form-group mb-4">
                        <label class="font-weight-bold text-dark">Leave Type Name</label>
                        <input type="text" name="name" class="form-control" style="background-color: #f8fafc; border: 1px solid #e5e7eb;" required placeholder="e.g. Sick Leave">
                    </div>
                    <button type="submit" class="btn ui-btn ui-btn-primary px-4">
                        <i class="fas fa-save mr-1"></i> Save
                    </button>
                    <a href="{{ url('admin/leave-types') }}" class="btn ui-btn btn-light border ml-2">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

