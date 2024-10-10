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
            max-height: 150px;
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

        .qqq .card:hover {
            transform: scale(1.02);
        }

        .q .card-body {
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

        .data .card {
            min-height: 750px;
            margin-bottom: 20px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            overflow: hidden;
        }

        .data .card-body {
            padding: 20px;
        }

        .data .card-body canvas {
            width: auto !important;
        }

        .qq {
            margin: 0;
            padding: 0;
            font-size: smaller
        }

        .data .chart-card {
            display: flex;
            flex-direction: column;
            height: 100%;
            /* Ensure the card height adapts to the content */
            margin-bottom: 20px;
            /* Add margin if needed */
        }

        .data .card-body {
            display: flex;
            flex-direction: column;
            flex: 1;
            /* Ensure the card body takes up remaining space */
            padding: 0;
        }

        .data .chart-container {
            flex: 1;
            /* Ensure the chart container takes up remaining space */
        }

        .data .chart {
            width: 100%;
            height: 300px;
            /* Set the height of the chart (adjust as needed) */
        }
    </style>

    <div class="container">
        <script src="https://code.highcharts.com/highcharts.js"></script>

        <div class="row qqq">
            <div class="col-md-3 mb-4">
                <a href="{{ route('ownermanagetower') }}">
                    <div class="card q">
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
                    <div class="card q">
                        <div class="card-header">
                            <h5 class="card-title">Workers</h5>
                        </div>
                        <div class="card-body">
                            <span class="card-text count">{{ $enabledWorkerCount }}</span>
                            <img src="{{ asset('images/icon/workericon.png') }}" alt="workericon">
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-6 mb-4">
                <div class="card qq">
                    <div class="card-body">
                        <h5 class="card-title">Tower Alert Logs</h5>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead class="thead-light">
                                    <tr>
                                        <th>No.</th>
                                        <th> Name</th>
                                        <th> Code</th>
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
            </div>
        </div>

        <div class="contain data">
            @if ($allDecryptedData)
                @foreach ($allDecryptedData as $id => $data)
                    @php
                        $code = $data['towercode'];
                    @endphp
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card chart-card">
                                <div class="card-header mb-0">
                                    <h4>Tower Code: {{ $code }} | Plant : {{ $data['plantVar'] }}</h4>
                                    <small>From: {{ $data['startDate'] }} To: {{ $data['endDate'] }}</small>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <!-- pH Graph Column -->
                                        <div class="col-md-6 mb-4">
                                            <div class="chart-container">
                                                <div id="phChart-{{ $code }}" class="chart"></div>
                                            </div>
                                        </div>

                                        <!-- Temperature Graph Column -->
                                        <div class="col-md-6 mb-4">
                                            <div class="chart-container">
                                                <div id="tempChart-{{ $code }}" class="chart"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <!-- Nutrient Level Graph Column -->
                                        <div class="col-md-6 mb-4">
                                            <div class="chart-container">
                                                <div id="waterChart-{{ $code }}" class="chart"></div>
                                            </div>
                                        </div>

                                        <!-- Light Graph Column -->
                                        <div class="col-md-6 mb-4">
                                            <div class="chart-container">
                                                <div id="lightChart-{{ $code }}" class="chart"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <!-- Pump Status Graph Column -->
                                        <div class="col-md-12 mb-4">
                                            <div class="chart-container">
                                                <div id="pumpChart-{{ $code }}" class="chart"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const data = @json($data['data']);

                            function createChart(containerId, title, dataKey, yAxisOptions) {
                                // Access the appropriate key based on the chart type
                                const chartData = data.map(item => {
                                    const dataArray = item[dataKey]; // Access the specific data array
                                    return dataArray.map(dataPoint => [new Date(dataPoint.created_at).getTime(), dataPoint
                                        .value
                                    ]); // Map to timestamp-value pairs
                                }).flat(); // Flatten the array since data is nested

                                const hasData = chartData.length > 0;

                                Highcharts.chart(containerId, {
                                    chart: {
                                        type: 'line',
                                        height: '300'
                                    },
                                    title: {
                                        text: title
                                    },
                                    xAxis: {
                                        type: 'datetime',
                                        labels: {
                                            format: '{value:%I:%M %p %b %e}'
                                        }
                                    },
                                    yAxis: yAxisOptions,
                                    series: [{
                                        name: title,
                                        data: hasData ? chartData : [],
                                        dataLabels: {
                                            enabled: !hasData, // Enable only if no data
                                            format: 'No data available'
                                        },
                                        dashStyle: 'solid',
                                    }],
                                    tooltip: {
                                        pointFormat: `{series.name}: <b>{point.y:.2f}</b>`
                                    }
                                });
                            }

                            const charts = [{
                                    id: 'phChart-{{ $code }}',
                                    title: 'pH Levels',
                                    key: 'pH_data', // Updated key
                                    yAxisOptions: {
                                        min: 1,
                                        max: 14,
                                        tickAmount: 10
                                    }
                                },
                                {
                                    id: 'tempChart-{{ $code }}',
                                    title: 'Temperature',
                                    key: 'temperature_data', // Updated key
                                    yAxisOptions: {
                                        min: 0,
                                        max: 60,
                                        tickAmount: 7,
                                        tickInterval: 10
                                    }
                                },
                                {
                                    id: 'waterChart-{{ $code }}',
                                    title: 'Nutrient Volume',
                                    key: 'nutrient_data', // Updated key
                                    yAxisOptions: {
                                        min: 1,
                                        max: 20,
                                        tickAmount: 5,
                                        tickInterval: 5
                                    }
                                },
                                {
                                    id: 'lightChart-{{ $code }}',
                                    title: 'Light',
                                    key: 'light_data', // Updated key
                                    yAxisOptions: {
                                        categories: [0, 1],
                                        tickAmount: 2,
                                        tickInterval: 1
                                    }
                                }
                            ];

                            charts.forEach(chart => {
                                createChart(chart.id, chart.title, chart.key, chart.yAxisOptions);
                            });

                            // Check for pump data
                            const pumpDataExists = data.some(item => item.pump_data.length > 0); // Check if pump data exists

                            Highcharts.chart('pumpChart-{{ $code }}', {
                                chart: {
                                    type: 'spline',
                                    height: '300'
                                },
                                title: {
                                    text: 'Pump'
                                },
                                xAxis: {
                                    type: 'datetime',
                                    labels: {
                                        format: '{value:%I:%M %p %b %e}'
                                    }
                                },
                                yAxis: {
                                    title: {
                                        text: 'Pump Status'
                                    },
                                    min: 0,
                                    max: 1,
                                    tickInterval: 1
                                },
                                series: [{
                                    name: 'Pump Status',
                                    data: pumpDataExists ? data.flatMap(item => item.pump_data.map(pump => [
                                        new Date(pump.pump_created_at).getTime(), pump.pump_status
                                    ])) : [],
                                    dataLabels: {
                                        enabled: !pumpDataExists, // Enable only if no data
                                        format: 'No data available'
                                    }
                                }],
                                tooltip: {
                                    pointFormat: '<span style="color:{series.color}">{series.name}: <b>{point.y:.0f}</b><br/>' // 0 or 1
                                }
                            });

                            if (!pumpDataExists) {
                                console.log('Pump data is not available');
                            }
                        });
                    </script>
                @endforeach
            @endif
        </div>

    </div>

    </div>

@endsection
