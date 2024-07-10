<!-- resources/views/your-view.blade.php -->
@extends('layout')

@section('title', 'Hydrosec')

@section('content')
<body>
    <nav class="main-menu d-flex navbar navbar-expand-lg p-2 py-3 p-lg-4 py-lg-4">
        <div class="container-fluid">
            <div class="main-logo d-lg-none">
                <a href="/">
                    <img src="images/logo.png" alt="logo" class="img-fluid">
                </a>
            </div>
            <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbarLabel">
                <div class="offcanvas-body justify-content-between">
                    <div class="main-logo">
                        <img src="images/logo.png" alt="logo" class="img-fluid">
                    </div>
                    <div class="d-none d-lg-flex align-items-center">
                        <ul class="d-flex align-items-center list-unstyled m-0">
                            <li>
                                <a href="/login">Login</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>
    <section id="hero" style="background-image:url(images/billboard-bg.png); background-repeat: no-repeat;">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 pe-5 mt-5 mt-md-0">
                    <h2 class="display-1 text-uppercase">Nurture your Hydroponics</h2>
                    <p class="fs-4 my-4 pb-2">Lettuce ..</p>
                    <div>
                        <form id="form" class="d-flex align-items-center position-relative" method="POST" action="{{ route('checkEmail') }}">
                            @csrf
                            <input type="email" name="email" placeholder="Enter email to have access" class="form-control bg-white border-1 rounded-4 shadow-none px-4 py-3 w-100">
                            <button class="btn btn-primary rounded-4 px-3 py-2 position-absolute align-items-center m-1 end-0">Register</button>
                        </form>
                    </div>
                </div>
                <div class="col-md-6 mt-5">
                    <img src="images/billboard-img.jpg" alt="img" class="img-fluid">
                </div>
            </div>
        </div>
    </section>

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

</body>
<footer class="fixed-bottom">
    <hr class="text-black-50">
    <div class="container text-center">
        <div class="row padding-0">
            <div class="col copyright">
                <p>© 2025 Hydrosec All rights reserved.</p>
            </div>
        </div>
    </div>
</footer>
@endsection
