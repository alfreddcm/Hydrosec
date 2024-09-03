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
<style>
body {
    background: linear-gradient(to right, rgba(7, 107, 121, 0.8), rgba(0, 150, 136, 0.8));

    margin: 0;
    padding: 0;
    /* background-image: url('{{ asset('images/bg2.png') }}'); */
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    height: 100vh;
    font-family: Arial, sans-serif;
}

.material-card {
    background: #ffffff;
    border-radius: 15px;
    box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.2);
    padding: 20px;
    text-align: center;
    max-width: 500px;
    margin: 0 auto;
    position: relative;
}

.material-card img {
    border-radius: 10px;
    width: 100%;
    height: auto;
}

#hero {
    display: flex;
    align-items: center;
    height: 100vh;
    color: #fff;
}

/* h2.display-1 {
    font-size: 3.5rem;
    font-weight: bold;
} */

p.fs-8 {
    font-size: 1.2rem;
}



.login-link {
   position: absolute;
    top: 30px;
    right: 50px; 
    text-decoration: none;
    font-weight: bold;
    padding: 5px 10px;
    transition: color 0.3s ease, text-decoration 0.3s ease;
}

.login-link:hover {
    text-decoration: underline;
    color: #00bcd4;
}

footer {
    background: rgba(255, 255, 255, 0.8);
    padding: 1px 0;
}

</style>
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
