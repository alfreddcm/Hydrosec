<!doctype html>
<html lang="en">
    <head>
        <title>@yield('title')</title>
        <!-- Required meta tags -->
        <meta charset="utf-8" />
        <meta
            name="viewport"
            content="width=device-width, initial-scale=1, shrink-to-fit=no"
        />

        <!-- Bootstrap CSS v5.2.1 -->
        <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>


    </head>

    <body>
        <header>
            <!-- place navbar here -->
        </header>
        <main>
            @yield('content')
        </main>

        <script src="https://www.google.com/recaptcha/api.js" async defer></script>

        <!-- Bootstrap JavaScript Libraries -->
        <script src="{{ asset('js/jquery.min.js') }}"></script>
        <script src="{{ asset('js/popper.min.js') }}"></script>
        <script src="{{ asset('js/bootstrap.min.js') }}"></script>
        <script src="{{ asset('js/sweetalert.js') }}"></script>
    </body>
</html>
