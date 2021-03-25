@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('Edit User') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" >
                        @csrf

                        <div class="form-group row">
                            <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('Name') }}</label>

                            <div class="col-md-6">
                                <input id="name" type="text" 
                                class="form-control @error('name') is-invalid @enderror" name="name" 
                                value="{{ old('name', $row->name) }}" required autocomplete="name" autofocus>

                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="location" class="col-md-4 col-form-label text-md-right">{{ __('Location') }}</label>

                            <div class="col-md-6">
                                <input id="location" type="text" 
                                class="form-control @error('location') is-invalid @enderror"
                                 name="location" value="{{ old('location', $row->location) }}" required autocomplete="location">

                                @error('location')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="location" class="col-md-4 col-form-label text-md-right">{{ __('Geo Location') }}</label>

                            <div class="col-md-6">
                                <input id="geo_location" type="text" 
                                class="form-control @error('geo_location') is-invalid @enderror" 
                                name="geo_location" value="{{ old('geo_location', $row->geo_location) }}" required autocomplete="geo_location">

                                @error('geo_location')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="username" class="col-md-4 col-form-label text-md-right">{{ __('Username') }}</label>

                            <div class="col-md-6">
                                <input id="username" type="username" 
                                class="form-control @error('username') is-invalid @enderror" 
                                name="username" value="{{ old('username', $row->username) }}" required autocomplete="username">

                                @error('username')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="password" class="col-md-4 col-form-label text-md-right">{{ __('Password') }}</label>

                            <div class="col-md-6">
                                <input id="password" type="password" 
                                class="form-control @error('password') is-invalid @enderror" 
                                name="password" autocomplete="new-password">

                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="password-confirm" class="col-md-4 col-form-label text-md-right">{{ __('Confirm Password') }}</label>

                            <div class="col-md-6">
                                <input id="password-confirm" type="password" class="form-control" 
                                name="password_confirmation" autocomplete="new-password">
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-4">
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" 
                                    value="general"
                                    name="type" id="type_general" 
                                    {{ old('type', $row->type) == 'general' ? 'checked' : '' }} 
                                    disabled />

                                    <label class="form-check-label" for="type_general">
                                        {{ __('General') }}
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="type" 
                                    value="salesman"
                                    id="type_salesman" 
                                    {{ old('type', $row->type) == 'salesman' ? 'checked' : '' }} 
                                    disabled />

                                    <label class="form-check-label" for="type_salesman">
                                        {{ __('Salesman') }}
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row" id="div-salesman" style="display:none" >
                            <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('Employee ID') }}</label>

                                <select id="employee_id"
                                class="form-control @error('employee_id') is-invalid @enderror" name="employee_id" 
                                value="{{ old('employee_id') }}" required>
                                    <option value="" >Select</option>
                                    @foreach($employee_ids as $eid)
                                    <option>{{$eid}}</option>
                                    @endforeach
                                </select>

                                @error('employee_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
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


@push('scripts')
<script>
$(function(){

    @if(($eid = old('employee_id', $row->employee_id)))
    $("#employee_id").val("{{$eid}}");
    @endif

    $("#type_general,#type_salesman").change(function(){
        if($("#type_salesman").is(':checked'))
        {
            $("#employee_id").prop('required', true);
            $("#div-salesman").show();
        }
        else
        {
            $("#employee_id").prop('required', false);
            $("#div-salesman").hide();
        }
    }).change();
});
</script>
@endpush
