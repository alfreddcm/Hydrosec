<!doctype html>
<html lang="en">

<head>
    <title>@yield('title')</title>
    <!-- Required meta tags -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <link href="{{ asset('css/sidebar.css') }}" rel="stylesheet">
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>

<body>
    <main class="containe">
        <div class="row g-0">
            <div class="col-2 side">
                <div class="p-2 text-white">
                    <a
                        href="#"class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
                        <img src="" alt="logo">
                        <span class="fs-4">
                            <div class="dropdown ps-1">
                                <a href="#"
                                    class="d-flex align-items-center text-white text-decoration-none dropdown-toggle"
                                    id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">

                                    <strong>
                                        @if (Auth::check())
                                            @php
                                                $decryptedName = \Illuminate\Support\Facades\Crypt::decryptString(
                                                    Auth::user()->name,
                                                );
                                            @endphp
                                            <p>{{ $decryptedName }}</p>
                                        @endif
                                    </strong>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-dark text-small shadow"
                                    aria-labelledby="dropdownUser1">
                                    <li><a class="dropdown-item" href="#">Manage Account</a></li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li><a class="dropdown-item" href="{{ route('logout') }}">Sign out</a></li>
                                </ul>
                            </div>
                        </span>
                    </a>
                    <hr>
                    <ul class="nav nav-pills flex-column mb-auto">
                        <li class="nav-item">
                            <a href="/Owner/dashboard"
                                class="nav-link {{ request()->is('Owner/dashboard') ? 'active' : 'text-white' }}"
                                aria-current="page">
                                <svg class="bi me-2" width="16" height="16">
                                    <use xlink:href="#home"></use>
                                </svg>
                                Dashboard
                            </a>
                        </li>
                        <li>
                            <a href="/Owner/ManageTower"
                                class="nav-link {{ request()->is('Owner/ManageTower') ? 'active' : 'text-white' }}">
                                <svg class="bi me-2" width="16" height="16">
                                    <use xlink:href="#speedometer2"></use>
                                </svg>
                                Towers
                            </a>
                        </li>
                        <li>
                            <a href="/Owner/WorkerAccounts"
                                class="nav-link {{ request()->is('Owner/WorkerAccounts') ? 'active' : 'text-white' }}">
                                <svg class="bi me-2" width="16" height="16">
                                    <use xlink:href="#table"></use>
                                </svg>
                                Worker Accounts
                            </a>
                        </li>

                    </ul>
                </div>

            </div>
            <div class="col-10 main">
                <div class="dashboard-header mb-2">
                    <div class="row">
                        <div class="col ">
                            <h2>@yield('title')</h2>
                        </div>
                        <div class="col text-end ">
                            <p>{{ \Carbon\Carbon::now()->format('D | M d, Y') }}</p>
                        </div>
                    </div>
                </div>
                @yield('content')
            </div>
    </main>

    <script src="https://www.google.com/recaptcha/api.js" async defer></script>

    <!-- Bootstrap JavaScript Libraries -->
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/popper.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('js/sweetalert.js') }}"></script>
</body>

</html>
