@extends('Owner/sidebar')
@section('title', 'Dashboard')
@section('content')
    @php

        use App\Models\Owner;
        use App\Models\Tower;
        use App\Models\Worker;
        use Carbon\Carbon;

        $towerCount = Tower::where('OwnerID', Auth::id())->count();
        $workerCount = Worker::where('OwnerID', Auth::id())->count();

        $towerCount = Tower::where('OwnerID', Auth::id())->count();

        $towerId = Tower::where('OwnerID', Auth::id())->value('id');

        $towerLogs = DB::table('tbl_towerlogs')
            ->join('tbl_tower', 'tbl_towerlogs.ID_tower', '=', 'tbl_tower.id')
            ->where('tbl_towerlogs.ID_tower', $towerId)
            ->select(
                'tbl_tower.name as tower_name',
                'tbl_tower.towercode as tower_code',
                'tbl_towerlogs.activity',
                'tbl_towerlogs.created_at',
            )
            ->orderBy('tbl_towerlogs.created_at', 'desc')
            ->get();
        $tower = Tower::where('OwnerID', Auth::id());
    @endphp
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

        .card:hover {
            transform: scale(1.02);
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
            @foreach ($tower as $item)
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <img src="{{ asset('images/towicon.png') }}" alt="Tower Image">
                        <div>
                            <strong>Tower Name:</strong> {{ $tower->name }}
                        </div>
                        <div>
                            <strong>Date Started:</strong> {{ Carbon::parse($tower->startdate)->format('d/m/Y') }}
                        </div>
                        <div>
                            <strong>Harvest Date:</strong>{{ Carbon::parse($tower->enddate)->format('d/m/Y') }}
                        </div>
                    </div>
                </div>
            @endforeach

<br>
            <div class="col-md-3 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Tower</h5>
                    </div>
                    <div class="card-body">
                        <img src="{{'assets/image/towicon.png'}}" alt="towericon"><span class="card-text count">{{ $towerCount }}</span>

                    </div>
                </div>
            </div>

            <!-- Worker Count Card -->
            <div class="col-md-3 mb-4">
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
