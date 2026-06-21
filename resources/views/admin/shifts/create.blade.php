@extends('layouts.app')

@section('page-title', 'Add Shift')

@section('content')
<div class="container-fluid pt-2">
    <div class="row">
        <div class="col-md-6">
            <div class="card-white p-4">
                <form action="{{ url('admin/Shifts/create') }}" method="POST">
                    @csrf
                    <div class="form-group mb-4">
                        <label class="font-weight-bold text-dark">Shift Name</label>
                        <input type="text" name="name" class="form-control" style="background-color: #f8fafc; border: 1px solid #e5e7eb;" value="{{ old('name') }}" required>
                        @error('name')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    <button type="submit" class="btn ui-btn ui-btn-primary px-4">
                        <i class="fas fa-save mr-1"></i> Save
                    </button>
                    <a href="{{ url('admin/Shifts') }}" class="btn ui-btn btn-light border ml-2">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
