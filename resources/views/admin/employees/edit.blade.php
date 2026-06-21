@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('Update Employee') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" >
                        @csrf

                        <div class="form-group row">
                            <label for="location" class="col-md-4 col-form-label text-md-right">{{ __('Employee ID') }}</label>

                            <div class="col-md-6">
                                <input id="employee_id" type="text" 
                                class="form-control @error('employee_id') is-invalid @enderror" 
                                name="employee_id" value="{{ old('employee_id', $row->employee_id) }}" required autocomplete="employee_id">

                                @error('employee_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('Name') }}</label>

                            <div class="col-md-6">
                                <input id="name" type="text" 
                                class="form-control @error('name') is-invalid @enderror" 
                                name="name" value="{{ old('name', $row->name) }}" required autocomplete="name" autofocus>

                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="department_id" class="col-md-4 col-form-label text-md-right">{{ __('Department') }}</label>

                            <div class="col-md-6">
                                <select id="department_id" name="department_id" class="form-control @error('department_id') is-invalid @enderror">
                                    <option value="">-- Select Department --</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}" {{ old('department_id', $row->department_id) == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                                    @endforeach
                                </select>

                                @error('department_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="designation_id" class="col-md-4 col-form-label text-md-right">{{ __('Designation') }}</label>

                            <div class="col-md-6">
                                <select id="designation_id" name="designation_id" class="form-control @error('designation_id') is-invalid @enderror">
                                    <option value="">-- Select Designation --</option>
                                    @foreach($designations as $desig)
                                        <option value="{{ $desig->id }}" {{ old('designation_id', $row->designation_id) == $desig->id ? 'selected' : '' }}>{{ $desig->name }}</option>
                                    @endforeach
                                </select>

                                @error('designation_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="shift_id" class="col-md-4 col-form-label text-md-right">{{ __('Shift Type') }}</label>

                            <div class="col-md-6">
                                <select id="shift_id" name="shift_id" class="form-control @error('shift_id') is-invalid @enderror">
                                    <option value="">-- Select Shift --</option>
                                    @foreach($shifts as $shift)
                                        <option value="{{ $shift->id }}" {{ old('shift_id', $row->shift_id) == $shift->id ? 'selected' : '' }}>{{ $shift->name }}</option>
                                    @endforeach
                                </select>

                                @error('shift_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="location_id" class="col-md-4 col-form-label text-md-right">{{ __('Location') }}</label>

                            <div class="col-md-6">
                                <select id="location_id" name="location_id" class="form-control @error('location_id') is-invalid @enderror">
                                    <option value="">-- Select Location --</option>
                                    @foreach($locations as $loc)
                                        <option value="{{ $loc->id }}" {{ old('location_id', $row->location_id) == $loc->id ? 'selected' : '' }}>{{ $loc->name }}</option>
                                    @endforeach
                                </select>

                                @error('location_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="id_number" class="col-md-4 col-form-label text-md-right">{{ __('ID Number') }}</label>

                            <div class="col-md-6">
                                <input id="id_number" type="text" class="form-control @error('id_number') is-invalid @enderror" name="id_number" value="{{ old('id_number', $row->id_number) }}" autocomplete="id_number" placeholder="National ID, Passport, etc.">

                                @error('id_number')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-4">
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" 
                                    value="0"
                                    name="is_locked" id="is_locked_0" 
                                    {{ old('is_locked', $row->is_locked) == 0 ? 'checked' : '' }} />

                                    <label class="form-check-label" for="is_locked_0">
                                        {{ __('Open') }}
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="is_locked" 
                                    value="1"
                                    id="is_locked_1" 
                                    {{ old('is_locked', $row->is_locked) == 1 ? 'checked' : '' }} />

                                    <label class="form-check-label" for="is_locked_1">
                                        {{ __('Locked') }}
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Update') }}
                                </button>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
