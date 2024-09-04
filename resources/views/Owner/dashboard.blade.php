@extends('Owner/sidebar')
@section('title', 'Dashboard')
@section('content')
    {{-- @php
use Illuminate\Support\Facades\Crypt;

$vali = Crypt::encryptString("1");
$vali2 = Crypt::encryptString("0");
@endphp --}}
    <style>
        .table-responsive {
            max-height: 350px;
            overflow-y: auto;
        }

        .table thead th {
            position: sticky;
            top: 0;
            background-color: #f8f9fa;
            z-index: 1;
        }

        .card {
            height: 200px;
            border-radius: 10px;
            border: 1px solid #ddd;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }

        .card .card-body {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .count {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            width: 25px;
            height: 25px;
            border-radius: 50%;
            border: 1px solid black;
            text-align: center;

        }
    </style>
    <div class="container">
        <div class="row">

            {{-- <span>{{ $vali }}</span><br> --}}
            {{-- <span>{{ $vali2 }}</span> --}}

            <!-- Tower Count Card -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Tower <span class="card-text count">{{ $towerCount }}</span></h5>

                    </div>
                    <div class="card-body">

                    </div>
                </div>
            </div>

            <!-- Worker Count Card -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Workers <span class="card-text count">{{ $towerCount }}</span></h5>
                    </div>
                    <div class="card-body">

                    </div>
                </div>
            </div>
        </div>

        </tbody>
        </table>

        <div class="mb-4">
            <h2>Tower Alert Logs</h2>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead class="thead-light">
                        <tr>
                            <th>Tower Name</th>
                            <th>Tower Code</th>
                            <th>Alert Activity</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($towerLogs as $log)
                            <tr>
                                <td>{{ Crypt::decryptString($log->tower_name) }}</td>
                                <td>{{ Crypt::decryptString($log->tower_code) }}</td>
                                <td>{{ Crypt::decryptString($log->activity) }}</td>
                                <td>{{ \Carbon\Carbon::parse($log->created_at)->format('g:i A D m/d/Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection
