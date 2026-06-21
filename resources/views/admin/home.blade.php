@extends('layouts.app')

@section('page-title', 'Dashboard')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card-white p-4">
            @if (session('status'))
                <div class="alert alert-success" role="alert">
                    {{ session('status') }}
                </div>
            @endif

            <h4 class="mb-3 text-dark font-weight-bold">Welcome Back, {{ Auth::user()->name }}!</h4>
            <p class="text-muted mb-0">Select an option from the sidebar to manage attendance, employees, or reports.</p>
        </div>
    </div>
</div>
@endsection
