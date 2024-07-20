@extends('layout')
@section('title', 'Login')
@section('content')
    <script async src="https://www.google.com/recaptcha/api.js"></script>

    <div class="container">

        <section class="section register min-vh-100 d-flex flex-column align-items-center justify-content-center py-4">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-4 col-md-6 d-flex flex-column align-items-center justify-content-center">
                        <div class="d-flex justify-content-center py-4">
                            <img src="assets/img/logo.png" alt="">
                            <span class="d-none d-lg-block">Hydrosec</span>
                        </div><!-- End Logo -->

                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="pt-4 pb-2">
                                    <h5 class="card-title text-center pb-0 fs-4">Login to Your Account</h5>
                                    <p class="text-center small">Enter your username & password to login</p>
                                </div>

                                <form action="{{ route('login.post') }}" method="POST"
                                    class="row g-3        needs-validation">
                                    @csrf
                                    @if (session('error'))
                                        <div class="alert alert-danger">
                                            {{ session('error') }}
                                        </div>
                                    @endif

                                    <div class="col-12">
                                        <label for="yourUsername" class="form-label">Username</label>
                                        <input type="username" class="form-control @error('username') is-invalid @enderror"
                                            id="username" name="username" value="{{ old('email') }}">
                                        @if ($errors->has('username'))
                                            <span class="text-danger">{{ $errors->first('username') }}</span>
                                        @endif
                                    </div>

                                    <div class="col-12">
                                        <label for="yourPassword" class="form-label">Password</label>
                                        <input type="password" class="form-control @error('password') is-invalid @enderror"
                                            id="password" name="password">
                                        @if ($errors->has('password'))
                                            <span class="text-danger">{{ $errors->first('password') }}</span>
                                        @endif
                                    </div>

                                    <div class="col-12">
                                        <div class="form-group form-check">
                                            <div class="g-recaptcha" name="g-recaptcha-response"
                                                data-sitekey="6LebogwqAAAAAJ47BvpYsyGSY5z2szywzZzJ6rMA"></div>
                                            @if ($errors->has('captcha'))
                                                <span class="text-danger">{{ $errors->first('captcha') }}</span>
                                            @endif
                                        </div>

                                    </div>
                                    <div class="col-12">
                                        <button class="btn btn-primary w-100" type="submit">Login</button>
                                    </div>
                                </form>

                                <div class="col-12">
                                    <p class="small mb-0">Don't have account? <a href="/">Create an account</a></p>
                                </div>


                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

@endsection
