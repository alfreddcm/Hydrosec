@extends('layout')
@section('title', 'Hydrosec')
@section('content')
    <link rel="stylesheet"
          href="{{ asset('assets/css/main.css') }}"
          class="rel">
    <script src="{{ asset('assets/js/main.js') }}"></script>

    <body>
        <style>
            a {
                text-decoration: none;
            }
        </style>
        <section id="hero">
            <div class="container">
                <div class="main-logo">
                    <a href="/">
                        <img src="images/logo.png"
                             alt="logo"
                             class="img-fluid">
                    </a>
                </div>
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h2 class="display-1 text-uppercase">Nurture your Hydroponics</h2>
                        <p class="fs-8 my-4 pb-2">Register to use HYDROSEC, a multi-level Security Web Smart monitoring
                            system</p>
                        <div>
                            <form id="form"
                                  class="d-flex align-items-center position-relative"
                                  method="POST"
                                  action="{{ route('checkEmail') }}">
                                @csrf
                                <input type="email"
                                       name="email"
                                       placeholder="Enter email to have access"
                                       class="form-control bg-white border-1 rounded-4 shadow-none px-4 py-3 w-100"
                                       required>
                                <button id="registerBtn"
                                        class="btn btn-primary rounded-4 px-3 py-2 position-absolute align-items-center m-1 end-0">Register</button>
                            </form>
                        </div>
                        <a href="/login"
                           class="login-link btn btn-primary rounded-4 px-3 py-2 position-absolute align-items-center m-1 ">Login</a>

                    </div>
                    <div class="col-md-6 mt-5">
                        <div class="image-container">

                            <div class="col-lg-4 order-lg-last hero-img aos-init aos-animate"
                                 data-aos="zoom-out"
                                 data-aos-delay="100">
                                <img src="images/lettu.png"
                                     class="img-fluid animated"
                                     alt="">
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </section>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                @if (session('success'))
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: '{{ session('success') }}',
                        timer: 3000,
                        showConfirmButton: false
                    });
                @elseif (session('error'))
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: '{{ session('error') }}',
                        timer: 3000,
                        showConfirmButton: false
                    });
                @endif
            });
            const showLoading = function() {
                Swal.fire({
                    title: '',
                    html: '<b>Be patient.</b><br/>Checking Email.',
                    allowEscapeKey: false,
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            };
            // Attach event listener to the button
            document.getElementById('registerBtn').addEventListener('click', showLoading);
        </script>
    </body>
    <footer class="fixed-bottom">
        <div class="container text-center">
            <div class="row padding-1">
                <div class="col copyright">
                    <p>© 2025 Hydrosec All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>
@endsection
