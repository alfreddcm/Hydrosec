
@extends('Worker/header')
@section('title', 'Hydrosec Dashboard')
@section('content')
    @php
        use App\Models\Tower;
        use App\Models\Worker;
        use App\Models\Owner;


        use Illuminate\Support\Facades\Auth;
        use Illuminate\Support\Facades\Crypt;
        $ownerid = Worker::where('id', Auth::id())->first();
        $owner=Owner::where('id',Auth::id())->first();


        $towerinfo = Tower::where('OwnerID', $ownerid->OwnerID)->first();
    @endphp
    <style>
        canvas {
            height: 200px !important;
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

        /*  */
        .sensor-card {
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
    </style>

        <div class="container my-4">
            <div class="card text-center">
                <div class="card-body justify-content-center">
                    <div class="card-title">

                        <h2 class="title">
                            {{ Crypt::decryptString($towerinfo->name) }}
                        </h2>
                            <p class="card-text">
                                {{ Crypt::decryptString($owner->name) }}
                            </p>
                       

                        <div class="row justify-content-center">
                            <div class="col-sm-3">
                                <h5>Status: <span id="statusCircle1" class="circle"></span><span id="statusText1" class="status-text">Inactive</span></h5>
                            </div>
                            <div class="col-sm-3">
                                <h5>Grow Lights : <span id="statusCircle" class="circle"></span><span id="statusText" class="status-text">Inactive</span></h5>
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
                                            data-bs-target="#tempmodal" data-tower-id="{{ $towerinfo->id }}"
                                            data-column="pH">

                                            <img src="{{ asset('images/icon/graph.png') }}" class="img-fluid rounded-top"
                                                alt="" style="height:30px" ; />
                                        </button>
                                        <div class="icon ">
                                            <img id="ph-scale" src="{{ asset('images/ph/8.png') }}" alt="ph-scale">
                                        </div>


                                        <div class="value">
                                            <h4 class="mt-3"><span id="ph-value">n/a</span> <span
                                                    id="ph-status">n/a</span></h4>

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
                            <h6>N/A</h6>
                        </div>
                        <div class="col-sm-3">
                            <h5>Expected Date Harvest</h5>
                            <h6>N/A</h6>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card nutcard text-start mt-2">
                <div class="card-body">
                    <h4 class="card-title">Nutrient Delivery Logs</h4>
                    <p class="card-text">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-borderless table-primary align-middle"
                            style="height: 30px">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>No.</th>
                                    <th>Status</th>
                                    <th>Timestamps</th>
                                </tr>
                            </thead>
                            <tbody id="sensor-data-body" class="table-group-divider">
                            </tbody>
                            <tfoot>

                            </tfoot>
                        </table>
                    </div>

                    </p>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="tempmodal" tabindex="-1" role="dialog" aria-labelledby="modalTitleId"
            aria-hidden="true">
            <div class="modal-dialog  modal-dialog-centered" role="document">
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
            $(document).ready(function() {
                let tempChart = null;

                $('#tempmodal').on('shown.bs.modal', function(event) {
                    let button = event.relatedTarget;
                    if (!button) {
                        console.error('No related target found. Unable to get data attributes.');
                        return;
                    }

                    let towerId = button.getAttribute('data-tower-id');
                    let column = button.getAttribute('data-column');

                    // Clear the existing chart if it exists
                    if (tempChart) {
                        tempChart.destroy();
                    }

                    $.ajax({
                        url: `/Worker/get-data/${towerId}/${column}`,
                        method: 'GET',
                        success: function(response) {
                            if (response.error) {
                                console.error('Error fetching data:', response.error);
                                return;
                            }

                            const data = response.sensorData;
                            const labels = data.map(item => item.timestamp);
                            const values = data.map(item => item.value);

                            // Ensure the canvas element is present
                            const ctx = document.getElementById('tempChart');
                            if (!ctx) {
                                console.error('Canvas element not found.');
                                return;
                            }

                            // Ensure the context is retrieved correctly
                            const chartCtx = ctx.getContext('2d');
                            if (!chartCtx) {
                                console.error('Unable to get canvas context.');
                                return;
                            }

                            tempChart = new Chart(chartCtx, {
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
            });


            var id = {{ $towerinfo->id }};

             $(document).ready(function() {

                function fetchSensorData2() {
                    $.ajax({
                        url: '/Worker/sensor-data/' + id, // Adjust the URL as needed
                        method: 'GET',
                        success: function(response) {
                            if (response.sensorData) {
                                const Temperature = parseFloat(response.sensorData.temperature) || 0;
                                const NutrientVolume = parseFloat(response.sensorData.nutrient_level) || 0;
                                const pHlevel = parseFloat(response.sensorData.pH) || 0;
                                const light = parseFloat(response.sensorData.light) || 0;
                                const status = parseFloat(response.sensorData.status) || 0;



                                // Update the graphs/images
                                updateThermometerImage(Temperature);
                                updateNutrientImage(NutrientVolume);
                                updatePhScaleImage(pHlevel);
                                updateLightStatus(light);
                                updatestatus(status);        

                            } else {
                                console.log('No data available');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('AJAX Error: ' + status + ' ' + error);
                        }
                    });

                    setInterval(fetchSensorData2, 10000); // Refresh graph data every 10 seconds
                }
                fetchSensorData2(); 
            });





            function updateThermometerImage(temperature) {
                const thermometer = document.getElementById('thermometer');
                const statusText = document.getElementById('temp-status');
                const tempValueElement = document.getElementById('temp-value');

                tempValueElement.textContent = `${temperature.toFixed(2)} ℃`;
                if (temperature < 18) {
                    thermometer.src = '{{ asset('images/Temp/cold.png') }}';
                    statusText.textContent = "Cold";
                    statusText.style.color = 'blue';
                    tempValueElement.style.color = 'blue';

                } else if (temperature >= 18 && temperature <= 25) {
                    thermometer.src =
                        '{{ asset('images/Temp/normal.png') }}';
                    statusText.textContent = "Normal (Optimal)";
                    statusText.style.color = 'gray';
                    tempValueElement.style.color = 'gray';

                } else {
                    thermometer.src = '{{ asset('images/Temp/hot.png') }}';
                    statusText.textContent = "Hot";
                    statusText.style.color = 'red';
                    tempValueElement.style.color = 'red';

                }
            }

            function updateNutrientImage(nutrientVolume) {
                const nutrientImage = document.getElementById('nutrient-image');
                const statusText = document.getElementById('nutrient-status');
                const volumeValueElement = document.getElementById('nutrient-value');

                volumeValueElement.textContent = `${nutrientVolume.toFixed(2)}%`;

                if (nutrientVolume >= 10 && nutrientVolume <= 20) {
                    nutrientImage.src = '{{ asset('images/Water/10.png') }}';
                    statusText.textContent = "Critically Low";
                    statusText.style.color = 'red';
                    volumeValueElement.style.color = 'red';

                } else if (nutrientVolume > 20 && nutrientVolume <= 30) {
                    nutrientImage.src = '{{ asset('images/Water/30.png') }}';
                    statusText.textContent = "Low";
                    statusText.style.color = 'orange';
                    volumeValueElement.style.color = 'orange';

                } else if (nutrientVolume > 30 && nutrientVolume <= 50) {
                    nutrientImage.src = '{{ asset('images/Water/50.png') }}';
                    statusText.textContent = "Good";
                    statusText.style.color = 'green';
                    volumeValueElement.style.color = 'green';

                } else if (nutrientVolume > 70 && nutrientVolume <= 90) {
                    nutrientImage.src = '{{ asset('images/Water/90.png') }}';
                    statusText.textContent = "Full";
                    statusText.style.color = 'blue';
                    volumeValueElement.style.color = 'blue';

                } else if (nutrientVolume > 90 && nutrientVolume <= 100) {
                    nutrientImage.src = '{{ asset('images/Water/100.png') }}';
                    statusText.textContent = "Full";
                    statusText.style.color = 'blue';
                    volumeValueElement.style.color = 'blue';

                } else {
                    nutrientImage.src = '{{ asset('images/Water/100.png') }}'; // Default image
                    statusText.textContent = "Out of Range";
                    statusText.style.color = 'gray';
                    volumeValueElement.style.color = 'gray';
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
                    } else if (phValue === 7) {
                        statusText.textContent = "Neutral";
                        statusText.style.color = 'green';
                        phValueElement.style.color = 'green';
                    } else {
                        statusText.textContent = "Good";
                        statusText.style.color = 'blue';
                        phValueElement.style.color = 'blue';
                    }
                } else {
                    statusText.textContent = "Invalid pH value";
                    statusText.style.color = 'black';
                    phValueElement.style.color = 'black';
                }
            }


            function fetchpump() {
                $.ajax({
                    url: `/Worker/pump-data/${id}`,
                    method: 'GET',
                    success: function(data) {
                        var tbody = $('#sensor-data-body');
                        tbody.empty(); 
                        $.each(data, function(index, item) {
                            var status = item.pump == 1 ? 'Pumped' : 'Not Pumped';
                            var row = `<tr class="table-light">
                                       <td>${index + 1}</td>
                                       <td>${status}</td>
                                       <td>${item.timestamp}</td>
                                   </tr>`;
                            tbody.append(row);
                        });
                    },
                    error: function() {
                        // Handle error case
                        console.error('Failed to fetch pump data');
                    }
                });
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

        function updatestatus(status) {
            const circle = document.getElementById('statusCircle1');
            const statusText = document.getElementById('statusText1');

            if (status === 1) {
                circle.style.backgroundColor = 'green';
                statusText.textContent = 'Active';

            } else {
                circle.style.backgroundColor = 'gray';
                statusText.textContent = 'Inactive';

            }
        }

            $(document).ready(function() {
                fetchpump();

                setInterval(fetchpump, 30000);
            });
        </script>


@endsection