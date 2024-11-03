<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>@yield('title')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0/dist/chartjs-plugin-datalabels.min.js">
    </script>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        body {
            height: 100vh;
        }

        canvas {
            height: max-content !important;
            width: 700px;
        }

        .nutcard {
            margin: 10px;
            width: 90%;
            height: 200px;
            border-radius: 10px;
            border: 1px solid #ddd;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }

        .title {
            text-transform: uppercase;
        }

        .sensor-card {
            width: 90%;
            height: 200px;
            border-radius: 10px;
            border: 1px solid #ddd;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }

        .sensor-card .card-body {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .sensor-card .icon {
            font-size: 2rem;
            color: #007bff;
        }

        .btnpop {
            position: absolute;
            bottom: 0;
            right: 1%;
        }

        #thermometer,
        #nutrient-image,
        #ph-scale {
            height: auto;
        }

        #thermometer {
            width: 50px;
        }

        #nutrient-image {
            width: 50px;
        }

        #ph-scale {
            width: 110px;
        }

        .circle {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background-color: gray;
            vertical-align: middle;
        }

        .status-text {
            font-size: smaller;
            margin-left: 10px;
        }

        .table-container {
            max-height: 300px;
            overflow-y: auto;
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

        .logo {
            width: 30px;
            height: 30px;
        }

        .time {
            margin: 0;
            padding: 0;
        }

        .main .con {
            margin: 5%;
        }

        .user-info {
            display: flex;
            align-items: center;
            background-color: #ffffff;
            border: 1px solid #dddddd;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 10px;
        }

        .logout-btn {
            background-color: #d32f2f;
            border: none;
            color: #ffffff;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin-right: 15px;
        }

        .logout-btn:hover {
            background-color: #b71c1c;
        }

        h3 {
            margin: 0;
        }
    </style>
    <script src="https://js.pusher.com/7.0/pusher.min.js"></script>

</head>

<body>
    @php
        use App\Models\Tower;
        use App\Models\Worker;
        use App\Models\Owner;
        use Illuminate\Support\Facades\Auth;
        use Illuminate\Support\Facades\Crypt;

        $owner = Worker::where('id', Auth::id())->first();
        $ownername = Owner::where('id', $owner->OwnerID)->first();
        $towerinfo = Tower::where('OwnerID', $owner->OwnerID)->first();
    @endphp

    <div class="col main">
        <div class="dashboard-header mb-2">
            <div class="row">
                <div class="col">
                    <h2>Hydrosec: Hydroponics Monitoring System</h2>
                </div>
                <div class="col text-end align-content-center">
                    <h4><span id="current-time"> </span> | {{ \Carbon\Carbon::now()->format('D | M d, Y') }}</h4>

                </div>
            </div>
        </div>

        <div class="main">
            <div class="con justify-content-center">
                <div class="user-info">
                    <div class="col ">
                        <h3>Hi, <b>{{ Crypt::decryptString(Auth::user()->name) }}</b></h3>
                    </div>
                    <div class="col text-end">

                        <a href="{{ route('logout') }}"> <button type="submit" class="logout-btn">LOGOUT</button></a>
                    </div>
                </div>
                <div class="card text-center maincard">
                    <div class="card-body justify-content-center">
                        <div class="card-title">
                            <h2 class="title">{{ Crypt::decryptString($towerinfo->name) }}
                                <div id="online-status" style="display: inline-block;">
                                </div>
                            </h2>
                            <h6>
                    <p class="card-text  no-line-spacing">
                        <b> Plant: </b>
                        @if ($towerinfo->plantVar)
                            {{ Crypt::decryptString($towerinfo->plantVar) }}
                        @else
                            Not set
                        @endif
                    </p>

                </h6>
                            @if ($owner)
                                <p class="card-text">Owner Name: {{ Crypt::decryptString($ownername->name) }}</p>
                            @else
                                <p class="card-text">No Worker set</p>
                            @endif
                            <center>
                                <div> <span id="created_at"></span></div>
                            </center>

                            <div class="row justify-content-center g-1">
                                <div class="col-sm-3">
                                    <h5>Mode: <span id="modeCircle" class="circle"></span><span id="modeText"
                                            class="status-text">N/A</span></h5>
                                </div>
                                <div class="col-sm-3">
                                    <h5>Status: <span id="statusCircle1" class="circle"></span><span id="statusText1"
                                            class="status-text">Inactive</span></h5>
                                </div>
                                <div class="col-sm-3">
                                    <h5>Grow Lights : <span id="statusCircle" class="circle"></span><span
                                            id="statusText" class="status-text">Inactive</span></h5>
                                </div>
                            </div>

                            <div class="row g-3">

                                <!-- Temperature Card -->
                                <div class="col-sm-4">
                                    <div class="card sensor-card">
                                        <center>
                                            <h3 class="mt-3">Temperature</h3>

                                            <button type="button" class="btn btnpop" data-bs-toggle="modal"
                                                data-bs-target="#tempmodal" data-tower-id="{{ $towerinfo->id }}"
                                                data-column="temp">

                                                <img src="{{ asset('images/icon/graph.png') }}"
                                                    class="img-fluid rounded-top" alt="" style="height:30px" ; />
                                            </button>

                                            <div class="card-body justify-content-center g-4">
                                                <div class="icon ">
                                                    <i class="bi bi-thermometer-half">
                                                        <img id="thermometer"
                                                            src="{{ asset('images/Temp/normal.png') }}"
                                                            alt="Thermometer">
                                                    </i>
                                                </div>
                                                <div class="value">
                                                    <h4 class="mt-3"><span id="temp-value">n/a</span></h4>
                                                    <span id="temp-status">n/a</span> |
                                                    <span id="temp-con">...</span>
                                                </div>
                                            </div>
                                        </center>
                                    </div>
                                </div>

                                <!-- Humidity Card -->
                                <div class="col-sm-4">
                                    <div class="card sensor-card">
                                        <center>
                                            <h3 class="mt-3">pH Level</h3>
                                            <button type="button" class="btn btnpop" data-bs-toggle="modal"
                                                data-bs-target="#tempmodal" data-tower-id="{{ $towerinfo->id }}"
                                                data-column="ph">

                                                <img src="{{ asset('images/icon/graph.png') }}"
                                                    class="img-fluid rounded-top" alt="" style="height:30px" ; />
                                            </button>
                                            <div class="icon ">
                                                <img id="ph-scale" src="{{ asset('images/ph/8.png') }}" alt="ph-scale">
                                            </div>

                                            <div class="value">
                                                <h4 class="mt-3"><span id="ph-value">n/a</span></h4>
                                                <span id="ph-status">n/a</span> |
                                                <span id="ph-con">...</span>
                                            </div>
                                        </center>
                                    </div>
                                </div>

                                {{-- ph --}}
                                <div class="col-sm-4">
                                    <div class="card sensor-card">
                                        <center>
                                            <h3 class="mt-3">Nutrient Solution </h3>
                                            <button type="button" class="btn btnpop" data-bs-toggle="modal"
                                                data-bs-target="#tempmodal" data-tower-id="{{ $towerinfo->id }}"
                                                data-column="nutlevel">

                                                <img src="{{ asset('images/icon/graph.png') }}"
                                                    class="img-fluid rounded-top" alt="" style="height:30px" ; />
                                            </button>

                                            <div class="card-body justify-content-center g-4">
                                                <div class="icon ">
                                                    <img id="nutrient-image" src="{{ asset('images/Water/100.png') }}"
                                                        alt="Nutient_volume">
                                                </div>

                                                <div class="value">
                                                    <h4 class="mt-3"><span id="nutrient-value">n/a</span></h4>
                                                    <span id="nutrient-status">n/a</span> |
                                                    <span id="nutrient-con">...</span>
                                                </div>
                                            </div>
                                        </center>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row justify-content-center">
                            <div class="col-sm-3">
                                <h5>Date Started</h5>
                                <h6>{{ $towerinfo->datestarted ? \Carbon\Carbon::parse($towerinfo->datestarted)->format('M d, Y') : 'N/A' }}
                                </h6>
                            </div>
                            <div class="col-sm-3">
                                <h5>Date Ended</h5>
                                <h6>{{ $towerinfo->dateended ? \Carbon\Carbon::parse($towerinfo->dateended)->format('M d, Y') : 'N/A' }}
                                </h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <center>
            <div class="card nutcard">
                <div class="card-body">
                    <h4 class="card-title">Nutrient Delivery Logs</h4>
                    <div class="table-container">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover table-borderless table-primary align-middle">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th>No.</th>
                                        <th>Status</th>
                                        <th>Timestamps</th>
                                    </tr>
                                </thead>
                                <tbody id="sensor-data-body" class="table-group-divider">
                                    <tr>
                                        <td colspan="3" class="text-center">No records available.</td>
                                    </tr>
                                </tbody>
                                <tfoot>

                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </center>

    </div>

    <!-- Modal -->
    <div class="modal fade " id="tempmodal" tabindex="-1" role="dialog" aria-labelledby="modalTitleId"
        aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-center" id="modalTitleId">
                        Graph
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="container-fluid">
                        <canvas id="tempChart"><img src="{{ asset('images/loading.svg') }}" alt="" style="height:30px"
                                ; /></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    let firstFetch = false;

    function updateTime() {
        const now = new Date();
        let hours = now.getHours();
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        const ampm = hours >= 12 ? 'PM' : 'AM';

        hours = hours % 12;
        hours = hours ? hours : 12;
        hours = String(hours).padStart(2, '0');

        document.getElementById('current-time').textContent = `${hours}:${minutes}:${seconds} ${ampm}`;
    }

    updateTime();
    setInterval(updateTime, 1000);


    var towerId = @json($towerinfo->id);

    var id = {{ $towerinfo->id }};
    $(document).ready(function () {
        var towerId = @json($towerinfo->id);
        let tempChart = null;
        let sensorDataInterval = null;
        let modeStatInterval = null;


        function load() {
            console.log('Livewire component has been loaded');

            fetchSensorData2();
            const datetime = document.getElementById('datetime');
            const now = new Date();

            const options = {
                timeZone: 'Asia/Manila', // Set the timezone to Asia/Manila
                hour: 'numeric',
                minute: 'numeric',
                second: 'numeric',
                hour12: true, // Use 12-hour format
                weekday: 'short', // Short form of the day (e.g., Mon, Tue)
                year: 'numeric',
                month: 'numeric',
                day: 'numeric',
            };

            const pusher = new Pusher('3e52514a75529a62c062', {
                cluster: 'ap1',
                encrypted: true
            });
            pusher.connection.bind('connected', function () {
                console.log('Pusher connection established');
            });
            pusher.connection.bind('disconnected', function () {
                console.log('Pusher connection disconnected');
            });
            pusher.connection.bind('failed', function () {
                console.log('Pusher connection failed');
            });

            const channel = pusher.subscribe('tower.' + towerId);
            channel.bind('SensorDataUpdated', function (data) {
                console.log('Successfully subscribed to channel:', 'tower.' + towerId);
                console.log('Real-time sensor data received:', data.sensorData);

                const sensorData = data.sensorData;

                if (data.sensorData && data) {
                    // Log the received sensor data
                    console.log('Updating sensor data:', sensorData);
                    updateNutrientImage(parseFloat(sensorData.nutrient_level));
                    updatePhScaleImage(parseFloat(sensorData.ph));
                    updateLightStatus(parseFloat(sensorData.light));
                    updateThermometerImage(parseFloat(sensorData.temperature));
                    updateOnlineStatus(true);

                    datetime.textContent = now.toLocaleString('en-US', options);

                } else {
                    console.log('No data available');
                }
            });
        }

        function updateOnlineStatus(isOnline) {
            const statusIndicator = $('#online-status');
            const color = isOnline ? 'green' : 'red';
            statusIndicator.html(
                `<div style="width: 10px; height: 10px; border-radius: 50%; background: ${color};"></div>`
            );
        }

<<<<<<< HEAD
        var towerId = @json($towerinfo->id);
=======

        $('#tempmodal').on('shown.bs.modal', function (event) {
            let button = event.relatedTarget;
            if (!button) {
                console.error('No related target found. Unable to get data attributes.');
                return;
            }
>>>>>>> a4f29473e5789dc53ce3900707f77a203e8c0482

            let towerId = button.getAttribute('data-tower-id');
            let column = button.getAttribute('data-column');

            if (tempChart) {
                tempChart.destroy();
            }


<<<<<<< HEAD
            function load() {
                console.log('Livewire component has been loaded');

                const datetime = document.getElementById('datetime');
                const now = new Date();

                const options = {
                    timeZone: 'Asia/Manila', // Set the timezone to Asia/Manila
                    hour: 'numeric',
                    minute: 'numeric',
                    second: 'numeric',
                    hour12: true, // Use 12-hour format
                    weekday: 'short', // Short form of the day (e.g., Mon, Tue)
                    year: 'numeric',
                    month: 'numeric',
                    day: 'numeric',
                };

                const pusher = new Pusher('3e52514a75529a62c062', {
                    cluster: 'ap1',
                    encrypted: true
                });
                pusher.connection.bind('connected', function() {
                    console.log('Pusher connection established');
                });
                pusher.connection.bind('disconnected', function() {
                    console.log('Pusher connection disconnected');
                });
                pusher.connection.bind('failed', function() {
                    console.log('Pusher connection failed');
                });

                const channel = pusher.subscribe('tower.' + towerId);
                channel.bind('SensorDataUpdated', function(data) {
                    console.log('Successfully subscribed to channel:', 'tower.' + towerId);
                    console.log('Real-time sensor data received:', data.sensorData);

                    const sensorData = data.sensorData;

                    if (data.sensorData && data) {
                        // Log the received sensor data
                        console.log('Updating sensor data:', sensorData);
                        updateNutrientImage(parseFloat(sensorData.nutrient_level));
                        updatePhScaleImage(parseFloat(sensorData.ph));
                        updateLightStatus(parseFloat(sensorData.light));
                        updateThermometerImage(parseFloat(sensorData.temperature));
                        updateOnlineStatus(true);

                        datetime.textContent = now.toLocaleString('en-US', options);

                    } else {
                        console.log('No data available');
=======
            $.ajax({
                url: `/Worker/get-data/${towerId}/${column}`,
                method: 'GET',
                success: function (response) {
                    if (response.error) {
                        console.error('Error fetching data:', response.error);
                        return;
>>>>>>> a4f29473e5789dc53ce3900707f77a203e8c0482
                    }

                    const data = response.sensorData;
                    if (data.length === 0) {
                        console.error('No data available for the selected tower.');
                        return;
                    }

                    const labels = data.map(item => item.timestamp);
                    const values = data.map(item => item.value);
                    const ctx = document.getElementById('tempChart');

                    if (!ctx) {
                        console.error('Canvas element not found.');
                        return;
                    }

<<<<<<< HEAD
                let towerId = button.getAttribute('data-tower-id');
                let column = button.getAttribute('data-column');

                if (tempChart) {
                    tempChart.destroy();
                }

                // Show loading indicator (if implemented)

                $.ajax({
                    url: `/Worker/get-data/${towerId}/${column}`,
                    method: 'GET',
                    success: function(response) {
                        if (response.error) {
                            console.error('Error fetching data:', response.error);
                            return;
                        }

                        const data = response.sensorData;
                        if (data.length === 0) {
                            console.error('No data available for the selected tower.');
                            return;
                        }

                        const labels = data.map(item => item.timestamp);
                        const values = data.map(item => item.value);
                        const ctx = document.getElementById('tempChart');

                        if (!ctx) {
                            console.error('Canvas element not found.');
                            return;
                        }

                        tempChart = new Chart(ctx.getContext('2d'), {
                            type: 'line',
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: 'Sensor Data',
                                    data: values,
                                    borderColor: 'rgba(75, 192, 192, 1)',
                                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                scales: {
                                    x: {
                                        type: 'category',
                                        title: {
                                            display: true,
                                            text: 'Time'
                                        }
                                    },
                                    y: {
                                        title: {
                                            display: true,
                                            text: 'Value'
                                        }
                                    }
                                }
                            }
                        });
                    },
                    error: function(xhr) {
                        console.error('An error occurred:', xhr.responseText);
                    }
                });
            });
 
=======
                    tempChart = new Chart(ctx.getContext('2d'), {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Sensor Data',
                                data: values,
                                borderColor: 'rgba(75, 192, 192, 1)',
                                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            scales: {
                                x: {
                                    type: 'category',
                                    title: {
                                        display: true,
                                        text: 'Time'
                                    }
                                },
                                y: {
                                    title: {
                                        display: true,
                                        text: 'Value'
                                    }
                                }
                            }
                        }
                    });
                },
                error: function (xhr) {
                    console.error('An error occurred:', xhr.responseText);
                }
            });
        });
>>>>>>> a4f29473e5789dc53ce3900707f77a203e8c0482

        // Fetch pump data
        function fetchPumpData() {
            $.ajax({
                url: `/Worker/pump-data/${towerId}`,
                method: 'GET',
                success: function (data) {
                    var tbody = $('#sensor-data-body');
                    tbody.empty();

                    if (data.length === 0) {
                        tbody.append(
                            '<tr><td colspan="3" class="text-center">No records available.</td></tr>'
                        );
                    } else {
                        $.each(data, function (index, item) {
                            // Ensure item.pump is treated as a number
                            var pumpStatus = parseInt(item.pump);
                            var status;
                            var textColor = '';

                            if (pumpStatus === 1) {
                                status = 'Pump';
                            } else if (pumpStatus === 0) {
                                status = 'Not Pump';
                                textColor = 'style="color: red;"';
                            } else {
                                status = 'Unknown';
                            }

                            var row = `<tr class="table-light">
                                            <td>${index + 1}</td>
                                            <td ${textColor}>${status}</td>
                                            <td>${item.timestamp}</td>
                                        </tr>`;
                            tbody.append(row);
                        });
                    }
                },
                error: function () {
                    console.error('Failed to fetch pump data');
                }
            });
        }

        function fetchModeStat() {
            $.ajax({
                url: '/Worker/modestat/' + towerId,
                method: 'GET',
                success: function (response) {
                    if (response.modestat) {
                        const mode = parseFloat(response.modestat.mode);
                        const status = parseFloat(response.modestat.status);
                        updatemode(mode);
                        updatestatus(status);
                    } else {
                        console.log('No data available');
                    }
                },
                error: function (xhr, status, error) {
                    console.error('AJAX Error: ' + status + ' ' + error);
                }
            });
        }

        // Start intervals
        function startIntervals() {
            if (!sensorDataInterval) {
                sensorDataInterval = setInterval(fetchSensorData2, 10000);
            }
<<<<<<< HEAD

            function stopIntervals() {
                clearInterval(sensorDataInterval);
                clearInterval(modeStatInterval);
                sensorDataInterval = null;
                modeStatInterval = null;
            }
            load();
            fetchPumpData();
            startIntervals();

            setInterval(fetchPumpData, 10000);
        });


        function updateNutrientImage(nutrientVolume) {
            const nutrientImage = document.getElementById('nutrient-image');
            const statusText = document.getElementById('nutrient-status');
            const volumeValueElement = document.getElementById('nutrient-value');
            const volcon = document.getElementById('nutrient-con');


            if (isNaN(nutrientVolume) || nutrientVolume === null) {
                nutrientImage.src = '{{ asset('images/Water/10.png') }}';
                statusText.textContent = "N/A";
                statusText.style.color = 'gray';
                volumeValueElement.style.color = 'gray';
                nutrientImage.style.filter = 'grayscale(100%)';
                volcon.textContent = '';
                volcon.style.color = '';
            } else {
                nutrientImage.style.filter = 'none';
                volumeValueElement.textContent = `${nutrientVolume.toFixed(2)} L`;

                if (nutrientVolume >= 20) {
                    nutrientImage.src = '{{ asset('images/Water/100.png') }}';
                    statusText.textContent = "Full";
                    statusText.style.color = 'blue';
                    volcon.textContent = 'Good';
                    volcon.style.color = '';
                } else if (nutrientVolume >= 17) {
                    nutrientImage.src = '{{ asset('images/Water/80.png') }}';
                    statusText.textContent = "85%";
                    statusText.style.color = 'blue';
                    volcon.textContent = 'Good';
                    volcon.style.color = '';
                } else if (nutrientVolume >= 15) {
                    nutrientImage.src = '{{ asset('images/Water/70.png') }}';
                    statusText.textContent = "75%";
                    statusText.style.color = 'blue';
                    volcon.textContent = 'Good';
                    volcon.style.color = '';
                } else if (nutrientVolume >= 12) {
                    nutrientImage.src = '{{ asset('images/Water/60.png') }}';
                    statusText.textContent = "60%";
                    statusText.style.color = 'blue';
                    volcon.textContent = 'Good';
                    volcon.style.color = '';
                } else if (nutrientVolume >= 10) {
                    nutrientImage.src = '{{ asset('images/Water/50.png') }}';
                    statusText.textContent = "50%";
                    statusText.style.color = 'blue';
                    volcon.textContent = 'Good';
                    volcon.style.color = '';
                } else if (nutrientVolume >= 7) {
                    nutrientImage.src = '{{ asset('images/Water/30.png') }}';
                    statusText.textContent = "35%";
                    statusText.style.color = 'orange';
                    volcon.textContent = 'Good';
                    volcon.style.color = '';
                } else if (nutrientVolume >= 5) {
                    nutrientImage.src = '{{ asset('images/Water/20.png') }}';
                    statusText.textContent = "25%";
                    statusText.style.color = 'orange';
                    volcon.textContent = 'Critical';
                    volcon.style.color = 'red';
                } else {
                    nutrientImage.src = '{{ asset('images/Water/10.png') }}';
                    statusText.textContent = "Low";
                    statusText.style.color = 'green';
                    volcon.textContent = 'Critical';
                    volcon.style.color = 'red';
                }

            }
        }

        function updatePhScaleImage(phValue) {
            const phScale = document.getElementById('ph-scale');
            const statusText = document.getElementById('ph-status');
            const phValueElement = document.getElementById('ph-value');
            const phcon = document.getElementById('ph-con');


            phValueElement.textContent = `${phValue.toFixed(2)}`;

            if (phValue >= 0 && phValue <= 14) {
                phScale.src = `{{ asset('images/ph/${Math.floor(phValue)}.png') }}`;
                phScale.style.filter = 'none';
                // Update status based on pH value
                if (phValue < 5.5) {
                    statusText.textContent = "Acidic";
                    statusText.style.color = 'orange';
                    phcon.textContent = "Critical";
                    phcon.style.color = 'red';

                } else if (phValue >= 5.5 && phValue <= 6.5) {
                    statusText.textContent = "Acidic";
                    statusText.style.color = 'green';
                    phcon.textContent = "Ideal";
                    phcon.style.color = 'black';

                } else if (phValue > 6.5 && phValue < 7) {
                    statusText.textContent = "Acidic";
                    statusText.style.color = 'orange';
                    phcon.textContent = "Critical";
                    phcon.style.color = 'red';

                } else if (phValue == 7) {
                    statusText.textContent = "Neutral";
                    statusText.style.color = 'blue';
                    phcon.textContent = "Critical";
                    phcon.style.color = 'red';

                } else if (phValue > 7) {
                    statusText.textContent = "Alkaline";
                    statusText.style.color = 'purple';
                    phcon.textContent = "Critical";
                    phcon.style.color = 'red';

                } else {
                    statusText.textContent = "Unknown";
                    statusText.style.color = 'gray';
                    phcon.textContent = "Unknown";
                    phcon.style.color = 'gray';
                }


            } else {

                phScale.src = `{{ asset('images/ph/7.png') }}`;
                statusText.textContent = "N/A";
                statusText.style.color = 'black';
                phValueElement.style.color = 'black';
                phScale.style.filter = 'grayscale(100%)';
            }
=======
            if (!modeStatInterval) {
                modeStatInterval = setInterval(fetchModeStat, 10000);
            }
        }

        function stopIntervals() {
            clearInterval(sensorDataInterval);
            clearInterval(modeStatInterval);
            sensorDataInterval = null;
            modeStatInterval = null;
>>>>>>> a4f29473e5789dc53ce3900707f77a203e8c0482
        }
        load();
        fetchPumpData();
        startIntervals();

<<<<<<< HEAD
        function updateThermometerImage(temperature) {
            const thermometer = document.getElementById('thermometer');
            const statusText = document.getElementById('temp-status');
            const tempValueElement = document.getElementById('temp-value');
            const tempcon = document.getElementById('temp-con');

=======
        setInterval(fetchPumpData, 10000);
    });

    function updateNutrientImage(nutrientVolume) {
        const nutrientImage = document.getElementById('nutrient-image');
        const statusText = document.getElementById('nutrient-status');
        const volumeValueElement = document.getElementById('nutrient-value');
>>>>>>> a4f29473e5789dc53ce3900707f77a203e8c0482

        if (isNaN(nutrientVolume) || nutrientVolume === null) {
            nutrientImage.src = '{{ asset('images/Water/10.png') }}';
            statusText.textContent = "N/A";
            statusText.style.color = 'gray';
            volumeValueElement.style.color = 'gray';
            nutrientImage.style.filter = 'grayscale(100%)';
        } else {

<<<<<<< HEAD
            if (temperature <= 20) {
                thermometer.src = '{{ asset('images/Temp/cold.png') }}';
                statusText.textContent = "Cold";
                statusText.style.color = 'blue';
                tempcon.textContent = "Critical";
                tempcon.style.color = 'red';
            } else if (temperature > 20 && temperature < 25) {
                thermometer.src = '{{ asset('images/Temp/cold.png') }}';
                statusText.textContent = "Mild";
                statusText.style.color = 'lightblue';
                tempcon.textContent = "Critical";
                tempcon.style.color = 'red';
            } else if (temperature >= 25 && temperature <= 30) {
                thermometer.src = '{{ asset('images/Temp/normal.png') }}';
                statusText.textContent = "Ideal";
                statusText.style.color = 'green';
                tempcon.textContent = "";
                tempcon.style.color = 'black';
            } else if (temperature > 30 && temperature <= 40) {
                thermometer.src = '{{ asset('images/Temp/hot.png') }}';
                statusText.textContent = "Warm";
                statusText.style.color = 'orange';
                tempcon.textContent = "Critical";
                tempcon.style.color = 'red';
            } else if (temperature > 40) {
                thermometer.src = '{{ asset('images/Temp/hot.png') }}';
                statusText.textContent = "Hot";
                statusText.style.color = 'darkred';
                tempcon.textContent = "Critical";
                tempcon.style.color = 'red';
=======
            nutrientImage.style.filter = 'none';
            volumeValueElement.textContent = `${nutrientVolume.toFixed(2)} L`;

            if (nutrientVolume >= 20) {
                nutrientImage.src = '{{ asset('images/Water/100.png') }}';
                statusText.textContent = "Full";
                statusText.style.color = 'blue';
            } else if (nutrientVolume >= 17) {
                nutrientImage.src = '{{ asset('images/Water/80.png') }}';
                statusText.textContent = "85%";
                statusText.style.color = 'blue';
            } else if (nutrientVolume >= 15) {
                nutrientImage.src = '{{ asset('images/Water/70.png') }}';
                statusText.textContent = "75%";
                statusText.style.color = 'blue';
            } else if (nutrientVolume >= 12) {
                nutrientImage.src = '{{ asset('images/Water/60.png') }}';
                statusText.textContent = "60%";
                statusText.style.color = 'blue';
            } else if (nutrientVolume >= 10) {
                nutrientImage.src = '{{ asset('images/Water/50.png') }}';
                statusText.textContent = "50%";
                statusText.style.color = 'blue';
            } else if (nutrientVolume >= 7) {
                nutrientImage.src = '{{ asset('images/Water/30.png') }}';
                statusText.textContent = "35%";
                statusText.style.color = 'orange';
            } else if (nutrientVolume >= 5) {
                nutrientImage.src = '{{ asset('images/Water/20.png') }}';
                statusText.textContent = "25%";
                statusText.style.color = 'orange';
            } else {
                nutrientImage.src = '{{ asset('images/Water/10.png') }}';
                statusText.textContent = "Low";
                statusText.style.color = 'green';
            }
        }
    }

    function updatePhScaleImage(phValue) {
        const phScale = document.getElementById('ph-scale');
        const statusText = document.getElementById('ph-status');
        const phValueElement = document.getElementById('ph-value');

        phValueElement.textContent = `${phValue.toFixed(2)}`;

        if (phValue >= 0 && phValue <= 14) {
            phScale.src = `{{ asset('images/ph/${Math.floor(phValue)}.png') }}`;
            phScale.style.filter = 'none';
            // Update status based on pH value
            if (phValue < 5.6) {
                statusText.textContent = "Acidic";
                statusText.style.color = 'orange';
            } else if (phValue >= 5.6 && phValue < 7) {
                statusText.textContent = "Good";
                statusText.style.color = 'green';
            } else if (phValue == 7) {
                statusText.textContent = "Neutral";
                statusText.style.color = 'blue';
            } else if (phValue > 7) {
                statusText.textContent = "Alkaline";
                statusText.style.color = 'purple';
>>>>>>> a4f29473e5789dc53ce3900707f77a203e8c0482
            } else {
                statusText.textContent = "Unknown";
                statusText.style.color = 'gray';
<<<<<<< HEAD
                tempValueElement.style.color = 'gray';
                tempcon.textContent = "...";
                tempcon.style.color = 'black';
            }


            // If valid temperature, update the temp value
            if (temperature !== null) {
                tempValueElement.textContent = `${temperature.toFixed(2)} ℃`;
            }
        }

        function updateLightStatus(status) {
            const circle = document.getElementById('statusCircle');
            const statusText = document.getElementById('statusText');

            if (status === 1) {
                circle.style.backgroundColor = 'green';
                statusText.textContent = 'Active';

            } else {
                circle.style.backgroundColor = 'gray';
                statusText.textContent = 'Inactive';

            }
=======
            }

        } else {
            // Handle invalid pH range
            phScale.src = `{{ asset('images/ph/7.png') }}`;
            statusText.textContent = "N/A";
            statusText.style.color = 'black';
            phValueElement.style.color = 'black';
            phScale.style.filter = 'grayscale(100%)';
        }
    }


    function updateThermometerImage(temperature) {
        const thermometer = document.getElementById('thermometer');
        const statusText = document.getElementById('temp-status');
        const tempValueElement = document.getElementById('temp-value');

        thermometer.style.filter = 'none'; // Reset filter for valid temperature values

        if (temperature < 20) {
            thermometer.src = '{{ asset('images/Temp/cold.png') }}';
            statusText.textContent = "Cold";
            statusText.style.color = 'blue';
        } else if (temperature >= 20 && temperature <= 25) {
            thermometer.src = '{{ asset('images/Temp/cold.png') }}';
            statusText.textContent = "Mild";
            statusText.style.color = 'lightblue';
        } else if (temperature > 25 && temperature <= 30) {
            thermometer.src = '{{ asset('images/Temp/normal.png') }}';
            statusText.textContent = "Good";
            statusText.style.color = 'green';
        } else if (temperature > 30 && temperature <= 40) {
            thermometer.src = '{{ asset('images/Temp/warm.png') }}';
            statusText.textContent = "Warm";
            statusText.style.color = 'orange';
        } else if (temperature > 40) {
            thermometer.src = '{{ asset('images/Temp/hot.png') }}';
            statusText.textContent = "Hot";
            statusText.style.color = 'darkred';
        } else {
            thermometer.src = '{{ asset('images/Temp/hot.png') }}';
            thermometer.style.filter = 'grayscale(100%)';
            tempValueElement.textContent = "N/A";
            statusText.textContent = "Unknown";
            statusText.style.color = 'gray';
            tempValueElement.style.color = 'gray';
        }

        // If valid temperature, update the temp value
        if (temperature !== null) {
            tempValueElement.textContent = `${temperature.toFixed(2)} ℃`;
>>>>>>> a4f29473e5789dc53ce3900707f77a203e8c0482
        }
    }



    function updateLightStatus(status) {
        const circle = document.getElementById('statusCircle');
        const statusText = document.getElementById('statusText');

        if (status === 1) {
            circle.style.backgroundColor = 'green';
            statusText.textContent = 'Active';

        } else {
            circle.style.backgroundColor = 'gray';
            statusText.textContent = 'Inactive';

        }
    }

    function updatemode(mode) {
        const modeText = document.getElementById('modeText');
        const modeCircle = document.getElementById('modeCircle');

        switch (mode) {
            case 0:
                modeText.textContent = 'Blackout';
                modeCircle.style.backgroundColor = 'gray';
                break;
            case 1:
                modeText.textContent = 'Day Mode';
                modeCircle.style.backgroundColor = 'yellow';
                break;
            case 2:
                modeText.textContent = 'Night Mode';
                modeCircle.style.backgroundColor = 'blue';
                break;
            default:
                modeText.textContent = 'Unknown';
                modeCircle.style.backgroundColor = 'red';
                break;
        }
    }

    function updatestatus(status) {
        const statusCircle = document.getElementById('statusCircle1');
        const statusText = document.getElementById('statusText1');

        if (status === 1) {
            statusCircle.style.backgroundColor = 'green';
            statusText.textContent = 'Active';
        } else {
            statusCircle.style.backgroundColor = 'gray';
            statusText.textContent = 'Inactive';
        }
    }
</script>

</html>