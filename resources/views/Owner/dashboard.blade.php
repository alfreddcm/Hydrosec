@extends('Owner/sidebar')
@section('title', 'Dashboard')
@section('content')

    @php

        use App\Models\Owner;
        use App\Models\Tower;
        use App\Models\Worker;
        use Carbon\Carbon;

        $towerCount = Tower::where('OwnerID', Auth::id())->count();
        $workers = Worker::where('OwnerID', Auth::id())->get();

        $enabledWorkerCount = $workers
            ->filter(function ($worker) {
                return Crypt::decryptString($worker->status) === '1';
            })
            ->count();
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
        $count = 0;
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

        .card-text {
            justify-self: flex-start;
        }

        .card-body img {
            height: 100px;
        }

        a {
            text-decoration: none;
        }

        canvas {
            width: 20%;
            !important
        }

        .data .card {
            min-height: 300px;
            /* Set the minimum height for the card */
            margin-bottom: 20px;
            /* Add some spacing below each card */
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            /* Add a slight shadow for visual depth */
            border-radius: 10px;
            /* Round the corners of the card */
            overflow: hidden;
            /* Ensure content doesn't overflow */
        }

        .data .card-body {
            padding: 20px;
            /* Add some padding inside the card */
        }

        .data .card-body canvas {
            height: 400px;
            /* Set a fixed height for the charts */
            width: auto !important;
            /* Ensure the charts stretch the full width */
        }
    </style>
    <div class="container">
        <div class="row">

            {{-- <span>{{ $vali }}</span><br> --}}
            {{-- <span>{{ $vali2 }}</span> --}}

            <!-- Tower Count Card -->
            {{-- @foreach ($tower as $item)
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div>
                            <strong>Tower Name:</strong> {{ $item->name }}
                        </div>
                        <div>
                            <strong>Date Started:</strong> {{ Carbon::parse($item->startdate)->format('d/m/Y') }}
                        </div>
                        <div>
                            <strong>Harvest Date:</strong>{{ Carbon::parse($item->enddate)->format('d/m/Y') }}
                        </div>
                    </div>
                </div>
            @endforeach --}}

            <br>
            <div class="col-md-3 mb-4">
                <a href="{{ route('ownermanagetower') }}">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Tower</h5>
                        </div>
                        <div class="card-body text-start">
                            <span class="card-text count">{{ $towerCount }}</span>

                            <img src="{{ asset('images/icon/towericon.png') }}" alt="towericon">

                        </div>
                    </div>
                </a>

            </div>
            <div class="col-md-3 mb-4">
                <a href="{{ route('ownerworkeraccount') }}">

                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Workers</h5>
                        </div>
                        <div class="card-body">
                            <span class="card-text count">{{ $enabledWorkerCount }}</span>

                            <img src="{{ asset('images/icon/workericon.png') }}" alt="towericon">

                        </div>
                    </div>
                </a>

            </div>

            <!-- Worker Count Card -->

        </div>
        <div class="contain data">
            @if ($allDecryptedData)
                @foreach ($allDecryptedData as $id => $data)
                    @php
            $code = $data['towercode'];

                    @endphp
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header mb-0">
                                    <h3>Tower Code: {{$code}}</h3>
                                    <br>
                                    <small>From: {{ $data['startDate'] }} To: {{ $data['endDate'] }}</small>
                                </div>
                                <div class="card-body">
                                    <div class="row g-0">
                                        {{--  --}}
                  {{--  --}}
                                        <!-- pH Graph Column -->
                                        <div class="col-md-3">
                                            <canvas id="phChart-{{ $code }}"></canvas>
                                        </div>

                                        <!-- Temperature Graph Column -->
                                        <div class="col-md-3">
                                            <canvas id="tempChart-{{ $code }}"></canvas>
                                        </div>

                                        <!-- Nutrient Level Graph Column -->
                                        <div class="col-md-3">
                                            <canvas id="waterChart-{{ $code }}"></canvas>
                                        </div>

                                        <!-- Light Graph Column -->
                                        <div class="col-md-3">
                                            <canvas id="lightChart-{{ $code }}"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <script>
                        // Data for each tower
                        const data = @json($data['data']);

                        function createChart(ctx, label, dataKey) {
                            new Chart(ctx, {
                                type: 'bar',
                                data: {
                                    labels: data.map(item => item.created_at),
                                    datasets: [{
                                        label: label,
                                        data: data.map(item => item[dataKey]),
                                        borderColor: 'rgba(75, 192, 192, 1)',
                                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                        borderWidth: 1
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    plugins: {
                                        legend: {
                                            display: true,
                                            position: 'top'
                                        },
                                        tooltip: {
                                            callbacks: {
                                                label: function(tooltipItem) {
                                                    return tooltipItem.dataset.label + ': ' + tooltipItem.raw;
                                                }
                                            }
                                        }
                                    }
                                }
                            });
                        }

                        // Create the charts
                        createChart(document.getElementById('phChart-{{ $code }}').getContext('2d'), 'pH Levels', 'pH');
                        createChart(document.getElementById('tempChart-{{ $code }}').getContext('2d'), 'Temperature',
                            'temperature');
                        createChart(document.getElementById('waterChart-{{ $code }}').getContext('2d'), 'Nutrient Level',
                            'nutrientlevel');
                        createChart(document.getElementById('lightChart-{{ $code }}').getContext('2d'), 'Light Level', 'light');
                    </script>
                @endforeach
        </div>
        @endif
    </div>

    <div class="mb-4">
        <h4>Tower Alert Logs</h4>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead class="thead-light">
                    <tr>
                        <th>No.</th>
                        <th>Tower Name</th>
                        <th>Tower Code</th>
                        <th>Alert Activity</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($towerLogs as $log)
                        <tr>
                            <td>{{ $count = $count + 1 }}</td>
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>

    <script></script>

@endsection
