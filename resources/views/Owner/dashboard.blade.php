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

        .data .chart-container {
            height: 350px;
            padding: 10px;
        }

        .data .chart-card {
            display: flex;
            flex-direction: column;
            height: 100%;
            margin-bottom: 20px;
        }

        .data .row {
            margin-bottom: 20px;
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
                                        <th> Name | Code</th>
                                        <th>Alert Activity</th>
                                        <th>Timestamp</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($towerLogs as $log)
                                        <tr>
                                            <td>{{ $count = $count + 1 }}</td>
                                            <td>{{ Crypt::decryptString($log->tower_name) }} |
                                                {{ Crypt::decryptString($log->tower_code) }}</td>
                                            <td>{{ Crypt::decryptString($log->activity) }}</td>
                                            <td>{{ Carbon::parse($log->created_at)->format('g:i A D m/d/Y') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if ($allDecryptedData)
            @foreach ($allDecryptedData as $id => $data)
                @php
                    $code = $data['towercode'];
                @endphp
                <div class="row">
                    <div class="col-md-12">
                        <div class="card chart-card">
                            <div class="card-header mb-0">
                                <h4>Tower Code: {{ $code }} | Plant: {{ $data['plantVar'] }}</h4>
                                <small>From: {{ $data['startDate'] }} To: {{ $data['endDate'] }}</small>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-4">
                                        <div class="chart-container" id="phChart-{{ $code }}"></div>
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <div class="chart-container" id="tempChart-{{ $code }}"></div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-4">
                                        <div class="chart-container" id="waterChart-{{ $code }}"></div>
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <div class="chart-container" id="pumpChart-{{ $code }}"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const phData = @json($data['pH_data']).map(entry => ({
                            created_at: entry.created_at,
                            value: Number(entry.value)
                        }));
                        const tempData = @json($data['temperature_data']).map(entry => ({
                            created_at: entry.created_at,
                            value: Number(entry.value)
                        }));
                        const nutrientData = @json($data['nutrient_data']).map(entry => ({
                            created_at: entry.created_at,
                            value: Number(entry.value)
                        }));
                        const pumpData = @json($data['pump_data']);

                        function createChart(chartId, title, yAxisTitle, data) {
                            if (data.length > 0) {
                                Highcharts.chart(chartId, {
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
                                    yAxis: {
                                        title: {
                                            text: yAxisTitle
                                        }
                                    },
                                    series: [{
                                        name: title,
                                        data: data.map(entry => [entry.created_at, entry.value]),
                                    }],
                                    tooltip: {
                                        pointFormat: '{series.name}: <b>{point.y:.2f}</b>'
                                    }
                                });
                            } else {
                                console.warn(`No data available for ${title}`);
                            }
                        }

                        createChart('phChart-{{ $code }}', 'pH Levels', 'pH Level', phData);
                        createChart('tempChart-{{ $code }}', 'Temperature', 'Temperature (°C)', tempData);
                        createChart('waterChart-{{ $code }}', 'Nutrient Solution Volume', 'Nutrient Solution Volume',
                            nutrientData);
                        createChart('pumpChart-{{ $code }}', 'Pump Status', 'Status', pumpData);
                    });
                </script>
            @endforeach
        @endif

    </div>

@endsection
