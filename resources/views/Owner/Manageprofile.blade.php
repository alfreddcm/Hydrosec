@extends('Owner/sidebar')
@section('title', 'Manage Profile')
@section('content')

@php
    $Name = Crypt::decryptString(Auth::user()->name);
    $Username = Crypt::decryptString(Auth::user()->username);
    $Email = Crypt::decryptString(Auth::user()->email);
@endphp

<div class="container mt-5">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-body">
                    <a href="{{ route('ownerdashboard') }}">Back</a>
                    <h5 class="card-title">Profile Information</h5>
                    <p class="card-text">Update your account's profile information and email address.</p>

                    <!-- Display Success Message -->
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <!-- Display Validation Errors -->

                    <form method="POST" action="{{ route('owner.profile.update') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ $Name }}">
                        </div>
                        <div class="mb-2">

                            <label for="username" class="form-label">Username</label>
                            @if ($errors->has('username'))
                            <br>
                            <span class="text-danger">{{ $errors->first('username') }}</span>
                        @endif
                            <input type="text" class="form-control" id="username" name="username" value="{{ $Username }}">
                        </div>
                        <div class="mb-2">
                            <label for="email" class="form-label">Email</label>
                            @if ($errors->has('email'))
                            <br>
                            <span class="text-danger">{{ $errors->first('email') }}</span>
                        @endif
                            <input type="email" class="form-control" id="email" name="email" value="{{ $Email }}" readonly>
                        </div>
                        <div class="mb-2 text">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#passwordConfirmModal">Save</button>
                        </div>
                    </form>
                    <div class="mb-2 text-center">
                        <a name="" id="" class="btn btn-info" href="{{ route('updatepassword') }}" role="button">Change Password</a><br>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Password Confirmation Modal -->
<div class="modal fade" id="passwordConfirmModal" tabindex="-1" aria-labelledby="passwordConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="passwordConfirmModalLabel">Confirm Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="passwordConfirmForm">
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                    </div>
                    <button type="button" class="btn btn-primary" id="confirmPasswordButton">Confirm</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('confirmPasswordButton').addEventListener('click', function () {
        var passwordConfirmation = document.getElementById('password_confirmation').value;
        var form = document.querySelector('form');
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'password_confirmation';
        input.value = passwordConfirmation;
        form.appendChild(input);
        form.submit();
    });
</script>

@endsection
