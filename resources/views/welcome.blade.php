@extends('User.layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header">{{ __('เข้าสู่ระบบ') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('user.login') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="email" class="form-label">{{ __('อีเมล') }}</label>
                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" 
                                   name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">{{ __('รหัสผ่าน') }}</label>
                            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" 
                                   name="password" required autocomplete="current-password">
                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remember" 
                                       id="remember" {{ old('remember') ? 'checked' : '' }}>
                                <label class="form-check-label" for="remember">
                                    {{ __('จดจำฉัน') }}
                                </label>
                            </div>
                        </div>

                        <div class="mb-0">
                            <button type="submit" class="btn btn-primary w-100">
                                {{ __('เข้าสู่ระบบ') }}
                            </button>

                            @if (Route::has('user.password.request'))
                                <a class="btn btn-link" href="{{ route('user.password.request') }}">
                                    {{ __('ลืมรหัสผ่าน?') }}
                                </a>
                            @endif
                        </div>
                    </form>

                    <hr>

    <div class="social-auth-links text-center mb-3">
    <p>- หรือ -</p>
    <a href="{{ route('user.auth.google') }}" class="btn btn-danger btn-lg w-100 mb-2">
        <i class="fab fa-google me-2"></i>{{ __('เข้าสู่ระบบด้วย Google') }}
    </a>
</div>

                    <div class="text-center">
                        <p>ยังไม่มีบัญชี? <a href="{{ route('user.register') }}">สมัครสมาชิก</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection