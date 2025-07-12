@extends('User.layouts.app')

@section('title', 'Register')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">{{ __('สมัครสมาชิก') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('user.register') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">{{ __('ชื่อ-นามสกุล') }}</label>
                            <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" 
                                   name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>
                            @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">{{ __('อีเมล') }}</label>
                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" 
                                   name="email" value="{{ old('email') }}" required autocomplete="email">
                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">{{ __('เบอร์โทรศัพท์') }}</label>
                            <input id="phone" type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                   name="phone" value="{{ old('phone') }}" required>
                            @error('phone')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">{{ __('รหัสผ่าน') }}</label>
                            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" 
                                   name="password" required autocomplete="new-password">
                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password-confirm" class="form-label">{{ __('ยืนยันรหัสผ่าน') }}</label>
                            <input id="password-confirm" type="password" class="form-control" 
                                   name="password_confirmation" required autocomplete="new-password">
                        </div>

                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary w-100">
                                {{ __('สมัครสมาชิก') }}
                            </button>
                        </div>
                    </form>

                    <hr>

                    <div class="social-auth-links text-center">
                        <p>- หรือ -</p>
                        <a href="{{ route('user.login.google') }}" class="btn btn-danger w-100 mb-2">
                            <i class="fab fa-google me-2"></i>สมัครสมาชิกด้วย Google
                        </a>
                        <a href="{{ route('user.login.line') }}" class="btn btn-success w-100">
                            <i class="fab fa-line me-2"></i>สมัครสมาชิกด้วย LINE
                        </a>
                    </div>

                    <div class="text-center mt-3">
                        <p>มีบัญชีอยู่แล้ว? <a href="{{ route('user.login') }}">เข้าสู่ระบบ</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection