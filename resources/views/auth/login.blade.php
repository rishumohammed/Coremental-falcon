@extends('layouts.app')

@section('content')
<div class="container d-flex align-items-center justify-content-center" style="min-height: 80vh;">
    <div class="col-md-8 col-lg-5">
        <div class="card border-0 shadow-sm" style="border-radius: 12px;">
            <div class="card-body p-5">
                <div class="text-center mb-4">
                    <h4 class="font-weight-bold text-dark mb-1">{{ __('Welcome Back') }}</h4>
                    <p class="text-muted small">Please enter your credentials to login</p>
                </div>

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="form-group mb-3">
                        <label for="username" class="font-weight-bold text-secondary small">{{ __('Username') }}</label>
                        <input id="username" type="text" class="form-control form-control-lg bg-light border-0 @error('username') is-invalid @enderror" name="username" value="{{ old('username') }}" required autocomplete="username" autofocus style="border-radius: 8px;">

                        @error('username')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group mb-4">
                        <label for="password" class="font-weight-bold text-secondary small">{{ __('Password') }}</label>
                        <input id="password" type="password" class="form-control form-control-lg bg-light border-0 @error('password') is-invalid @enderror" name="password" required autocomplete="current-password" style="border-radius: 8px;">

                        @error('password')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label text-muted small" for="remember">
                                {{ __('Remember Me') }}
                            </label>
                        </div>
                        @if (Route::has('password.request'))
                            <a class="text-primary small text-decoration-none font-weight-bold" href="{{ route('password.request') }}">
                                {{ __('Forgot Password?') }}
                            </a>
                        @endif
                    </div>

                    <button type="submit" class="btn btn-primary btn-block btn-lg font-weight-bold" style="border-radius: 8px; background-color: #2563eb; border-color: #2563eb;">
                        {{ __('Login') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
