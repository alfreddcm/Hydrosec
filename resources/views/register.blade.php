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
                        <form>

                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" value="{{ request('email') }}" class="form-control" id="email" placeholder="Email" readonly>
                            </div>
                            <div class="form-group">
                                <label for="username">Username</label>
                                <input type="text" class="form-control" id="username" placeholder="Username">
                            </div>
                            <div class="form-group">
                                <label for="firstName">First Name</label>
                                <input type="text" class="form-control" id="firstName" placeholder="First Name">
                            </div>
                            <div class="form-group">
                                <label for="lastName">Last Name</label>
                                <input type="text" class="form-control" id="lastName" placeholder="Last Name">
                            </div>

                            <div class="form-group">
                                <label for="password">Password</label>
                                <input type="password" class="form-control" id="password" placeholder="Password">
                            </div>
                            <div class="form-group">
                                <label for="confirmPassword">Confirm Password</label>
                                <input type="password" class="form-control" id="confirmPassword" placeholder="Confirm Password">
                            </div>
                            <div class="form-group form-check">
                                <input type="checkbox" class="form-check-input" id="recaptcha">
                                <label class="form-check-label" for="recaptcha">reCaptcha</label>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">SIGN UP</button>
                            <button type="button" class="btn btn-secondary btn-block">CLOSE</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
