@extends('layouts.app')

@section('page-title', 'Edit Designation')

@section('content')
<div class="container-fluid pt-2">
        <div class="d-flex align-items-center mb-3">
        <a href="{{ url('admin/designations') }}" class="btn btn-light border mr-3 px-3" title="Back to Designations">
            <i class="fas fa-arrow-left text-secondary"></i>
        </a>
        <h5 class="mb-0 text-dark font-weight-bold">Edit Designation</h5>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="card-white p-4">
                <form action="{{ url('admin/designations/edit/'.$designation->id) }}" method="POST">
                    @csrf
                    <div class="form-group mb-4">
                        <label class="font-weight-bold text-dark">Designation Name</label>
                        <input type="text" name="name" class="form-control" style="background-color: #f8fafc; border: 1px solid #e5e7eb;" value="{{ old('name', $designation->name) }}" required>
                        @error('name')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    <button type="submit" class="btn ui-btn ui-btn-primary px-4">
                        <i class="fas fa-save mr-1"></i> Update
                    </button>
                    <a href="{{ url('admin/designations') }}" class="btn ui-btn btn-light border ml-2">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

