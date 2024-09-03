@extends('layout')
@section('title', 'Login')
@section('content')
<style>

</style>
    <script async src="https://www.google.com/recaptcha/api.js"></script>

    <div class="container">

        <section class="section register min-vh-100 d-flex flex-column align-items-center justify-content-center py-4">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-4 col-md-6 d-flex flex-column align-items-center justify-content-center">
                       
                        <div class="card mb-3">
                            <div class="d-flex justify-content-center py-4">
                                {{-- <img src="images/logo.png" alt="logo" class="img-fluid">
                                <br> --}}
                                <h4 class="">HYDROSEC</h4>
                            </div><!-- End Logo -->
                            
                            <div class="card-body">
                                <div class="pt-2 pb-2">
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
                                    
                                    </div>

                                    <div class="col-12">
                                        <label for="yourPassword" class="form-label">Password</label>
                                        <input type="password" class="form-control @error('password') is-invalid @enderror"
                                            id="password" name="password">
                                     
                                    </div>

                                    <div class="col-12">
                                        <div class="form-group form-check">
                                            <div class="g-recaptcha" 
         data-sitekey="{{ env('RECAPTCHA_SITE_KEY') }}"></div>
    @if ($errors->has('g-recaptcha-response'))
        <span class="text-danger">{{ $errors->first('g-recaptcha-response') }}</span>
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Display success message if present
            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: '{{ session('success') }}',
                    timer: 3000,
                    showConfirmButton: false
                });
            @endif
    
            // Display error messages if present
            @if ($errors->any())
                var errors = @json($errors->all());
                var errorText = errors.join('\n');
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorText,
                    timer: 5000,
                    showConfirmButton: true
                });
            @endif
    
            // Display a timeout error alert if there's a timeout
            @if (session('timeout'))
                Swal.fire({
                    icon: 'error',
                    title: 'Timeout Error',
                    text: 'The connection to the server timed out. Please try again later.',
                    timer: 5000,
                    showConfirmButton: true
                });
            @endif
        });
    </script>
@endsection
