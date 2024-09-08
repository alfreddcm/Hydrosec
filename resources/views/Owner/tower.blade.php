@extends('Owner/sidebar')
@section('title', 'Tower ')
@section('content')
    @php
        use App\Models\Tower;
        use App\Models\Worker;

        use Illuminate\Support\Facades\Auth;
        use Illuminate\Support\Facades\Crypt;

        $towerinfo = Tower::where('OwnerID', Auth::id())->first();
        $wokername = Worker::where('OwnerID', Auth::id())->first();
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
            height: 200px;
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

                        {{ Crypt::decryptString($towerinfo->name) }}
                    </h2>
                    @if ($wokername)
                        <p class="card-text">
                            Assgned User: {{ Crypt::decryptString($wokername->name) }}
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
                                    <h3 class="mt-3">Nutrient Level</h3>
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
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-warning">Update Dates</button>
                                </div>
                            </form>

                            <form action="{{ route('tower.stop') }}" method="POST">
                                @csrf
                                <input type="hidden" name="tower_id" value="{{ $towerinfo->id  }}">
                                <button type="submit" class="btn btn-danger mb-3">Stop Cycle</button>
                            </form>
                            
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
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th>No.</th>
                                        <th>Status</th>
                                        <th>Timestamps</th>
                                    </tr>
                                </thead>
                                <tbody id="sensor-data-body" class="table-group-divider">
                                    <tr><td colspan="3" class="text-center">No records available.</td></tr>
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
                        <canvas id="tempChart"> <img src="{{ asset('images/icon/loading.gif') }}"
                                class="img-fluid rounded-top" alt="" style="height:30px" ; /></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <script>
        var towerId = @json($towerinfo->id);

        var id = {{ $towerinfo->id }};
        $(document).ready(function() {
            var towerId = @json($towerinfo->id);
            let tempChart = null;
            let sensorDataInterval = null;
            let modeStatInterval = null;

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

                        const chartCtx = ctx.getContext('2d');
                        if (!chartCtx) {
                            console.error('Unable to get canvas context.');
                            return;
                        }

                        tempChart = new Chart(chartCtx, {
                            labels: labels,
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

            // Fetch sensor data and update images
            function fetchSensorData2() {
                $.ajax({
                    url: '/sensor-data/' + towerId,
                    method: 'GET',
                    success: function(response) {
                        if (response.sensorData) {
                            const Temperature = parseFloat(response.sensorData.temperature);
                            const NutrientVolume = parseFloat(response.sensorData.nutrient_level);
                            const pHlevel = parseFloat(response.sensorData.pH);
                            const light = parseFloat(response.sensorData.light);

                            updateNutrientImage(NutrientVolume);
                            updatePhScaleImage(pHlevel);
                            updateLightStatus(light);
                            updateThermometerImage(Temperature);

                        } else {
                            console.log('No data available');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error: ' + status + ' ' + error);
                    }
                });
            }

            // Fetch pump data
            function fetchPumpData() {
    $.ajax({
        url: `/pump-data/${towerId}`,
        method: 'GET',
        success: function(data) {
            var tbody = $('#sensor-data-body');
            tbody.empty();

            if (data.length === 0) {
                tbody.append('<tr><td colspan="3" class="text-center">No records available.</td></tr>');
            } else {
                $.each(data, function(index, item) {
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
                        // In case there's an unexpected value
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
                            const mode = parseFloat(response.modestat.mode);
                            const status = parseFloat(response.modestat.status);
                            updatemode(mode);
                            updatestatus(status);
                        } else {
                            console.log('No data available');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error: ' + status + ' ' + error);
                    }
                });
            }

            // Start intervals
            function startIntervals() {
                if (!sensorDataInterval) {
                    sensorDataInterval = setInterval(fetchSensorData2, 10000);
                }
                if (!modeStatInterval) {
                    modeStatInterval = setInterval(fetchModeStat, 10000);
                }
            }

            // Stop intervals
            function stopIntervals() {
                clearInterval(sensorDataInterval);
                clearInterval(modeStatInterval);
                sensorDataInterval = null;
                modeStatInterval = null;
            }
            fetchPumpData();
            startIntervals();

            // Refresh pump data every 30 seconds
            setInterval(fetchPumpData, 10000);
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

                if (nutrientVolume >= 20) {
                    nutrientImage.src = '{{ asset('images/Water/100.png') }}';
                    statusText.textContent = "Full";
                    statusText.style.color = 'blue';
                    volumeValueElement.style.color = 'blue';
                } else if (nutrientVolume >= 17) {
                    nutrientImage.src = '{{ asset('images/Water/80.png') }}';
                    statusText.textContent = "85%";
                    statusText.style.color = 'blue';
                    volumeValueElement.style.color = 'blue';
                } else if (nutrientVolume >= 15) {
                    nutrientImage.src = '{{ asset('images/Water/70.png') }}';
                    statusText.textContent = "75%";
                    statusText.style.color = 'blue';
                    volumeValueElement.style.color = 'blue';
                } else if (nutrientVolume >= 12) {
                    nutrientImage.src = '{{ asset('images/Water/60.png') }}';
                    statusText.textContent = "60%";
                    statusText.style.color = 'blue';
                    volumeValueElement.style.color = 'blue';
                } else if (nutrientVolume >= 10) {
                    nutrientImage.src = '{{ asset('images/Water/50.png') }}';
                    statusText.textContent = "50%";
                    statusText.style.color = 'blue';
                    volumeValueElement.style.color = 'blue';
                } else if (nutrientVolume >= 7) {
                    nutrientImage.src = '{{ asset('images/Water/30.png') }}';
                    statusText.textContent = "35%";
                    statusText.style.color = 'orange';
                    volumeValueElement.style.color = 'orange';
                } else if (nutrientVolume >= 5) {
                    nutrientImage.src = '{{ asset('images/Water/20.png') }}';
                    statusText.textContent = "25%";
                    statusText.style.color = 'orange';
                    volumeValueElement.style.color = 'orange';
                } else {
                    nutrientImage.src = '{{ asset('images/Water/10.png') }}';
                    statusText.textContent = "Empty";
                    statusText.style.color = 'gray';
                    volumeValueElement.style.color = 'gray';
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
                if (phValue < 5.0) {
                    statusText.textContent = "Acidic";
                    statusText.style.color = 'red';
                    phValueElement.style.color = 'red';
                    phScale.style.filter = 'none';
                } else if (phValue === 7) {
                    statusText.textContent = "Neutral";
                    statusText.style.color = 'green';
                    phValueElement.style.color = 'green';
                    phScale.style.filter = 'none';
                } else {
                    statusText.textContent = "Good";
                    statusText.style.color = 'blue';
                    phValueElement.style.color = 'blue';
                    phScale.style.filter = 'none';
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

            if (temperature <= 18) {
                thermometer.src = '{{ asset('images/Temp/cold.png') }}';
                statusText.textContent = "Cold";
                statusText.style.color = 'blue';
                tempValueElement.style.color = 'blue';
                tempValueElement.textContent = `${temperature.toFixed(2)} ℃`;

            } else if (temperature > 18 && temperature <= 25) {
                thermometer.src = '{{ asset('images/Temp/normal.png') }}';
                statusText.textContent = "Normal (Optimal)";
                statusText.style.color = 'gray';
                tempValueElement.style.color = 'gray';
                tempValueElement.textContent = `${temperature.toFixed(2)} ℃`;

            } else if (temperature > 25 && temperature <= 30) {
                thermometer.src = '{{ asset('images/Temp/hot.png') }}';
                statusText.textContent = "Hot";
                statusText.style.color = 'red';
                tempValueElement.style.color = 'red';
                tempValueElement.textContent = `${temperature.toFixed(2)} ℃`;

            } else if (temperature > 31) {
                thermometer.src = '{{ asset('images/Temp/hot.png') }}';
                statusText.textContent = "Too Hot";
                statusText.style.color = 'darkred';
                tempValueElement.style.color = 'darkred';
                tempValueElement.textContent = `${temperature.toFixed(2)} ℃`;

            } else {
                thermometer.src = '{{ asset('images/Temp/hot.png') }}';
                thermometer.style.filter = 'grayscale(100%)';
                tempValueElement.textContent = "N/A";
                statusText.style.color = 'gray';
                tempValueElement.style.color = 'gray';
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
                    modeText.textContent = 'Inactive';
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

@endsection
