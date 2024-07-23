<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        a{
            text-decoration: none;

        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            border-bottom: 1px solid #000;
        }
        .content {
            padding: 20px;
        }
        .user-card {
            border: 1px solid #000;
            border-radius: 10px;
            padding: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            max-width: 500px;
            margin-top: 20px;
        }
        .add-button {
            position: absolute;
            bottom: 20px;
            right: 20px;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: #000;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <div>
                    <a href="/Admin/dashboard">
                        <strong> DASHBOARD
                        </strong>
                        </a>
                    <a href="/Admin/profile">
                        | Manage Account
                    </a>
                </div>
            </div>
            <div>
                <a href="{{ route('logout') }}">LOG OUT</a>
            </div>
        </div>
        <div class="content">
            <h4>Welcome</h4>
            <h2>@if (Auth::check())
                @php
                    $decryptedName = \Illuminate\Support\Facades\Crypt::decryptString(
                        Auth::user()->name,
                    );
                @endphp
                <p>{{ $decryptedName }}</p>
            @endif</h2>
            <p>{{ \Carbon\Carbon::now()->format('D | M d, Y') }}</p>
            <div class="user-card">
                <span>ANDREY PIMPIL</span>
                <span>
                    <i class="bi bi-person-fill"></i>
                    <i class="bi bi-pencil"></i>
                    <i class="bi bi-x"></i>
                </span>
            </div>
        </div>
        <div class="add-button">
            <i class="bi bi-plus"></i>
        </div>
    </div>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.8.1/font/bootstrap-icons.min.js"></script>
</body>
</html>
