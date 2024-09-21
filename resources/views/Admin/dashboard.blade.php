@extends('Admin/sidebar')
@section('title', 'Dashboard')
@section('content')
    <style>
        .card {
            padding: 10px;
            height: 200px;
            border-radius: 10px;
            border: 1px solid #ddd;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }

        .card:hover {
            transform: scale(1.02);
        }

        .card .card-body {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .count {
            margin-left: 20px;
            display: inline-flex;
            justify-content: start;
            align-items: center;
            width: 25px;
            height: 25px;
            font-size: 100px;
        }

        .card-body img {
            height: 100px;
        }
        .intr{
            height: max-content;
        }
        
    </style>
    <div class="container mt-1">
        <div class="row justify-content-start align-items-center g-2">

            <div class="col-md-3 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Owner account</h5>
                    </div>
                    <div class="card-body text-start">
                        <span class="card-text count">{{ $ownerCount }}</span>
                        <img src="{{ asset('images/icon/workericon.png') }}" alt="towericon">

                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Worker accounts</h5>
                    </div>
                    <div class="card-body text-start">
                        <span class="card-text count">{{ $workerCount }}</span>
                        <img src="{{ asset('images/icon/workericon.png') }}" alt="towericon">

                    </div>
                </div>
            </div>

        </div>
        <div class="row ${1| ,row-cols-2,row-cols-3, auto,justify-content-md-center,|}">
            <div class="card intr">
                <div class="card-header">
                    Intrusion Detection Logs
                </div>
                <div class="card-body table-card">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>IP Address</th>
                                <th>User Agent</th>
                                <th>Failed Attempts</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($intrusions as $index => $intrusion)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $intrusion->ip_address }}</td>
                                    <td>{{ $intrusion->user_agent }}</td>
                                    <td>{{ $intrusion->failed_attempts }}</td>
                                    <td>{{ $intrusion->formatted_detected_at }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

@endsection
