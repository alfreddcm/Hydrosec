@extends('Owner/sidebar')
@section('title', 'Tower ')
@section('content')
    @php
        use App\Models\Tower;
        use App\Models\Worker;

        use Illuminate\Support\Facades\Auth;
        use Illuminate\Support\Facades\Crypt;

        $towerinfo = Tower::where('id', $id)->first();
        $wokername = Worker::where('towerid', $towerinfo->id)->get();
    @endphp
    <style>
        canvas {
            height: max-content !important;
            width: 700px;
        }

        a {
            text-decoration: none;
            display: inline-block;
            padding: 3px 10px;
        }

        a:hover {
            background-color: #ddd;
            color: black;
        }

        .previous {
            background-color: #4495f1;
            color: white;
            border-radius: 3px;
            position: absolute;
            top: 0;
            left: 1%;
        }

        .title {
            text-transform: uppercase;
        }

        .maincard {
            margin: 30px;
            padding: 10px;
            align-self: center;
        }

        /*  */
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

        #thermometer {
            width: 50px;
            height: auto;
        }

        #nutrient-image {
            width: 50px;
            height: auto;
        }

        #ph-scale {
            width: 110px;
            height: auto;
        }

        .btnpop {
            position: absolute;
            bottom: 0;
            right: 1%;
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

        .nutcard {
            margin: 10px;
            width: 90%;
            height: max-content;
            border-radius: 10px;
            border: 1px solid #ddd;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <div class="con justify-content-center ">
        <div class="card text-center maincard">
            <div class="card-body justify-content-center">
                <div class="card-title">

                    <h2 class="title">
                        <a href="{{ route('ownermanagetower') }}" class="previous">&laquo;</a>

                        {{ Crypt::decryptString($towerinfo->name) }} <div id="online-status" style="display: inline-block;">
                        </div>

                    </h2>
                    @if ($wokername)
                        <p class="card-text">
                            Assigned User: <br>
                            @foreach ($wokername as $item)
                                {{ Crypt::decryptString($item->name) }} &nbsp;
                            @endforeach
                        </p>
                    @else
                        <p class="card-text">
                            No Worker set
                        </p>
                    @endif

                    <div class="row justify-content-center g-1">
                        <div class="col-sm-3">
                            <h5>Mode: <span id="modeCircle" class="circle"></span><span id="modeText"
                                    class="status-text">N/a</span></h5>
                        </div>
                        <div class="col-sm-3">
                            <h5>Status: <span id="statusCircle1" class="circle"></span><span id="statusText1"
                                    class="status-text">Inactive</span></h5>
                        </div>
                        <div class="col-sm-3">
                            <h5>Grow Lights : <span id="statusCircle" class="circle"></span><span id="statusText"
                                    class="status-text">Inactive</span></h5>
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
                                        data-column="temperature">

                                        <img src="{{ asset('images/icon/graph.png') }}" class="img-fluid rounded-top"
                                            alt="" style="height:30px" ; />
                                    </button>

                                    <div class="card-body justify-content-center g-4">
                                        <div class="icon ">
                                            <i class="bi bi-thermometer-half">
                                                <img id="thermometer" src="{{ asset('images/Temp/normal.png') }}"
                                                    alt="Thermometer">
                                            </i>
                                        </div>
                                        <div class="value">
                                            <h4 class="mt-3"><span id="temp-value">n/a</span></h4>
                                            <span id="temp-status">n/a</span>
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
                                        data-bs-target="#tempmodal" data-tower-id="{{ $towerinfo->id }}" data-column="pH">

                                        <img src="{{ asset('images/icon/graph.png') }}" class="img-fluid rounded-top"
                                            alt="" style="height:30px" ; />
                                    </button>
                                    <div class="icon ">
                                        <img id="ph-scale" src="{{ asset('images/ph/8.png') }}" alt="ph-scale">
                                    </div>

                                    <div class="value">
                                        <h4 class="mt-3"><span id="ph-value">n/a</span> <span id="ph-status">n/a</span>
                                        </h4>

                                    </div>
                                </center>
                            </div>
                        </div>

                        {{-- ph --}}
                        <div class="col-sm-4">
                            <div class="card sensor-card">
                                <center>
                                    <h3 class="mt-3">Nutrient Volume</h3>
                                    <button type="button" class="btn btnpop" data-bs-toggle="modal"
                                        data-bs-target="#tempmodal" data-tower-id="{{ $towerinfo->id }}"
                                        data-column="nutrientlevel">

                                        <img src="{{ asset('images/icon/graph.png') }}" class="img-fluid rounded-top"
                                            alt="" style="height:30px" ; />
                                    </button>

                                    <div class="card-body justify-content-center g-4">
                                        <div class="icon ">
                                            <img id="nutrient-image" src="{{ asset('images/Water/100.png') }}"
                                                alt="Nutient_volume">
                                        </div>
                                        <div class="value">
                                            <h4 class="mt-3"><span id="nutrient-value">n/a</span></h4>
                                            <span id="nutrient-status">n/a</span>
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
                        <h6>
                            {{ $towerinfo->startdate ? \Carbon\Carbon::parse($towerinfo->startdate)->format('F j, Y') : 'N/A' }}
                        </h6>
                    </div>
                    <div class="col-sm-3">
                        <h5>Expected Date Harvest</h5>

                        <h6>
                            {{ $towerinfo->enddate ? \Carbon\Carbon::parse($towerinfo->enddate)->format('F j, Y') : 'N/A' }}
                        </h6>
                    </div>
                </div>

                @if (is_null($towerinfo->startdate) && is_null($towerinfo->enddate))
                    <!-- Show Start Cycle Button and Modal if dates are null -->
                    <form>
                        @csrf
                        <input type="hidden" name="tower_id" value="{{ $towerinfo->id }}">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                            data-bs-target="#startCycleModal">
                            START CYCLE
                        </button>
                    </form>
                @elseif (Crypt::decryptString($towerinfo->status) == 4)
                    <!-- Show Restart Button if status is 4 -->
                    <form action="{{ route('tower.restart') }}" method="POST">
                        @csrf
                        <input type="hidden" name="tower_id" value="{{ $towerinfo->id }}">
                        <button type="submit" class="btn btn-primary mb-1">Restart</button>
                    </form>
                @else
                    <!-- Show Update Dates Button and Modal if dates are not null -->
                    <form>
                        @csrf
                        <input type="hidden" name="tower_id" value="{{ $towerinfo->id }}">
                        <button type="button" class="btn btn-warning" data-bs-toggle="modal"
                            data-bs-target="#updateCycleModal">
                            UPDATE CYCLE
                        </button>
                    </form>
                @endif

                <!-- Start Cycle Modal -->
                <div class="modal fade" id="startCycleModal" tabindex="-1" aria-labelledby="startCycleModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="{{ route('cycle') }}" method="POST">
                                @csrf
                                <input type="hidden" name="tower_id" value="{{ $towerinfo->id }}">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="startCycleModalLabel">Start New Cycle</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label for="days">Select Number of Days:</label>
                                        <select name="days" id="days" class="form-control">
                                            @for ($i = 15; $i <= 50; $i++)
                                                <option value="{{ $i }}">{{ $i }} days</option>
                                            @endfor
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary">Start Cycle</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Update Cycle Modal -->
                <div class="modal fade" id="updateCycleModal" tabindex="-1" aria-labelledby="updateCycleModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="{{ route('cycle') }}" method="POST">
                                @csrf
                                <input type="hidden" name="tower_id" value="{{ $towerinfo->id }}">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="updateCycleModalLabel">Update Cycle Dates</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label for="newDays">Select New Number of Days:</label>
                                        <select name="newDays" id="newDays" class="form-control">
                                            @for ($i = 1; $i <= 50; $i++)
                                                <option value="{{ $i }}">{{ $i }} days</option>
                                            @endfor
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer justify-content-center">
                                    <button type="submit" class="btn btn-warning">Update Dates</button>
                                </div>
                            </form>
                            <hr>
                            <!-- Separate the action buttons from the main form footer -->
                            <div class="d-flex justify-content-center mt-1 mb-2">
                                <form action="{{ route('tower.stop') }}" method="POST" class="me-2">
                                    @csrf
                                    <input type="hidden" name="tower_id" value="{{ $towerinfo->id }}">
                                    <button type="submit" class="btn btn-danger"
                                        onclick="return confirm('Are you sure you want to stop the cycle?');">Stop
                                        Cycle</button>
                                </form>

                                <form action="{{ route('tower.stopdis') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="tower_id" value="{{ $towerinfo->id }}">
                                    <button type="submit" class="btn btn-danger"
                                        onclick="return confirm('Are you sure you want to disable the tower?');">Disable
                                        Tower</button>
                                </form>

                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
        <center>
            <div class="card nutcard mt-2">
                <div class="card-body">
                    <h4 class="card-title">Nutrient Delivery Logs</h4>
                    <div class="table-container">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover table-borderless table-primary align-middle">
                                <thead class="table-light sticky-top ">
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
                        <canvas id="tempChart"> ><img src="{{ asset('images/loading.svg') }}" alt=""
                                style="height:30px" ; /></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://js.pusher.com/7.0/pusher.min.js"></script>

    <script>
        var towerId = @json($towerinfo->id);

        $(document).ready(function() {
            let tempChart = null;
            let modeStatInterval = null;

            function updateOnlineStatus(isOnline) {
                const statusIndicator = $('#online-status');
                const color = isOnline ? 'green' : 'red';
                statusIndicator.html(
                    `<div style="width: 10px; height: 10px; border-radius: 50%; background: ${color};"></div>`);
            }

            $('#tempmodal').on('shown.bs.modal', function(event) {
                let button = event.relatedTarget;
                if (!button) {
                    console.error('No related target found. Unable to get data attributes.');
                    return;
                }

                let towerId = button.getAttribute('data-tower-id');
                let column = button.getAttribute('data-column');

                if (tempChart) {
                    tempChart.destroy();
                }

                $.ajax({
                    url: `/get-data/${towerId}/${column}`,
                    method: 'GET',
                    success: function(response) {
                        if (response.error) {
                            console.error('Error fetching data:', response.error);
                            return;
                        }

                        const data = response.sensorData;
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

            function fetchInitialSensorData() {
                $.ajax({
                    url: '/sensor-data/' + towerId,
                    method: 'GET',
                    success: function(response) {
                        if (response.sensorData) {
                            const {
                                temperature,
                                nutrient_level,
                                pH,
                                light
                            } = response.sensorData;
                            updateNutrientImage(parseFloat(nutrient_level));
                            updatePhScaleImage(parseFloat(pH));
                            updateLightStatus(parseFloat(light));
                            updateThermometerImage(parseFloat(temperature));
                            updateOnlineStatus(false);
                        } else {
                            console.log('No data available');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error: ' + status + ' ' + error);
                    }
                });
            }

            function setupPusher() {
                // Initialize Pusher with the correct app key and cluster
                const pusher = new Pusher('3e52514a75529a62c062', {
                    cluster: 'ap1',
                    encrypted: true
                });
                console.log('Pusher initialized');

                // Check connection state
                pusher.connection.bind('state_change', function(states) {
                    console.log('Pusher connection state changed:', states);
                });

                // Bind to connection errors
                pusher.connection.bind('error', function(err) {
                    console.error('Pusher connection error:', JSON.stringify(err));
                });

                // Subscribe to the channel
                const channel = pusher.subscribe('sensor-data-channel.' + towerId);
                console.log('Subscribed to channel:', 'sensor-data-channel.' + towerId);

                // Bind to the sensor-data-updated event
                channel.bind('sensor-data-updated', function(data) {
                    console.log('Received Pusher data:', data);

                    if (data.towerId === towerId) {
                        console.log('Data matches the towerId:', towerId);
                        const {
                            temperature,
                            nutrient_level,
                            pH,
                            light
                        } = data;
                        console.log('Updating UI with data:', {
                            temperature,
                            nutrient_level,
                            pH,
                            light
                        });

                        updateNutrientImage(parseFloat(nutrient_level));
                        updatePhScaleImage(parseFloat(pH));
                        updateLightStatus(parseFloat(light));
                        updateThermometerImage(parseFloat(temperature));
                        updateOnlineStatus(true);
                    } else {
                        console.warn('Received data for different towerId:', data.towerId);
                    }
                });
            }

            function fetchPumpData() {
                $.ajax({
                    url: `/pump-data/${towerId}`,
                    method: 'GET',
                    success: function(data) {
                        var tbody = $('#sensor-data-body');
                        tbody.empty();

                        if (data.length === 0) {
                            tbody.append(
                                '<tr><td colspan="3" class="text-center">No records available.</td></tr>'
                            );
                        } else {
                            // Construct HTML for pump data
                            let rows = data.map((item, index) => {
                                let pumpStatus = parseInt(item.pump);
                                let status = pumpStatus === 1 ? 'Pump' : (pumpStatus === 0 ?
                                    'Not Pump' : 'Unknown');
                                let textColor = pumpStatus === 0 ? 'style="color: red;"' : '';
                                return `<tr class="table-light"><td>${index + 1}</td><td ${textColor}>${status}</td><td>${item.timestamp}</td></tr>`;
                            }).join('');
                            tbody.append(rows);
                        }
                    },
                    error: function() {
                        console.error('Failed to fetch pump data');
                    }
                });
            }

            function fetchModeStat() {
                $.ajax({
                    url: '/modestat/' + towerId,
                    method: 'GET',
                    success: function(response) {
                        if (response.modestat) {
                            const {
                                mode,
                                status
                            } = response.modestat;
                            updatemode(parseFloat(mode));
                            updatestatus(parseFloat(status));
                        } else {
                            console.log('No data available');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error: ' + status + ' ' + error);
                    }
                });
            }

            function startIntervals() {
                if (!modeStatInterval) {
                    modeStatInterval = setInterval(fetchModeStat, 5000);
                }
            }

            fetchInitialSensorData();
            fetchPumpData();
            setupPusher();
            startIntervals();
            setInterval(fetchPumpData, 5000);
        });

        function updateNutrientImage(nutrientVolume) {
            const nutrientImage = document.getElementById('nutrient-image');
            const statusText = document.getElementById('nutrient-status');
            const volumeValueElement = document.getElementById('nutrient-value');

            if (isNaN(nutrientVolume) || nutrientVolume === null) {
                nutrientImage.src = '{{ asset('images/Water/10.png') }}'; // Grayscale image
                statusText.textContent = "N/A";
                statusText.style.color = 'gray';
                volumeValueElement.style.color = 'gray';
                nutrientImage.style.filter = 'grayscale(100%)';
            } else {
                nutrientImage.style.filter = 'none'; // Reset filter for valid nutrient values
                volumeValueElement.textContent = `${nutrientVolume.toFixed(2)} L`;

                // Update image and status based on nutrient volume
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

                // Update status based on pH value
                if (phValue < 5.5) {
                    statusText.textContent = "Too Acidic";
                    statusText.style.color = 'red';
                } else if (phValue < 6.0) {
                    statusText.textContent = "Acidic";
                    statusText.style.color = 'orange';
                } else if (phValue > 7.0) {
                    statusText.textContent = "Too Basic";
                    statusText.style.color = 'purple';
                } else if (phValue > 6.5) {
                    statusText.textContent = "Basic";
                    statusText.style.color = 'blue';
                } else {
                    statusText.textContent = "Good";
                    statusText.style.color = 'green';
                }
            } else {
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

            // Update thermometer and status based on temperature
            if (temperature <= 18) {
                thermometer.src = '{{ asset('images/Temp/cold.png') }}';
                statusText.textContent = "Cold";
                statusText.style.color = 'blue';
            } else if (temperature > 18 && temperature <= 25) {
                thermometer.src = '{{ asset('images/Temp/cold.png') }}';
                statusText.textContent = "Cold (Optimal)";
                statusText.style.color = 'blue';
            } else if (temperature > 25 && temperature <= 30) {
                thermometer.src = '{{ asset('images/Temp/normal.png') }}';
                statusText.textContent = "Good";
                statusText.style.color = 'green';
            } else if (temperature > 31) {
                thermometer.src = '{{ asset('images/Temp/hot.png') }}';
                statusText.textContent = "Too Hot";
                statusText.style.color = 'darkred';
            } else {
                thermometer.src = '{{ asset('images/Temp/hot.png') }}';
                thermometer.style.filter = 'grayscale(100%)';
                tempValueElement.textContent = "N/A";
                statusText.style.color = 'gray';
            }
            tempValueElement.textContent = `${temperature.toFixed(2)} ℃`;
        }

        function updateLightStatus(status) {
            const circle = document.getElementById('statusCircle');
            const statusText = document.getElementById('statusText');

            // Update light status based on the status value
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

            // Update mode display based on the mode value
            switch (mode) {
                case 0:
                    modeText.textContent = 'Off';
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

            // Update status display based on the status value
            if (status === 1) {
                statusCircle.style.backgroundColor = 'green';
                statusText.textContent = 'Active';
            } else {
                statusCircle.style.backgroundColor = 'gray';
                statusText.textContent = 'Inactive';
            }
        }
    </script>

@endsection
