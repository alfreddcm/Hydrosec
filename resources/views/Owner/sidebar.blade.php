<!doctype html>
<html lang="en">

    <head>
        <title>@yield('title')</title>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="csrf-token" content="{{ csrf_token() }}">

        {{-- <link href="{{ asset('css/sidebar.css') }}" rel="stylesheet"> --}}
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
        <link rel="stylesheet"
            href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
        <script src="{{ asset('js/jquery.min.js') }}"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/date-fns@2.28.0/date-fns.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0/dist/chartjs-plugin-datalabels.min.js">
        </script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <style>
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                margin: 0;
                padding: 0;
            }

            .side a {
                color: #ecf0f1;
                text-decoration: none;
            }

            .side a:hover {
                color: #bdc3c7;
            }

            .main {
                background-color: #ecf0f1;
                transition: margin-left 0.3s ease;
            }

            .dashboard-header {
                padding: 10px 20px;
                background-color: #00bcd4;
                color: #fff;
                text-align: start;
                top: 0;
                width: 100%;
                position: fixed;
                z-index: 1000;
                height: 60px;
            }

            .container-fluid {
                padding: 0;
            }

            .side {
                position: fixed;
                top: 0;
                left: 0;
                height: 100vh;
                width: 250px;
                background-color: #2c3e50;
                overflow-y: auto;
                z-index: 1040;
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }

            .side.show {
                transform: translateX(0);
            }

            .main.expanded {
                margin-left: 250px;
            }

            .contenting {
                margin-top: 80px;
            }

            a {
                text-decoration: none;
            }

            .time p {
                margin: 0;
                padding: 0;
            }

            /* Sidebar off-screen on mobile */
            @media (max-width: 767px) {
                .main.expanded {
                    margin-left: 0;
                }

                .close-sidebar-btn {
                    position: absolute;
                    top: 10px;
                    right: 10px;
                    background: none;
                    border: none;
                    color: #ecf0f1;
                    font-size: 20px;
                    cursor: pointer;
                }
            }

            /* Sidebar visible on larger screens */
            @media (min-width: 768px) {
                .side {
                    transform: translateX(0);
                }

                .close-sidebar-btn {
                    display: none;
                }

                .main {
                    margin-left: 250px;
                    max-width: calc(100% - 250px);
                }
            }
        </style>
    </head>

    <body>
        @php
            use Illuminate\Support\Facades\Crypt;
            use Carbon\Carbon;
            use App\Models\Owner;

            $decryptedName = null;
            if (Auth::check()) {
                $user = Owner::find(Auth::id());
                if ($user) {
                    $decryptedName = Crypt::decryptString($user->name);
                }
            }
        @endphp

        <!-- Sidebar toggle button -->

        <main class="container-fluid">
            <div class="row g-0">
                <!-- Sidebar -->
                <div class="col-2 side" id="sidebar">
                    <button class="close-sidebar-btn" onclick="toggleSidebar()">&times;</button>
                    <div class="p-2 text-white">
                        <a href="#"
                            class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
                            <span class="fs-4">
                                <div class="dropdown ps-1">
                                    <a href="#"
                                        class="d-flex align-items-center text-white text-decoration-none dropdown-toggle"
                                        id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                                        <img src="{{ asset('images/logo.png') }}" alt="logo" class="rounded-circle"
                                            style="width: 50px; height: 50px; margin-right: 8px;">
                                        <strong>{{ $decryptedName ?? 'Guest' }}</strong>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-dark text-small shadow"
                                        aria-labelledby="dropdownUser1">
                                        <li><a class="dropdown-item" href="{{ route('ownermanageprofile') }}">Manage
                                                Account</a></li>
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
                                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                                </a>
                            </li>
                            <li>
                                <a href="/Owner/ManageTower"
                                    class="nav-link {{ request()->is('Owner/ManageTower') ? 'active' : 'text-white' }}">
                                    <i class="bi bi-building me-2"></i> Towers
                                </a>
                            </li>
                            <li>
                                <a href="/Owner/WorkerAccounts"
                                    class="nav-link {{ request()->is('Owner/WorkerAccounts') ? 'active' : 'text-white' }}">
                                    <i class="bi bi-people me-2"></i> Worker Accounts
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                <!-- Main content -->
                <div class="col main">
                    <div class="dashboard-header">
                        <div class="row">
                            <div class="col ps-3">
                                <h2> <button class="sidebar-toggle btn btn-primary d-md-none" onclick="toggleSidebar()">
                                        <i class="fa fa-bars"></i>
                                    </button>
                                    @yield('title')</h2>
                            </div>
                            <div class="col text-end time">
                                <p>{{ Carbon::now()->format('D | M d, Y') }}
                                <p id="current-time"></p>
                                </p>

                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="contenting">
                            @yield('content')
                        </div>
                    </div>

                </div>
            </div>
        </main>

        <script src="{{ asset('js/popper.min.js') }}"></script>
        <script src="{{ asset('js/bootstrap.min.js') }}"></script>
        <script src="{{ asset('js/sweetalert.js') }}"></script>
        <script>
            function updateTime() {
                const now = new Date();
                let hours = now.getHours();
                const minutes = String(now.getMinutes()).padStart(2, '0');
                const seconds = String(now.getSeconds()).padStart(2, '0');
                const ampm = hours >= 12 ? 'PM' : 'AM';

                hours = hours % 12 || 12;
                document.getElementById('current-time').textContent =
                    `${String(hours).padStart(2, '0')}:${minutes}:${seconds} ${ampm}`;
            }

            updateTime();
            setInterval(updateTime, 1000);
        </script>

        <script>
            // Toggle sidebar function for mobile view
            function toggleSidebar() {
                const sideBar = document.querySelector('.side');
                sideBar.classList.toggle('show');
            }

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
        </script>
    </body>

</html>
