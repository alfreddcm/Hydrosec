@extends('layout')
@section('title', 'Registration')
@section('content')
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card mt-5">
                    <div class="card-header text-center">
                        <h4>SIGN UP</h4>
                    </div>
                    <div class="card-body">

                        <form action="{{ route('register.post') }}" method="POST">
                            @csrf

                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" name="email" value="{{ request('email') }}" class="form-control" id="email" placeholder="Email" readonly>
                            </div>

                            <div class="form-group">
                                <label for="username">Username</label>
                                <input type="text" name="username" class="form-control" id="username" placeholder="Username">
                            </div>

                            <div class="form-group">
                                <label for="fullName">Full Name</label>
                                <input type="text" name="fullName" class="form-control" id="fullName" placeholder="Full Name">
                            </div>

                            <div class="form-group">
                                <label for="password">Password</label>
                                <input type="password" name="password" class="form-control" id="password" placeholder="Password">
                            </div>

                            <div class="form-group">
                                <label for="confirmPassword">Confirm Password</label>
                                <input type="password" name="password_confirmation" class="form-control" id="confirmPassword" placeholder="Confirm Password">
                            </div>

                            <div class="form-group form-check">
                                <div class="g-recaptcha" data-sitekey="6LebogwqAAAAAJ47BvpYsyGSY5z2szywzZzJ6rMA"></div>
                            </div>

                            <div class="text-center">
                                <button type="submit" class="btn btn-primary btn-block">SIGN UP</button>
                            </div>
                        </form>

                        <div class="text-center pt-1">
                            <button type="button" class="btn btn-secondary btn-block" onclick="window.location.href='{{ url('/') }}'">RETURN</button>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

        <!-- SweetAlert Script -->
        @if(session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: '{{ session('error') }}',
                });
            });
        </script>
    @endif

    @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                title: "Success!",
                text: '{{ session('success') }}',
                icon: "success"
                });


            });
        </script>
    @endif

@endsection
