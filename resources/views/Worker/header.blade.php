<!doctype html>
<html lang="en">
    <head>
        <title>@yield('title')</title>
        <!-- Required meta tags -->
        <meta charset="utf-8" />
        <meta
            name="viewport"
            content="width=device-width, initial-scale=1, shrink-to-fit=yes"
        />

        <!-- Bootstrap CSS v5.2.1 -->
        <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">

    </head>

    <body>
        @yield('Owner.content')

    </body>
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/popper.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('js/sweetalert.js') }}"></script>
</html>
